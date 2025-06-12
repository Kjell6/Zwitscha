<?php
require_once __DIR__ . '/php/PostVerwaltung.php';
require_once __DIR__ . '/php/NutzerVerwaltung.php';

$feedbackMessage = '';
$feedbackType = '';

// Aktueller Benutzer (später aus Session oder Authentifizierung holen)
$currentUserId = 1;
$nutzerVerwaltung = new NutzerVerwaltung();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$postId) {
    // Wenn keine ID vorhanden ist, kann nichts geladen werden.
    header("Location: index.php");
    exit();
}

$repository = new PostVerwaltung();

// POST Request Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_comment':
            $commentText = trim($_POST['comment_text'] ?? '');

            if (empty($commentText)) {
                $feedbackMessage = 'Kommentar-Text darf nicht leer sein.';
                $feedbackType = 'error';
            } elseif (strlen($commentText) > 500) {
                $feedbackMessage = 'Kommentar darf maximal 500 Zeichen lang sein.';
                $feedbackType = 'error';
            } else {
                $success = $repository->createComment($postId, $currentUserId, $commentText);
                if ($success) {
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();
                } else {
                    $feedbackMessage = 'Fehler beim Speichern des Kommentars.';
                    $feedbackType = 'error';
                }
            }
            break;

        case 'delete_comment':
            $commentId = (int)($_POST['comment_id'] ?? 0);
            if ($commentId) {
                // Sicherheitsprüfung: Ist der Nutzer Admin oder Eigentümer des Kommentars?
                $commentToDelete = $repository->findCommentById($commentId);
                if ($commentToDelete) {
                    $isOwner = (int)$commentToDelete['nutzer_id'] === (int)$currentUserId;
                    // Annahme: $currentUser['istAdministrator'] ist verfügbar
                    $isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']; 
                    if ($isOwner || $isAdmin) {
                        $repository->deleteComment($commentId);
                        // Redirect zur selben Seite, um das Ergebnis anzuzeigen
                        header("Location: " . $_SERVER['REQUEST_URI']);
                        exit();
                    }
                }
            }
            break;
    }
}

// ---- Daten für die Detailansicht laden ----
$post = $repository->getPostById($postId, $currentUserId);
$comments = $repository->getCommentsByPostId($postId);

// Wenn der Post nicht gefunden wurde, zur Startseite umleiten.
if (!$post) {
    header("Location: index.php");
    exit();
}

// Die Logik für Reaktionen und Löschen wird von der Startseite geerbt,
// daher müssen wir die Logik hier nicht duplizieren, sondern nur die Anzeige sicherstellen.
// Die `$post` Variable wird an die inkludierte `post.php` weitergegeben.

