<?php
// Session-Management und Authentifizierung

/**
 * Startet eine Session falls noch nicht gestartet.
 */
function ensureSessionStarted(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // "Angemeldet bleiben"-Cookie prüfen
    if (!isset($_SESSION['angemeldet']) && isset($_COOKIE['rememberme'])) {
        $parts = explode(':', $_COOKIE['rememberme']);
        if (count($parts) === 2) {
            $selector = $parts[0];
            $validator = $parts[1];

            require_once __DIR__ . '/NutzerVerwaltung.php';
            $nutzerVerwaltung = new NutzerVerwaltung();
            $user = $nutzerVerwaltung->consumeRememberToken($selector, $validator);

            if ($user) {
                // Nutzer via Cookie authentifiziert, Session setzen
                $_SESSION['angemeldet'] = true;
                $_SESSION['eingeloggt'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nutzerName'];
                $_SESSION['ist_admin'] = $user['istAdministrator'];
            } else {
                // Ungültiges Token, Cookie löschen
                setcookie('rememberme', '', time() - 3600, '/');
            }
        }
    }
}

/**
 * Prüft ob ein Nutzer angemeldet ist.
 *
 * @return bool True wenn angemeldet.
 */
function isLoggedIn(): bool {
    ensureSessionStarted();
    // Prüfe zusätzlich, ob der Nutzer noch existiert
    if (!validateCurrentUser()) {
        return false;
    }
    return isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
}

/**
 * Holt die aktuelle Nutzer-ID aus der Session.
 *
 * @return int|null Die Nutzer-ID oder null wenn nicht angemeldet.
 */
function getCurrentUserId(): ?int {
    ensureSessionStarted();
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Holt den aktuellen Benutzernamen aus der Session.
 *
 * @return string|null Der Benutzername oder null wenn nicht angemeldet.
 */
function getCurrentUsername(): ?string {
    ensureSessionStarted();
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Prüft ob der aktuelle Nutzer ein Administrator ist.
 *
 * @return bool True wenn Admin.
 */
function isCurrentUserAdmin(): bool {
    ensureSessionStarted();
    return isset($_SESSION['ist_admin']) && $_SESSION['ist_admin'] == 1;
}

/**
 * Leitet zur Login-Seite weiter wenn nicht angemeldet.
 *
 * @param string $redirectAfterLogin Optionale URL für Weiterleitung nach Login.
 */
function requireLogin(string $redirectAfterLogin = ''): void {
    if (!isLoggedIn()) {
        $loginUrl = 'Login.php';
        if (!empty($redirectAfterLogin)) {
            $loginUrl .= '?redirect=' . urlencode($redirectAfterLogin);
        }
        header("Location: $loginUrl");
        exit();
    }
}

/**
 * Überprüft ob der aktuelle Benutzer noch in der Datenbank existiert.
 * Falls nicht, wird die Session beendet.
 *
 * @return bool True wenn der Benutzer noch existiert.
 */
function validateCurrentUser(): bool {
    ensureSessionStarted();

    // Wenn nicht angemeldet, ist die Validierung erfolgreich (kein Benutzer zum Validieren)
    if (!isset($_SESSION['angemeldet']) || $_SESSION['angemeldet'] !== true) {
        return true;
    }

    // Benutzer-ID aus Session holen
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    if (!$userId) {
        // Session ohne Benutzer-ID ist ungültig
        session_destroy();
        return false;
    }

    // Benutzer in Datenbank prüfen
    require_once __DIR__ . '/NutzerVerwaltung.php';
    $nutzerVerwaltung = new NutzerVerwaltung();
    $user = $nutzerVerwaltung->getUserById($userId);

    if (!$user) {
        // Benutzer existiert nicht mehr - Session beenden
        session_destroy();
        return false;
    }

    return true;
}

/**
 * Meldet den aktuellen Nutzer ab.
 */
function logout(): void {
    ensureSessionStarted();

    // "Angemeldet bleiben"-Token löschen
    if (isset($_COOKIE['rememberme'])) {
        $parts = explode(':', $_COOKIE['rememberme']);
        if (count($parts) === 2) {
            $selector = $parts[0];
            require_once __DIR__ . '/NutzerVerwaltung.php';
            $nutzerVerwaltung = new NutzerVerwaltung();
            $nutzerVerwaltung->deleteRememberToken($selector);
        }
        setcookie('rememberme', '', time() - 3600, '/');
    }

    session_destroy();
    header("Location: Login.php");
    exit();
}

// AJAX-Endpoint für Login-Validierung
if (isset($_GET['action']) && $_GET['action'] === 'validate' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    if (isLoggedIn()) {
        echo json_encode(['valid' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['valid' => false, 'error' => 'Nicht angemeldet']);
    }
    exit();
}