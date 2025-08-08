<?php
require_once __DIR__ . '/NutzerVerwaltung.php';

// Nur POST-Requests verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// JSON-Response Header setzen
header('Content-Type: application/json');

try {
    $nutzerVerwaltung = new NutzerVerwaltung();
    
    // Suchbegriff aus POST-Daten holen
    $searchTerm = trim($_POST['query'] ?? '');
    
    if (empty($searchTerm)) {
        echo json_encode([]);
        exit();
    }
    
    // Suche durchführen
    $users = $nutzerVerwaltung->searchUsers($searchTerm, 8);
    // Wenn der anfragende Nutzer eingeloggt ist, prüfen wir für jedes Ergebnis, ob es eine gegenseitige Follow-Beziehung gibt
    $currentUserId = null;
    if (function_exists('getCurrentUserId')) {
        // session_helper.php wird normalerweise included; versuchen wir es defensiv
        @include_once __DIR__ . '/session_helper.php';
        $currentUserId = getCurrentUserId() ?? null;
    }
    
    // Ergebnisse für Frontend aufbereiten
    $results = [];
    foreach ($users as $user) {
        $mutual = false;
        if ($currentUserId) {
            // gegenseitiges Folgen prüfen
            $mutual = $nutzerVerwaltung->isFollowing($currentUserId, (int)$user['id']) && $nutzerVerwaltung->isFollowing((int)$user['id'], $currentUserId);
        }
        $results[] = [
            'id' => (int)$user['id'],
            'name' => $user['nutzerName'],
            'avatar' => 'getImage.php?type=user&id=' . $user['id'],
            'profileUrl' => 'Profil.php?userid=' . $user['id'],
            'mutual' => $mutual
        ];
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    // Bei Fehlern leeres Array zurückgeben
    http_response_code(500);
    echo json_encode([]);
}
?> 