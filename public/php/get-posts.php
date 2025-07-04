<?php
require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/NutzerVerwaltung.php';

header('Content-Type: text/html');

// Nutzer muss eingeloggt sein
if (!isLoggedIn()) {
    http_response_code(403);
    echo "<p>Nicht eingeloggt</p>";
    exit;
}

$currentUserId = getCurrentUserId();
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$filter = $_GET['filter'] ?? 'all';

$postVerwaltung = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();

if ($filter === 'followed') {
    $posts = $postVerwaltung->getFollowedPosts($currentUserId, $limit, $offset);
} else {
    $posts = $postVerwaltung->getAllPosts($currentUserId, $limit, $offset);
}

// Wenn keine Posts gefunden wurden, einen leeren String zurückgeben.
if (empty($posts)) {
    // Ein spezieller Header oder eine leere Antwort, damit das Frontend weiß, dass es keine weiteren Posts gibt.
    http_response_code(204); // No Content
    exit;
}

// Posts als HTML-Snippets rendern und zurückgeben
foreach ($posts as $post) {
    // Die 'post.php' Vorlage wiederverwenden, um Konsistenz zu gewährleisten
    // Die Variablen müssen hier für das Template verfügbar sein
    include __DIR__ . '/../post.php';
}
