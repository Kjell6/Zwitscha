<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Ausgabe als HTML, nicht JSON
header('Content-Type: text/html; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(403);
    echo '<p>Bitte zuerst einloggen.</p>';
    exit;
}

// --- Parameter aus der Anfrage holen ---
$currentUserId = getCurrentUserId();
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$context = $_GET['context'] ?? 'all'; // 'all', 'followed', 'user', 'hashtag'

$postVerwaltung = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

$posts = [];

// --- Je nach Kontext die passende Methode aufrufen ---
switch ($context) {
    case 'followed':
        $posts = $postVerwaltung->getFollowedPosts($currentUserId, $limit, $offset);
        break;
    
    case 'user':
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;
        if ($userId > 0) {
            $posts = $postVerwaltung->getPostsByUserId($userId, $currentUserId, $limit, $offset);
        }
        break;

    case 'hashtag':
        $tag = $_GET['tag'] ?? '';
        if (!empty($tag)) {
            $posts = $postVerwaltung->getPostsByHashtag($tag, $currentUserId, $limit, $offset);
        }
        break;

    case 'all':
    default:
        $posts = $postVerwaltung->getAllPosts($currentUserId, $limit, $offset);
        break;
}

// --- Posts als HTML ausgeben ---
if (empty($posts)) {
    // Wenn keine Posts geladen wurden, einen leeren Response senden,
    // damit das Frontend weiß, dass das Ende erreicht ist.
    http_response_code(200); 
    exit;
}

foreach ($posts as $post) {
    // WICHTIG: Das post.php Skript erwartet die Variablen $post und $currentUser
    include __DIR__ . '/../post.php';
} 