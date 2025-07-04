<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Ausgabe als HTML, nicht JSON
header('Content-Type: text/html');

if (!isLoggedIn()) {
    http_response_code(403);
    echo '<p>Bitte zuerst einloggen.</p>';
    exit;
}

$currentUserId = getCurrentUserId();
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;

$postVerwaltung = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

$posts = $postVerwaltung->getPostsWithOffset($currentUserId, $offset, $limit);

foreach ($posts as $post) {
    // WICHTIG: $post.php erwartet $post und $currentUser
    include __DIR__ . '/../post.php';
}
