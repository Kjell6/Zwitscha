<?php

// Überprüfe ob die benötigten Variablen gesetzt sind
if (!isset($post) || !isset($currentUser)) {
    die('Post-Daten oder currentUser nicht verfügbar');
}

$canDelete = ($post['autor'] === $currentUser);
?>

<article class="post" data-post-id="<?php echo $post['id']; ?>">
    <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
        <img src="<?php echo htmlspecialchars($post['profilBild']); ?>" class="post-user-image">
    </a>
    <main class="post-main-content">
        <section class="post-user-infos">
            <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
                <img src="<?php echo htmlspecialchars($post['profilBild']); ?>" class="post-user-image-inline">
            </a>
            <div class="post-user-details">
                <a href="Profil.php" class="post-author-name">
                    <?php echo htmlspecialchars($post['autor']); ?>
                </a>
                <time datetime="<?php echo $post['datumZeit']; ?>" class="post-timestamp">
                    <?php echo htmlspecialchars($post['time_label']); ?>
                </time>
            </div>
            <?php if ($canDelete && $post['id'] != 2 && $post['id'] != 4): ?>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Post wirklich löschen?');">
                    <input type="hidden" name="action" value="delete_post">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button class="post-options-button no-post-details" type="submit" aria-label="Post löschen">
                        <?php // Hier später Datenbank-Interaktion zum Löschen des Posts ?>
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </section>
        <div class="post-content">
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
        <div class="post-actions">
            <div class="post-reactions">
                <?php
                $emojis = ['👍', '👎', '❤️', '🤣', '❓', '‼️'];
                foreach ($emojis as $emoji):
                    $count = $post['reactions'][$emoji] ?? 0;
                    ?>
                    <form method="POST" style="display: inline;" class="reaction-form">
                        <input type="hidden" name="action" value="toggle_reaction">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="emoji" value="<?php echo $emoji; ?>">
                        <button class="reaction-button no-post-details" type="submit" data-emoji="<?php echo $emoji; ?>">
                            <?php // Hier später Datenbank-Interaktion zum Togglen der Reaktion ?>
                            <?php echo $emoji; ?> <span class="reaction-counter"><?php echo $count; ?></span>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
            <a href="postDetails.php?id=<?php echo $post['id']; ?>" class="comment-link">
                <button class="action-button comment-button" type="button">
                    <i class="bi bi-chat-dots-fill"></i> <?php echo $post['comments']; ?> Kommentar<?php echo $post['comments'] != 1 ? 'e' : ''; ?>
                </button>
            </a>
        </div>
    </main>
</article>