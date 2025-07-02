<?php
require_once __DIR__ . '/php/PostVerwaltung.php';
require_once __DIR__ . '/php/NutzerVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';
require_once __DIR__ . '/php/helpers.php';

// Sicherstellen, dass Nutzer angemeldet ist
requireLogin();

// Aktueller Nutzer
$currentUserId = getCurrentUserId();
$nutzerVerwaltung = new NutzerVerwaltung();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

// Post ID aus URL
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$postId) {
    header("Location: index.php");
    exit();
}

$postRepository = new PostVerwaltung();

// Post und Kommentare laden
$post = $postRepository->getPostById($postId, $currentUserId);
$comments = $postRepository->getCommentsByPostId($postId);

if (!$post) {
    header("Location: index.php");
    exit();
}
?>




<!DOCTYPE html>
<html lang="de">
<head>
    <!-- ... Meta, CSS & Titel ... -->
</head>
<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <!-- Zurück-Button & Post-Details oben -->
    <div class="page-header-container">
        <button onclick="history.back()" class="back-button" type="button">Zurück</button>
        <h1>Post</h1>
    </div>

    <!-- Post anzeigen (wie gehabt) -->
    <article class="detail-post">
        <!-- ... Post-Benutzerinfos, Inhalt, Reaktionen ... -->
    </article>

    <!-- Neues Kommentarformular für Hauptkommentar -->
    <form method="POST" action="php/post_action_handler.php" class="create-post-form">
        <input type="hidden" name="action" value="create_comment">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

        <div class="form-header">
            <img class="user-avatar" src="getImage.php?type=user&id=<?php echo $currentUserId; ?>" alt="Dein Profilbild">
            <textarea name="comment_text" id="post-input" placeholder="Schreibe einen Kommentar..." maxlength="300" required></textarea>
        </div>

        <div class="form-footer">
            <div class="form-actions"></div>
            <div class="form-submit-area">
                <p class="character-count">0/300</p>
                <button id="post-button" type="submit">Kommentieren</button>
            </div>
        </div>
    </form>

    <!-- Kommentare Sektion mit Antworten -->
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
                <?php
                // Kommentare als Baum (mit Replies verschachtelt) rendern
                function renderCommentsTree($comments, $parentId = null) {
                    foreach ($comments as $comment) {
                        if ($comment['parent_id'] === $parentId) {
                            global $currentUser, $postId, $nutzerVerwaltung;
                            $comment_for_template = $comment;

                            // Zeitlabel anpassen
                            $commentDate = new DateTime($comment['datumZeit']);
                            $comment_for_template['time_label'] = $commentDate->format('d.m.y, H:i');

                            echo '<div class="comment-tree-level" style="margin-left: ' . ($parentId === null ? '0' : '2rem') . ';">';
                            include 'kommentar.php'; // rendert Kommentar + Antwortbutton + Antwortformular
                            // Rekursive Aufrufe für Kinder
                            renderCommentsTree($comments, $comment['id']);
                            echo '</div>';
                        }
                    }
                }

                renderCommentsTree($comments);
                ?>
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
        commentInput.style.height = 'auto';
        commentInput.style.height = commentInput.scrollHeight + 'px';

        const count = commentInput.value.length;
        const maxLength = commentInput.maxLength;
        charCount.textContent = count + '/' + maxLength;

        charCount.style.color = count > (maxLength - 20) ? '#dc3545' : '#6c757d';
    });
</script>

<?php include 'lightbox.php'; ?>

</body>
</html>
