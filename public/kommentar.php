<?php

// Überprüfe, ob die benötigten Variablen gesetzt sind
if (!isset($comment_for_template) || !isset($currentUser) || !isset($postId)) {
    echo '<p style="color: red;">Fehler: Notwendige Daten für Kommentar-Anzeige fehlen.</p>';
    return;
}

// Berechtigung zum Löschen des Kommentars prüfen
$isOwner = (int)$comment_for_template['userId'] === (int)$currentUser['id'];
$isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
$canDeleteComment = $isOwner || $isAdmin;

?>

<article class="post comment-layout">
    <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="no-post-details comment-profil-link">
        <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-user-image" loading="lazy" alt="Profilbild von <?php echo htmlspecialchars($comment_for_template['autor']); ?>">
    </a>

    <main class="post-main-content">
        <section class="post-user-infos">
            <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="no-post-details comment-profil-link-inline">
                <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-user-image-inline" loading="lazy" alt="">
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

        <div class="form-submit-area mt">
            <?php
            $antwortenCount = isset($comment_for_template['antworten']) ? count($comment_for_template['antworten']) : 0;
            ?>
            <button class="reply-button" onclick="toggleReplyForm(<?php echo $comment_for_template['id']; ?>)">
                Antworten<?php echo $antwortenCount > 0 ? " ({$antwortenCount})" : ""; ?>
            </button>


            <!-- Antwortformular (initial versteckt) -->
            <div class="reply-form hidden" id="reply-form-<?php echo $comment_for_template['id']; ?>">
                <form method="POST" action="php/post_action_handler.php" class="reply-comment-form">
                    <input type="hidden" name="action" value="reply_comment">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <input type="hidden" name="parent_comment_id" value="<?php echo $comment_for_template['id']; ?>">

                    <textarea name="comment_text" placeholder="Antwort schreiben..." maxlength="300" required></textarea>
                    <div class="reply-submit-area">
                        <p class="character-count">0/300</p>
                        <button class="reply-button" type="submit">Senden</button>
                    </div>
                </form>

                <?php if (!empty($comment_for_template['antworten'])): ?>
                    <div class="replies-list">
                        <?php foreach ($comment_for_template['antworten'] as $antwort): ?>
                            <article class="reply-comment">
                                <div class="reply-user">
                                    <a href="Profil.php?userid=<?php echo htmlspecialchars($antwort['userId']); ?>">
                                        <?php echo htmlspecialchars($antwort['autor']); ?>
                                    </a>
                                    <time datetime="<?php echo htmlspecialchars($antwort['datumZeit']); ?>">
                                        <?php
                                        $date = new DateTime($antwort['datumZeit']);
                                        echo $date->format('d.m.y, H:i');
                                        ?>
                                    </time>
                                </div>
                                <p><?php echo htmlspecialchars($antwort['text']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-replies-text">Keine Antworten vorhanden.</p>
                <?php endif; ?>

            </div>
        </div>
    </main>
</article>
