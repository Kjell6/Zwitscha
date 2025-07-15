<?php
// Ajax-Handler für Post-Reaktionen
require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Nur POST-Requests verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Nur POST-Requests erlaubt']);
    exit();
}

// Content-Type auf JSON setzen
header('Content-Type: application/json');

try {
    // Initialisierung
    $postRepository = new PostVerwaltung();
    $nutzerVerwaltung = new NutzerVerwaltung();
    
    // Login-Status prüfen
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Nicht angemeldet']);
        exit();
    }
    
    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
    
    // Parameter validieren
    $postId = (int)($_POST['post_id'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    
    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültige Post-ID']);
        exit();
    }
    
    if (empty($emoji) || strlen($emoji) > 10) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültiges Emoji']);
        exit();
    }
    
    // Erlaubte Emojis prüfen
    $allowedEmojis = ['👍', '👎', '❤️', '🤣', '❓', '‼️'];
    if (!in_array($emoji, $allowedEmojis)) {
        http_response_code(400);
        echo json_encode(['error' => 'Emoji nicht erlaubt']);
        exit();
    }
    
    // Reaktion togglen
    $success = $postRepository->toggleReaction($currentUserId, $postId, $emoji);
    
    if (!$success) {
        http_response_code(500);
        echo json_encode(['error' => 'Fehler beim Verarbeiten der Reaktion']);
        exit();
    }
    
    // Aktualisierte Reaktionen für diesen Post laden
    $post = $postRepository->getPostById($postId, $currentUserId);
    
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post nicht gefunden']);
        exit();
    }
    
    // Erfolgreiche Antwort mit aktuellen Reaktionsdaten
    echo json_encode([
        'success' => true,
        'post_id' => $postId,
        'reactions' => $post['reactions'],
        'currentUserReactions' => $post['currentUserReactions']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Serverfehler: ' . $e->getMessage()]);
} 