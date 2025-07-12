<?php
// Überprüfe, ob die benötigten Variablen gesetzt sind
if (!isset($comment) || !isset($currentUser)) {
    echo '<p style="color: red;">Fehler: Notwendige Daten für Kommentar-Anzeige fehlen.</p>';
    return;
}

// Berechtigung zum Löschen des Kommentars prüfen
$isOwner = (int)$comment['userId'] === (int)$currentUser['id'];
$isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
$canDeleteComment = $isOwner || $isAdmin;

?>

<article class="post comment-item">
    <a href="Profil.php?userid=<?php echo htmlspecialchars($comment['userId']); ?>" class="no-post-details">
        <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($comment['userId']); ?>" class="post-user-image" loading="lazy" alt="Profilbild von <?php echo htmlspecialchars($comment['autor']); ?>">
    </a>

    <main class="post-main-content">
        <section class="post-user-infos">
            <div>
                <a href="Profil.php?userid=<?php echo htmlspecialchars($comment['userId']); ?>" class="post-author-name">
                    <?php echo htmlspecialchars($comment['autor']); ?>
                </a>
                <time datetime="<?php echo htmlspecialchars($comment['datumZeit']); ?>" class="post-timestamp">
                    <?php echo time_ago($comment['datumZeit']); ?>
                </time>
            </div>

            <?php if ($canDeleteComment): ?>
                <form method="POST" action="php/post_action_handler.php" style="display: inline;" onsubmit="return confirm('Kommentar wirklich löschen?');">
                    <input type="hidden" name="action" value="delete_comment">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button class="post-options-button no-post-details" type="submit" aria-label="Kommentar löschen">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </section>

        <div class="post-content">
            <p><?php echo linkify_content($comment['text'], $nutzerVerwaltung); ?></p>
        </div>

        <!-- Ursprünglicher Post-Kontext -->
        <div class="comment-context" onclick="window.location.href='postDetails.php?id=<?php echo $comment['post_id']; ?>'" style="cursor: pointer;">
            <div class="comment-context-info">
                <i class="bi bi-reply"></i>
                <span>Antwort an</span>
                <a href="Profil.php?userid=<?php echo htmlspecialchars($comment['postAutorId']); ?>" class="context-author" onclick="event.stopPropagation();">
                    <?php echo htmlspecialchars($comment['postAutor']); ?>
                </a>
                <time datetime="<?php echo htmlspecialchars($comment['postDatum']); ?>" class="context-timestamp">
                    · <?php echo time_ago($comment['postDatum']); ?>
                </time>
            </div>
            <div class="comment-context-content">
                <p><?php echo linkify_content(mb_substr($comment['postText'], 0, 120) . (mb_strlen($comment['postText']) > 120 ? '...' : ''), $nutzerVerwaltung); ?></p>
                <?php if (!empty($comment['postBildDaten'])): ?>
                    <div class="post-image-container">
                        <img loading="lazy" src="getImage.php?type=post&id=<?php echo $comment['post_id']; ?>"
                             alt="Bild des Original-Posts"
                             class="post-image no-post-details"
                             onclick="event.stopPropagation(); openLightbox('getImage.php?type=post&id=<?php echo $comment['post_id']; ?>')"
                             style="cursor: pointer;">
                    </div>
                <?php endif; ?>
            </div>
            <div class="view-full-post">
                <i class="bi bi-arrow-return-right"></i>
                Vollständigen Post anzeigen
            </div>
        </div>
    </main>
</article> 