// HILFSFUNKTION FÜR ZEITANGABE (aus post.php übernommen)
if (!function_exists('time_ago')) {
    function time_ago(string $datetime, string $full = 'vor %s'): string {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'Jahr', 'm' => 'Monat', 'w' => 'Woche', 'd' => 'Tag',
            'h' => 'Stunde', 'i' => 'Minute', 's' => 'Sekunde',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 'n' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!empty($string)) $string = array_slice($string, 0, 1);
        $time_ago = $string ? implode(', ', $string) : 'gerade jetzt';
        return sprintf($full, $time_ago);
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? 'Post von ' . htmlspecialchars($post['autor']) : 'Post Details'; ?></title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/postDetail.css">
    <link rel="stylesheet" href="css/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <div class="page-header-container">
        <a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'index.php'); ?>" class="back-button">
            <button type="button">Zurück</button>
        </a>
        <h1>Post</h1>
    </div>

    <?php
    // Definiere den Ladezustand basierend darauf, ob ein Post gefunden wurde.
    if ($post) {
        $loadingState = 'data';
    } else {
        $loadingState = 'empty';
    }

    switch ($loadingState) {
        case 'error':
            ?>
            <div class="error-state">
                <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>Fehler beim Laden des Posts</h3>
                <p>Der Post konnte nicht geladen werden. Bitte versuchen Sie es später erneut.</p>
                <button onclick="window.location.reload()" class="btn btn-primary">Neu laden</button>
            </div>
            <?php
            break;

        case 'empty':
        case 'data':
        default:
            if (!$post) {
                ?>
                <div class="empty-state">
                    <i class="bi bi-file-earmark-x" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Post nicht gefunden</h3>
                    <p>Der gewünschte Post existiert nicht oder wurde gelöscht.</p>
                    <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
                </div>
                <?php
            } else {
                // Post anzeigen
                // Die Darstellung eines einzelnen Posts wird jetzt durch post.php gehandhabt
                ?>
                <article class="detail-post">
                    <section class="post-user-infos-detail">
                        <div class="post-user-info-left">
                            <a href="Profil.php" class="no-post-details">
                                <img src="<?php echo htmlspecialchars($post['profilBild']); ?>" alt="Profilbild">
                            </a>
                            <div class="post-user-details-detail">
                                <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="post-author-name">
                                    <?php echo htmlspecialchars($post['autor']); ?>
                                </a>
                                <time datetime="<?php echo $post['datumZeit']; ?>" class="post-timestamp">
                                    <?php 
                                        // Zeit-Label direkt hier berechnen
                                        $time_label = time_ago($post['datumZeit']);
                                        echo htmlspecialchars($time_label); 
                                    ?>
                                </time>
                            </div>
                        </div>
                        <?php 
                            // Berechtigung zum Löschen prüfen
                            $isOwner = (int)$post['userId'] === (int)$currentUser['id'];
                            $isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
                            $canDeletePost = ($isAdmin || $isOwner);
                            if ($canDeletePost): 
                        ?>
                            <form method="POST" action="php/post_action_handler.php" style="display: inline;" onsubmit="return confirm('Post wirklich löschen?');">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button class="post-options-button no-post-details" type="submit" aria-label="Post löschen">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </section>

                    <div class="post-content-detail">
                        <p><?php echo nl2br(htmlspecialchars($post['text'])); ?></p>

                        <?php if (!empty($post['bildPfad'])): ?>
                            <div class="post-image-container">
                                <img src="<?php echo htmlspecialchars($post['bildPfad']); ?>"
                                     alt="Post-Bild"
                                     class="post-image"
                                     onclick="openLightbox('<?php echo htmlspecialchars($post['bildPfad']); ?>')"
                                     style="cursor: pointer;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <section class="post-actions detail-actions">
                        <div class="post-reactions">
                            <?php
                            $emojis = ['👍', '👎', '❤️', '🤣', '❓', '‼️'];
                            foreach ($emojis as $emoji):
                                $count = $post['reactions'][$emoji] ?? 0;
                                
                                // Logik für aktive Reaktionen direkt in der Schleife
                                $reactionEmojiMap = [
                                    'Daumen Hoch' => '👍', 'Daumen Runter' => '👎', 'Herz' => '❤️',
                                    'Lachen' => '🤣', 'Fragezeichen' => '❓', 'Ausrufezeichen' => '‼️',
                                ];
                                $reactionTypeFromEmoji = array_search($emoji, $reactionEmojiMap);
                                $isActive = in_array($reactionTypeFromEmoji, $post['currentUserReactions']);
                                ?>
                                <form method="POST" action="php/post_action_handler.php" style="display:inline" class="reaction-form">
                                    <input type="hidden" name="action" value="toggle_reaction">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="emoji" value="<?php echo $emoji; ?>">
                                    <button class="reaction-button <?php echo $isActive ? 'active' : ''; ?>" type="submit" data-emoji="<?php echo $emoji; ?>">
                                        <?php echo $emoji; ?> <span class="reaction-counter"><?php echo $count; ?></span>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>

                        <!-- Kommentar-Eingabeformular -->
                        <form method="POST" class="comment-input-group">
                            <input type="hidden" name="action" value="create_comment">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <textarea name="comment_text" id="comment-input" placeholder="Schreibe einen Kommentar..." maxlength="500" required></textarea>
                            <button id="comment-button" type="submit">Kommentieren</button>
                        </form>
                    </section>
                </article>

                <!-- Kommentare Sektion -->
                <section class="comments-section">
                    <?php if (empty($comments)): ?>
                        <div class="empty-state">
                            <i class="bi bi-chat-dots" style="font-size: 32px; margin-bottom: 15px;"></i>
                            <h3>Noch keine Kommentare</h3>
                            <p>Sei der Erste, der einen Kommentar schreibt!</p>
                        </div>
                    <?php else: ?>
                        <h2><?php echo count($comments); ?> Kommentar<?php echo count($comments) != 1 ? 'e' : ''; ?></h2>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <?php
                                // Bereite die Daten für das Template vor
                                $comment_for_template = $comment;
                                $comment_for_template['time_label'] = time_ago($comment['datumZeit']);
                                
                                // Die Darstellung eines einzelnen Kommentars wird durch kommentar.php gehandhabt
                                include 'kommentar.php';
                                ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <?php
            }
            break;
    }
    ?>

</main>

<?php include 'footerMobile.php'; ?>

<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<script>
    const commentInput = document.getElementById('comment-input');
    commentInput.addEventListener('input', () => {
        commentInput.style.height = 'auto';
        commentInput.style.height = commentInput.scrollHeight + 'px';
    });
</script>

<?php include 'lightbox.php'; ?>


</body>
</html>