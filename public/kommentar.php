<?php


if (!isset($comment_for_template) || !isset($currentUser) || !isset($postId)) {
    echo '<p style="color: red;">Fehler: Notwendige Daten für Kommentar-Anzeige fehlen.</p>';
    return;
}

$isOwner = (int)$comment_for_template['userId'] === (int)$currentUser['id'];
$isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
$canDeleteComment = $isOwner || $isAdmin;
?>

<article class="post comment-layout" id="comment-<?php echo $comment_for_template['id']; ?>">
    <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="no-post-details comment-profil-link">
        <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-user-image" alt="Profilbild von <?php echo htmlspecialchars($comment_for_template['autor']); ?>">
    </a>

    <main class="post-main-content">
        <section class="post-user-infos">
            <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="no-post-details comment-profil-link-inline">
                <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-user-image-inline" alt="">
            </a>

            <div class="post-user-details">
                <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-author-name">
                    <?php echo htmlspecialchars($comment_for_template['autor']); ?>
                </a>
                <time datetime="<?php echo htmlspecialchars($comment_for_template['datumZeit']); ?>" class="post-timestamp">
                    <?php echo htmlspecialchars($comment_for_template['time_label']); ?>
                </time>
            </div>

            <?php if ($canDeleteComment): ?>
                <form method="POST" action="php/post_action_handler.php" style="display: inline;" onsubmit="return confirm('Kommentar wirklich löschen?');">
                    <input type="hidden" name="action" value="delete_comment">
                    <input type="hidden" name="comment_id" value="<?php echo $comment_for_template['id']; ?>">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <button class="post-options-button no-post-details" type="submit" aria-label="Kommentar löschen">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </section>

        <div class="post-content">
            <p><?php echo linkify_content($comment_for_template['text'], $nutzerVerwaltung); ?></p>
        </div>

        <!-- Antwort Button -->
        <button class="reply-button" type="button" onclick="toggleReplyForm(<?php echo $comment_for_template['id']; ?>)">
            Antworten
        </button>

        <!-- Antwort Formular, standardmäßig versteckt -->
        <form method="POST" action="php/post_action_handler.php" class="reply-form" id="reply-form-<?php echo $comment_for_template['id']; ?>" style="display:none; margin-top: 0.5rem;">
            <input type="hidden" name="action" value="create_comment" />
            <input type="hidden" name="post_id" value="<?php echo $postId; ?>" />
            <input type="hidden" name="parent_id" value="<?php echo $comment_for_template['id']; ?>" />

            <textarea name="comment_text" placeholder="Antwort schreiben..." maxlength="300" required style="width: 100%; min-height: 4rem;"></textarea>
            <button type="submit" style="margin-top: 0.25rem;">Antwort senden</button>
        </form>
    </main>
</article>

<script>
    function toggleReplyForm(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>
