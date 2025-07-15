<?php
    require_once __DIR__ . '/php/helpers.php';

// === Initialisierung ===
    if (!isset($post)) {
        die('Post-Daten nicht verfügbar');
    }

// === Aktueller Benutzer laden ===
    if (!isset($currentUser)) {
        require_once __DIR__ . '/php/NutzerVerwaltung.php';
        require_once __DIR__ . '/php/session_helper.php';
        $nutzerVerwaltung = new NutzerVerwaltung();
        $currentUserId = getCurrentUserId() ?? 0;
        $currentUser = $currentUserId ? $nutzerVerwaltung->getUserById($currentUserId) : null;
    }

    // NutzerVerwaltung für Template bereitstellen
    if (!isset($nutzerVerwaltung)) {
        require_once __DIR__ . '/php/NutzerVerwaltung.php';
        $nutzerVerwaltung = new NutzerVerwaltung();
    }

// === Berechtigung prüfen ===
    $isOwner = $currentUser && (int)$post['userId'] === (int)$currentUser['id'];
    $isAdmin = $currentUser && isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
    $canDelete = ($isAdmin || $isOwner);

    // Relative Zeit berechnen
    $time_label = time_ago($post['datumZeit']);

    // Mapping von DB-Reaktionstypen zu Emojis (zentral definiert)
    $reactionEmojiMap = getReactionEmojiMap();
?>

<!-- === POST CONTAINER === -->
<article class="post" id="post-<?php echo $post['id']; ?>" data-post-id="<?php echo $post['id']; ?>" onclick="navigateToPost(event, <?php echo $post['id']; ?>)">
    <!-- Profile Image (Desktop) -->
    <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
        <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($post['userId']); ?>" class="post-user-image" loading="lazy"  alt="Profil-Bild">
    </a>
    
    <!-- === POST MAIN CONTENT === -->
    <main class="post-main-content">
        <!-- === POST HEADER === -->
        <section class="post-user-infos">
            <!-- Profile Image (Mobile) -->
            <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
                <img src="getImage.php?type=user&id=<?php echo htmlspecialchars($post['userId']); ?>" class="post-user-image-inline" loading="lazy"  alt="Profil-Bild">
            </a>
            
            <!-- User Details -->
            <div class="post-user-details">
                <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="post-author-name">
                    <?php echo htmlspecialchars($post['autor']); ?>
                </a>
                <time datetime="<?php echo htmlspecialchars($post['datumZeit']); ?>" class="post-timestamp">
                    <?php echo htmlspecialchars($time_label); ?>
                </time>
            </div>
            
            <!-- Post Options -->
            <?php if ($canDelete): ?>
                <form method="POST" action="php/post_action_handler.php" style="display: inline;" onsubmit="return confirm('Post wirklich löschen?');">
                    <input type="hidden" name="action" value="delete_post">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button class="post-options-button no-post-details" type="submit" aria-label="Post löschen">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </section>
        
        <!-- === POST CONTENT === -->
        <div class="post-content">
            <p><?php echo linkify_content($post['text'], $nutzerVerwaltung); ?></p>
            
            <!-- === POST IMAGES === -->
            <?php if (!empty($post['bildDaten'])): ?>
                <div class="post-image-container">
                    <img loading="lazy" src="getImage.php?type=post&id=<?php echo $post['id']; ?>"
                         alt="Post-Bild"
                         class="post-image no-post-details"
                         onclick="openLightbox('getImage.php?type=post&id=<?php echo $post['id']; ?>')"
                         style="cursor: pointer;">
                </div>
            <?php endif; ?>
        </div>
        
        <!-- === POST ACTIONS === -->
        <div class="post-actions">
            <!-- Post Reactions -->
            <div class="post-reactions">
                <?php
                $emojis = ['👍', '👎', '❤️', '🤣', '❓', '‼️'];
                foreach ($emojis as $emoji):
                    $count = $post['reactions'][$emoji] ?? 0;
                    $reactionTypeFromEmoji = array_search($emoji, $reactionEmojiMap);
                    $isActive = in_array($reactionTypeFromEmoji, $post['currentUserReactions']);
                    ?>
                    <form method="POST" action="php/reaction_handler.php" style="display: inline;" class="reaction-form">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="emoji" value="<?php echo $emoji; ?>">
                        <button class="reaction-button no-post-details <?php echo $isActive ? 'active' : ''; ?>" type="submit" data-emoji="<?php echo $emoji; ?>">
                            <?php echo $emoji; ?> <span class="reaction-counter"><?php echo $count; ?></span>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
            <!-- Action Buttons -->
            <a href="postDetails.php?id=<?php echo $post['id']; ?>" class="comment-link">
                <button class="action-button comment-button" type="button">
                    <i class="bi bi-chat-dots-fill"></i> <?php echo $post['comments']; ?> Kommentar<?php echo $post['comments'] != 1 ? 'e' : ''; ?>
                </button>
            </a>
        </div>
    </main>
</article>

<?php
if (!function_exists('render_post_script')) {
    function render_post_script() {
        return '
        <script>
            // === POST-NAVIGATION ===
            function navigateToPost(event, postId) {
                // Verhindere Navigation bei interaktiven Elementen (Buttons, Links)
                if (event.target.closest(".no-post-details")) {
                    return;
                }
                // Navigiere zur Post-Detail-Seite
                window.location.href = "postDetails.php?id=" + postId;
            }
        </script>
        ';
    }
    echo render_post_script();
}
?>