<?php
require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Stellt sicher, dass das Skript nur bei POST-Requests ausgeführt wird.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Optional: Umleiten oder eine Fehlermeldung ausgeben, wenn die Methode nicht POST ist.
    header('Location: ../index.php');
    exit();
}

// === Initialisierung ===
$postRepository = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();
$action = $_POST['action'] ?? '';

// Prüfen ob angemeldet
if (!isLoggedIn()) {
    header("Location: ../Login.php");
    exit();
}

// Benutzer-ID aus Session holen
$currentUserId = getCurrentUserId();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);


// === Aktionen verarbeiten ===
switch ($action) {
    case 'delete_post':
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($postId > 0) {
            $postToDelete = $postRepository->findPostById($postId);

            // Sicherheitsprüfung: Gehört der Post dem Nutzer oder ist der Nutzer ein Admin?
            $isOwner = ($postToDelete && (int)$postToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = ($currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);

            if ($postToDelete && ($isOwner || $isAdmin)) {
                $postRepository->deletePost($postId);
            }
        }
        break;

    case 'toggle_reaction':
        $postId = (int)($_POST['post_id'] ?? 0);
        $emoji = $_POST['emoji'] ?? '';
        if ($postId > 0 && !empty($emoji)) {
            $postRepository->toggleReaction($currentUserId, $postId, $emoji);
        }
        break;

    case 'create_comment':
        $postId = (int)($_POST['post_id'] ?? 0);
        $commentText = trim($_POST['comment_text'] ?? '');
        // Parent ID optional (null, wenn keine Antwort)
        $parentId = isset($_POST['parent_id']) && is_numeric($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if ($postId > 0 && !empty($commentText) && strlen($commentText) <= 300) {
            // Hier die createComment Funktion entsprechend anpassen, um parent_id zu speichern
            $postRepository->createComment($postId, $currentUserId, $commentText, $parentId);
        }
        break;


    case 'delete_comment':
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId > 0) {
            $commentToDelete = $postRepository->findCommentById($commentId);

            // Sicherheitsprüfung: Gehört der Kommentar dem Nutzer oder ist der Nutzer ein Admin?
            $isOwner = ($commentToDelete && (int)$commentToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = ($currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);

            if ($commentToDelete && ($isOwner || $isAdmin)) {
                $postRepository->deleteComment($commentId);
            }
        }
        break;
    
    // Zukünftige Aktionen könnten hier hinzugefügt werden (z.B. 'edit_post', 'edit_comment')
}


// === Rückleitung zur ursprünglichen Seite ===
// Wir verwenden HTTP_REFERER, um zur vorherigen Seite zurückzukehren.
// Das macht den Handler flexibel für index.php, Profil.php, etc.
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../index.php';

if (isset($postId) && strpos($redirectUrl, 'postDetails.php') === false) {
    // Stelle sicher, dass der Anker korrekt an die URL angehängt wird.
    // Entferne zuerst einen eventuell vorhandenen Anker.
    $redirectUrl = strtok($redirectUrl, '#');
    // Hänge den neuen Anker an.
    $redirectUrl .= "#post-" . $postId;
}

header("Location: " . $redirectUrl);
exit(); 