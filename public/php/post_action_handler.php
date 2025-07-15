<?php
// Post-Aktionen verarbeiten (Like, Delete, Comment, etc.)
require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Nur POST-Requests verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// Initialisierung
$postRepository = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();
$action = $_POST['action'] ?? '';

// Login-Status prüfen
if (!isLoggedIn()) {
    header("Location: ../Login.php");
    exit();
}

$currentUserId = getCurrentUserId();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

// Aktionen verarbeiten
switch ($action) {
    case 'delete_post':
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($postId > 0) {
            $postToDelete = $postRepository->findPostById($postId);

            $isOwner = ($postToDelete && (int)$postToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = ($currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);

            if ($postToDelete && ($isOwner || $isAdmin)) {
                $postRepository->deletePost($postId);
            }
        }
        break;

    case 'create_comment':
        $postId = (int)($_POST['post_id'] ?? 0);
        $commentText = trim($_POST['comment_text'] ?? '');
        // Kommentar erstellen
        if ($postId > 0 && !empty($commentText) && strlen($commentText) <= 300) {
            $postRepository->createComment($postId, $currentUserId, $commentText);
        }
        break;

    case 'delete_comment':
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId > 0) {
            $commentToDelete = $postRepository->findCommentById($commentId);

            $isOwner = ($commentToDelete && (int)$commentToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = ($currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);

            if ($commentToDelete && ($isOwner || $isAdmin)) {
                $postRepository->deleteComment($commentId);
            }
        }
        break;

    case 'reply_comment':
        $postId = (int)($_POST['post_id'] ?? 0);
        $parentCommentId = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;
        $commentText = trim($_POST['comment_text'] ?? '');

        // Antwort auf Kommentar erstellen
        if ($postId > 0 && $parentCommentId > 0 && !empty($commentText) && strlen($commentText) <= 300) {
            $postRepository->createComment($postId, $currentUserId, $commentText, $parentCommentId);
        }
        break;
}

// Zurück zur ursprünglichen Seite
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../index.php';

// Bei Posts: Anker zur Post-ID hinzufügen
if (isset($postId) && strpos($redirectUrl, 'postDetails.php') === false) {
    $redirectUrl = strtok($redirectUrl, '#');
    $redirectUrl .= "#post-" . $postId;
}

header("Location: " . $redirectUrl);
exit(); 