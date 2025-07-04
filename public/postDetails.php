<?php
require_once __DIR__ . '/php/PostVerwaltung.php';
require_once __DIR__ . '/php/NutzerVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';
require_once __DIR__ . '/php/helpers.php';

// Prüfen ob angemeldet
requireLogin();

// Aktueller Benutzer aus Session holen
$currentUserId = getCurrentUserId();
$nutzerVerwaltung = new NutzerVerwaltung();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$postId) {
    // Wenn keine ID vorhanden ist, kann nichts geladen werden.
    header("Location: index.php");
    exit();
}

$repository = new PostVerwaltung();

// ---- Daten für die Detailansicht laden ----
$post = $repository->getPostById($postId, $currentUserId);
$comments = $repository->getMainCommentsByPostId($postId);

// Wenn der Post nicht gefunden wurde, zur Startseite umleiten.
if (!$post) {
    header("Location: index.php");
    exit();
}

// Zentrales Emoji-Mapping für spätere Verwendung
$reactionEmojiMap = getReactionEmojiMap();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? 'Post von ' . htmlspecialchars($post['autor']) : 'Post Details'; ?></title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/postDetail.css">
    <link rel="stylesheet" href="css/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <div class="page-header-container">
        <a href="index.php" class="logo">
            <button class="back-button" type="button">Zurück</button>
        </a>
        <h1>Post</h1>
    </div>

    <?php
    ?>
    <article class="detail-post">
        <section class="post-user-infos-detail">
            <div class="post-user-info-left">
                <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
                    <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($post['userId']); ?>" loading="lazy" alt="Profilbild">
                </a>
                <div class="post-user-details-detail">
                    <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="post-author-name">
                        <?php echo htmlspecialchars($post['autor']); ?>
                    </a>
                    <time datetime="<?php echo $post['datumZeit']; ?>" class="post-timestamp">
                        <?php
                        // Datum und Uhrzeit im Format 'd.m.y, H:i' ausgeben
                        $date = new DateTime($post['datumZeit']);
                        echo $date->format('d.m.y, H:i');
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
            <p><?php echo linkify_content($post['text'], $nutzerVerwaltung); ?></p>

            <?php if (!empty($post['bildDaten'])): ?>
                <div class="post-image-container">
                    <img loading="lazy" src="getImage.php?type=post&id=<?php echo $post['id']; ?>"
                         alt="Post-Bild"
                         class="post-image"
                         onclick="openLightbox('getImage.php?type=post&id=<?php echo $post['id']; ?>')"
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

                    // Logik für aktive Reaktionen unter Verwendung des zentralen Mappings
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
        </section>
    </article>

    <form method="POST" action="php/post_action_handler.php" class="create-post-form">
        <input type="hidden" name="action" value="create_comment">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

        <div class="form-header">
            <img class="user-avatar" src="getImage.php?type=user&id=<?php echo $currentUserId; ?>" loading="lazy" alt="Dein Profilbild">
            <textarea name="comment_text" id="post-input" placeholder="Schreibe einen Kommentar..." maxlength="300" required></textarea>
        </div>

        <div class="form-footer">
            <div class="form-actions"></div>
            <div class="form-submit-area">
                <p class="character-count">0/300</p>
                <button class="post-button" type="submit">Kommentieren</button>
            </div>
        </div>
    </form>

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

                    // Datum und Uhrzeit fürs Kommentar-Template formatieren
                    $commentDate = new DateTime($comment['datumZeit']);
                    $comment_for_template['time_label'] = $commentDate->format('d.m.y, H:i');

                    // Jetzt holst du die Antworten zu diesem Kommentar aus der DB
                    // Beispiel: $repository ist deine Kommentar/Post-Verwaltungsklasse
                    $antworten = $repository->getRepliesByParentCommentId($comment['id']);

                    // Übergib die Antworten an das Template
                    $comment_for_template['antworten'] = $antworten;

                    // Die Darstellung eines einzelnen Kommentars wird durch kommentar.php gehandhabt
                    include 'kommentar.php';
                    ?>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'footerMobile.php'; ?>

<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<script>
    const commentInput = document.getElementById('post-input');
    const charCount = document.querySelector('.form-submit-area .character-count');

    commentInput.addEventListener('input', () => {
        // Automatische Höhenanpassung
        commentInput.style.height = 'auto';
        commentInput.style.height = commentInput.scrollHeight + 'px';

        // Zeichenzähler aktualisieren
        const count = commentInput.value.length;
        const maxLength = commentInput.maxLength;
        charCount.textContent = count + '/' + maxLength;
        
        // Farbe ändern, wenn das Limit fast erreicht ist
        charCount.style.color = count > (maxLength - 20) ? '#dc3545' : '#6c757d';
    });


    function toggleReplyForm(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        if (!form) return;

            form.classList.toggle('hidden');

        // Zustand in sessionStorage speichern
        let openReplies = JSON.parse(sessionStorage.getItem('openReplies')) || [];
        const isOpen = !form.classList.contains('hidden');

        if (isOpen) {
            // Zur Liste hinzufügen, wenn nicht schon vorhanden
            if (!openReplies.includes(commentId)) {
                openReplies.push(commentId);
        }
        } else {
            // Aus der Liste entfernen
            openReplies = openReplies.filter(id => id !== commentId);
        }

        sessionStorage.setItem('openReplies', JSON.stringify(openReplies));
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Zustand aus sessionStorage wiederherstellen
        const openReplies = JSON.parse(sessionStorage.getItem('openReplies')) || [];
        openReplies.forEach(commentId => {
            const form = document.getElementById('reply-form-' + commentId);
            if (form) {
                form.classList.remove('hidden');
            }
        });
    });


    document.querySelectorAll('textarea').forEach(textarea => {
        const counter = textarea.closest('form').querySelector('.character-count');

        textarea.addEventListener('input', () => {
            // Höhe automatisch anpassen
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';

            // Zeichenzähler aktualisieren, falls vorhanden
            if (counter) {
                const count = textarea.value.length;
                const maxLength = textarea.maxLength;
                counter.textContent = count + '/' + maxLength;

                // Farbe ändern, wenn nah am Limit
                counter.style.color = count > (maxLength - 20) ? '#dc3545' : '#6c757d';
            }
        });
    });


</script>

<?php include 'lightbox.php'; ?>


</body>
</html>