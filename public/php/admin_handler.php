<?php
// Admin-Handler für Administrator-Aktionen
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Nur POST-Requests verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

try {
    $nutzerVerwaltung = new NutzerVerwaltung();
    
    // Aktuellen Benutzer aus Session holen
    $currentUserId = getCurrentUserId();
    if (!$currentUserId) {
        http_response_code(401);
        exit();
    }
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
    
    // Admin-Berechtigung prüfen
    if (!$currentUser || !$currentUser['istAdministrator']) {
        http_response_code(403);
        exit();
    }
    
    $action = $_POST['action'] ?? '';
    
    // Admin-Status umschalten
    if ($action === 'toggle_admin') {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        
        if ($targetUserId > 0) {
            // Aktuelle Daten des Ziel-Benutzers holen
            $targetUser = $nutzerVerwaltung->getUserById($targetUserId);
            
            if ($targetUser) {
                // Admin-Status umschalten
                $newAdminStatus = !$targetUser['istAdministrator'];
                $nutzerVerwaltung->setAdminStatus($targetUserId, $newAdminStatus);
            }
        }
    }
    
    // Zurück zur ursprünglichen Seite
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $referrer);
    exit();
    
} catch (Exception $e) {
    // Bei Fehlern zur ursprünglichen Seite zurück
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $referrer);
    exit();
}
?> 