<?php
/**
 * Hilfsfunktionen für Session-Management
 */

/**
 * Startet eine Session falls noch nicht gestartet.
 */
function ensureSessionStarted(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Prüft ob ein Nutzer angemeldet ist.
 *
 * @return bool True wenn angemeldet.
 */
function isLoggedIn(): bool {
    ensureSessionStarted();
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
 * Meldet den aktuellen Nutzer ab.
 */
function logout(): void {
    ensureSessionStarted();
    session_destroy();
    header("Location: Login.php");
    exit();
}