<?php
// Post-Aktionen verarbeiten (Like, Delete, Comment, etc.)
require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// Nur POST-Requests verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Nur POST-Requests erlaubt']);
        exit();
    }
    header('Location: ../index.php');
    exit();
}

// Initialisierung
$postRepository = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();
$action = $_POST['action'] ?? '';

// Login-Status prüfen
if (!isLoggedIn()) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Nicht angemeldet']);
        exit();
    }
    header("Location: ../Login.php");
    exit();
}

$currentUserId = getCurrentUserId();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

// Hilfsfunktion: AJAX-Request erkennen
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Hilfsfunktion: JSON-Response senden
function sendJsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}

// Hilfsfunktion: Fehler-Response senden
function sendErrorResponse($message) {
    sendJsonResponse(false, $message);
}

// Aktionen verarbeiten
switch ($action) {
    case 'create_post':
        $postText = trim($_POST['post_text'] ?? '');
        
        // Validierung
        if (empty($postText)) {
            if (isAjaxRequest()) {
                sendErrorResponse('Post-Text darf nicht leer sein.');
            }
            $feedbackMessage = 'Post-Text darf nicht leer sein.';
            break;
        }
        
        if (strlen($postText) > 300) {
            if (isAjaxRequest()) {
                sendErrorResponse('Post-Text darf maximal 300 Zeichen lang sein.');
            }
            $feedbackMessage = 'Post-Text darf maximal 300 Zeichen lang sein.';
            break;
        }
        
        // Bild-Verarbeitung
        $imageData = null;
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($_FILES['post_image']['type'], $allowedTypes)) {
                if (isAjaxRequest()) {
                    sendErrorResponse('Nur JPEG, PNG, GIF und WebP Dateien sind erlaubt.');
                }
                $feedbackMessage = 'Nur JPEG, PNG, GIF und WebP Dateien sind erlaubt.';
                break;
            }
            
            if ($_FILES['post_image']['size'] > 2 * 1024 * 1024) {
                if (isAjaxRequest()) {
                    sendErrorResponse('Das Bild ist zu groß. Maximal 2 MB sind erlaubt.');
                }
                $feedbackMessage = 'Das Bild ist zu groß. Maximal 2 MB sind erlaubt.';
                break;
            }
            
            $imageData = file_get_contents($_FILES['post_image']['tmp_name']);
            if ($imageData === false) {
                if (isAjaxRequest()) {
                    sendErrorResponse('Fehler beim Lesen der Bilddatei.');
                }
                $feedbackMessage = 'Fehler beim Lesen der Bilddatei.';
                break;
            }
        }
        
        // Post erstellen
        $newPostId = $postRepository->createPost($currentUserId, $postText, $imageData);
        
        if ($newPostId) {
            if (isAjaxRequest()) {
                // Post-HTML für AJAX-Response generieren
                $post = $postRepository->getPostById($newPostId, $currentUserId);
                if ($post) {
                    ob_start();
                    include __DIR__ . '/../post.php';
                    $postHtml = ob_get_clean();
                    
                    sendJsonResponse(true, 'Post erfolgreich erstellt', [
                        'post' => $postHtml,
                        'post_id' => $newPostId
                    ]);
                } else {
                    sendErrorResponse('Post konnte nicht geladen werden.');
                }
            }
            $feedbackMessage = 'Post erfolgreich angelegt.';
        } else {
            if (isAjaxRequest()) {
                sendErrorResponse('Fehler beim Speichern des Posts in der Datenbank.');
            }
            $feedbackMessage = 'Fehler beim Speichern des Posts in der Datenbank.';
        }
        break;

    case 'delete_post':
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($postId > 0) {
            $postToDelete = $postRepository->findPostById($postId);

            $isOwner = ($postToDelete && (int)$postToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = ($currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);

            if ($postToDelete && ($isOwner || $isAdmin)) {
                $success = $postRepository->deletePost($postId);
                if (isAjaxRequest()) {
                    if ($success) {
                        sendJsonResponse(true, 'Post erfolgreich gelöscht');
                    } else {
                        sendErrorResponse('Fehler beim Löschen des Posts');
                    }
                }
            } else {
                if (isAjaxRequest()) {
                    sendErrorResponse('Keine Berechtigung zum Löschen dieses Posts');
                }
            }
        } else {
            if (isAjaxRequest()) {
                sendErrorResponse('Ungültige Post-ID');
            }
        }
        break;

    case 'create_comment':
        $postId = (int)($_POST['post_id'] ?? 0);
        $commentText = trim($_POST['comment_text'] ?? '');
        
        // Validierung
        if ($postId <= 0) {
            if (isAjaxRequest()) {
                sendErrorResponse('Ungültige Post-ID');
            }
            break;
        }
        
        if (empty($commentText)) {
            if (isAjaxRequest()) {
                sendErrorResponse('Kommentar-Text darf nicht leer sein');
            }
            break;
        }
        
        if (strlen($commentText) > 300) {
            if (isAjaxRequest()) {
                sendErrorResponse('Kommentar-Text darf maximal 300 Zeichen lang sein');
            }
            break;
        }
        
        // Kommentar erstellen
        $newCommentId = $postRepository->createComment($postId, $currentUserId, $commentText);
        
        if ($newCommentId && isAjaxRequest()) {
            // Kommentar-HTML für AJAX-Response generieren
            $comment = $postRepository->getCommentById($newCommentId);
            if ($comment) {
                // Kommentar-Daten für Template vorbereiten
                $comment_for_template = [
                    'id' => $comment['id'],
                    'text' => $comment['text'],
                    'userId' => $comment['nutzer_id'],
                    'autor' => $comment['nutzer_name'],
                    'datumZeit' => $comment['datumZeit'],
                    'time_label' => (new DateTime($comment['datumZeit']))->format('d.m.y, H:i'),
                    'antworten' => []
                ];
                
                ob_start();
                include __DIR__ . '/../kommentar.php';
                $commentHtml = ob_get_clean();
                
                sendJsonResponse(true, 'Kommentar erfolgreich erstellt', [
                    'comment' => $commentHtml,
                    'comment_id' => $newCommentId
                ]);
            } else {
                sendErrorResponse('Kommentar konnte nicht geladen werden');
            }
        }
        break;

    case 'delete_comment':
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId > 0) {
            $commentToDelete = $postRepository->findCommentById($commentId);

            $isOwner = ($commentToDelete && (int)$commentToDelete['nutzer_id'] === $currentUserId);
            $isAdmin = ($currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);

            if ($commentToDelete && ($isOwner || $isAdmin)) {
                $success = $postRepository->deleteComment($commentId);
                if (isAjaxRequest()) {
                    if ($success) {
                        sendJsonResponse(true, 'Kommentar erfolgreich gelöscht');
                    } else {
                        sendErrorResponse('Fehler beim Löschen des Kommentars');
                    }
                }
            } else {
                if (isAjaxRequest()) {
                    sendErrorResponse('Keine Berechtigung zum Löschen dieses Kommentars');
                }
            }
        } else {
            if (isAjaxRequest()) {
                sendErrorResponse('Ungültige Kommentar-ID');
            }
        }
        break;

    case 'reply_comment':
        $postId = (int)($_POST['post_id'] ?? 0);
        $parentCommentId = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;
        $commentText = trim($_POST['comment_text'] ?? '');

        // Validierung
        if ($postId <= 0 || $parentCommentId <= 0) {
            if (isAjaxRequest()) {
                sendErrorResponse('Ungültige Post- oder Kommentar-ID');
            }
            break;
        }
        
        if (empty($commentText)) {
            if (isAjaxRequest()) {
                sendErrorResponse('Antwort-Text darf nicht leer sein');
            }
            break;
        }
        
        if (strlen($commentText) > 300) {
            if (isAjaxRequest()) {
                sendErrorResponse('Antwort-Text darf maximal 300 Zeichen lang sein');
            }
            break;
        }

        // Antwort auf Kommentar erstellen
        $newReplyId = $postRepository->createComment($postId, $currentUserId, $commentText, $parentCommentId);
        
        if ($newReplyId && isAjaxRequest()) {
            // Antwort-HTML für AJAX-Response generieren
            $reply = $postRepository->getCommentById($newReplyId);
            if ($reply) {
                // Antwort-Daten für Template vorbereiten
                $antwort = [
                    'id' => $reply['id'],
                    'text' => $reply['text'],
                    'userId' => $reply['nutzer_id'],
                    'autor' => $reply['nutzer_name'],
                    'datumZeit' => $reply['datumZeit']
                ];
                
                ob_start();
                ?>
                <article class="post comment-layout">
                    <a href="Profil.php?userid=<?php echo htmlspecialchars($antwort['userId']); ?>" class="no-post-details comment-profil-link">
                        <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($antwort['userId']); ?>" class="post-user-image" loading="lazy" alt="Profilbild von <?php echo htmlspecialchars($antwort['autor']); ?>">
                    </a>
                    <main class="post-main-content">
                        <section class="post-user-infos">
                            <a href="Profil.php?userid=<?php echo htmlspecialchars($antwort['userId']); ?>" class="no-post-details comment-profil-link-inline">
                                <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($antwort['userId']); ?>" class="post-user-image-inline" loading="lazy" alt="">
                            </a>
                            <div class="post-user-details">
                                <a href="Profil.php?userid=<?php echo htmlspecialchars($antwort['userId']); ?>" class="post-author-name">
                                    <?php echo htmlspecialchars($antwort['autor']); ?>
                                </a>
                                <time datetime="<?php echo htmlspecialchars($antwort['datumZeit']); ?>" class="post-timestamp">
                                    <?php echo (new DateTime($antwort['datumZeit']))->format('d.m.y, H:i'); ?>
                                </time>
                            </div>
                            <?php if ((int)$antwort['userId'] === $currentUserId || (isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'])): ?>
                                <form class="delete-form" data-type="comment" data-comment-id="<?php echo $antwort['id']; ?>" data-post-id="<?php echo $postId; ?>" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_comment">
                                    <input type="hidden" name="comment_id" value="<?php echo $antwort['id']; ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                                    <button class="post-options-button no-post-details" type="submit" aria-label="Antwort löschen">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </section>
                        <div class="post-content">
                            <p><?php echo linkify_content($antwort['text'], $nutzerVerwaltung); ?></p>
                        </div>
                    </main>
                </article>
                <?php
                $replyHtml = ob_get_clean();
                
                sendJsonResponse(true, 'Antwort erfolgreich erstellt', [
                    'comment' => $replyHtml,
                    'reply_id' => $newReplyId
                ]);
            } else {
                sendErrorResponse('Antwort konnte nicht geladen werden');
            }
        }
        break;
}

// Nur für normale (nicht-AJAX) Requests weiterleiten
if (!isAjaxRequest()) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../index.php';

    // Bei Posts: Anker zur Post-ID hinzufügen
    if (isset($postId) && strpos($redirectUrl, 'postDetails.php') === false) {
        $redirectUrl = strtok($redirectUrl, '#');
        $redirectUrl .= "#post-" . $postId;
    }

    header("Location: " . $redirectUrl);
    exit();
} 