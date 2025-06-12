<?php
require_once __DIR__ . '/PostVerwaltung.php';

// Stellt sicher, dass das Skript nur bei POST-Requests ausgeführt wird.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Optional: Umleiten oder eine Fehlermeldung ausgeben, wenn die Methode nicht POST ist.
    header('Location: ../index.php');
    exit();
}

// === Initialisierung ===
$postRepository = new PostVerwaltung();
$action = $_POST['action'] ?? '';
// DUMMY-BENUTZERDATEN (später aus Session holen)
$currentUserId = 1;


// === Aktionen verarbeiten ===
switch ($action) {
    case 'delete_post':
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($postId > 0) {
            $postToDelete = $postRepository->findPostById($postId);

            // Sicherheitsprüfung: Gehört der Post dem Nutzer oder ist der Nutzer ein Admin?
            // HINWEIS: isAdmin ist hier noch ein Platzhalter.
            $isOwner = ($postToDelete && (int)$postToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = false; // TODO: Echte Admin-Prüfung implementieren.

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
    
    // Zukünftige Aktionen könnten hier hinzugefügt werden (z.B. 'edit_post')
}


// === Rückleitung zur ursprünglichen Seite ===
// Wir verwenden HTTP_REFERER, um zur vorherigen Seite zurückzukehren.
// Das macht den Handler flexibel für index.php, Profil.php, etc.
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../index.php';

// Umleiten mit einem Anker, um direkt zum bearbeiteten Post zu springen.
if (isset($postId)) {
    // Stelle sicher, dass der Anker korrekt an die URL angehängt wird.
    // Entferne zuerst einen eventuell vorhandenen Anker.
    $redirectUrl = strtok($redirectUrl, '#');
    // Hänge den neuen Anker an.
    $redirectUrl .= "#post-" . $postId;
}

header("Location: " . $redirectUrl);
exit(); 