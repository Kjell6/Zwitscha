<?php
// === Initialisierung ===
    if (!isset($comment_for_template) || !isset($currentUser) || !isset($postId)) {
        echo '<p style="color: red;">Fehler: Notwendige Daten für Kommentar-Anzeige fehlen.</p>';
        return;
    }

// === Berechtigung prüfen ===
    $isOwner = (int)$comment_for_template['userId'] === (int)$currentUser['id'];
    $isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
    $canDeleteComment = $isOwner || $isAdmin;
?>

<!-- === COMMENT CONTAINER === -->
<article class="post comment-layout">
    <!-- Profile Image (Desktop) -->
    <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="no-post-details comment-profil-link">
        <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-user-image" loading="lazy" alt="Profilbild von <?php echo htmlspecialchars($comment_for_template['autor']); ?>">
    </a>

    <!-- === COMMENT MAIN CONTENT === -->
    <main class="post-main-content">
        <!-- === COMMENT HEADER === -->
        <section class="post-user-infos">
            <!-- Profile Image (Mobile) -->
            <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="no-post-details comment-profil-link-inline">
                <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-user-image-inline" loading="lazy" alt="">
            </a>

            <!-- User Details -->
            <div class="post-user-details">
                <a href="Profil.php?userid=<?php echo htmlspecialchars($comment_for_template['userId']); ?>" class="post-author-name">
                    <?php echo htmlspecialchars($comment_for_template['autor']); ?>
                </a>
                <time datetime="<?php echo htmlspecialchars($comment_for_template['datumZeit']); ?>" class="post-timestamp">
                    <?php echo htmlspecialchars($comment_for_template['time_label']); ?>
                </time>
            </div>

            <!-- Comment Options -->
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

        <!-- === COMMENT CONTENT === -->
        <div class="post-content">
            <p><?php echo linkify_content($comment_for_template['text'], $nutzerVerwaltung); ?></p>
        </div>

        <!-- === COMMENT ACTIONS === -->
        <div class="form-submit-area mt">
            <?php
            $antwortenCount = isset($comment_for_template['antworten']) ? count($comment_for_template['antworten']) : 0;
            ?>
            <button class="reply-button" onclick="toggleReplyForm(<?php echo $comment_for_template['id']; ?>)">
                <i class="bi bi-chat-dots-fill"></i> Antworten<?php echo $antwortenCount > 0 ? " ({$antwortenCount})" : ""; ?>
            </button>
        </div>

        <!-- === REPLY SECTION === -->
        <div class="reply-section hidden" id="reply-form-<?php echo $comment_for_template['id']; ?>">
            <!-- Reply Form -->
            <form method="POST" action="php/post_action_handler.php" class="create-post-form">
                <input type="hidden" name="action" value="reply_comment">
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                <input type="hidden" name="parent_comment_id" value="<?php echo $comment_for_template['id']; ?>">

                <div class="form-header">
                    <img class="user-avatar" src="getImage.php?type=user&id=<?php echo $currentUserId; ?>" loading="lazy" alt="Dein Profilbild">
                    <textarea name="comment_text" placeholder="Antwort schreiben..." maxlength="300" required></textarea>
                </div>

                <div class="form-footer">
                    <div class="form-actions"></div>
                    <div class="form-submit-area">
                        <p class="character-count">0/300</p>
                        <button class="post-button" type="submit">Antworten</button>
                    </div>
                </div>
            </form>

            <!-- === REPLIES LIST === -->
            <?php if (!empty($comment_for_template['antworten'])): ?>
                <div class="replies-list">
                    <?php foreach ($comment_for_template['antworten'] as $antwort): ?>
                        <?php
                            $canDeleteReply = ((int)$antwort['userId'] === (int)$currentUser['id']) || (isset($currentUser['istAdministrator']) && $currentUser['istAdministrator']);
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
                                            <?php
                                            $date = new DateTime($antwort['datumZeit']);
                                            echo $date->format('d.m.y, H:i');
                                            ?>
                                        </time>
                                    </div>
                                    <?php if ($canDeleteReply): ?>
                                        <form method="POST" action="php/post_action_handler.php" style="display: inline;" onsubmit="return confirm('Antwort wirklich löschen?');">
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
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</article>
