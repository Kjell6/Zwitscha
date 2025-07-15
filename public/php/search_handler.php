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
    
    // Ergebnisse für Frontend aufbereiten
    $results = [];
    foreach ($users as $user) {
        $results[] = [
            'id' => (int)$user['id'],
            'name' => $user['nutzerName'],
            'avatar' => 'getImage.php?type=user&id=' . $user['id'],
            'profileUrl' => 'Profil.php?userid=' . $user['id'],
        ];
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    // Bei Fehlern leeres Array zurückgeben
    http_response_code(500);
    echo json_encode([]);
}
?> 