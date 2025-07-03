<?php
require_once __DIR__ . '/php/PostVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';

header('Content-Type: application/json');

// Nutzer muss eingeloggt sein
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Nicht eingeloggt']);
    exit;
}

$currentUserId = getCurrentUserId();
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;



$postVerwaltung = new PostVerwaltung();
$pageNum = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$posts = $postVerwaltung->getPostsByPage($currentUserId, $pageNum);


// Optional: nur bestimmte Felder zurückgeben (z. B. bei Mobile-Feed)
echo json_encode($posts);
