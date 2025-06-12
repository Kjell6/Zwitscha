<?php

// ---- HILFSFUNKTION FÜR ZEITANGABE ----
if (!function_exists('time_ago')) {
    function time_ago(string $datetime, string $full = 'vor %s'): string {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        // Berechne Wochen separat ohne dynamische Eigenschaften
        $weeks = floor($diff->d / 7);
        $days = $diff->d - ($weeks * 7);

        $values = [
            'y' => $diff->y,
            'm' => $diff->m,
            'w' => $weeks,
            'd' => $days,
            'h' => $diff->h,
            'i' => $diff->i,
            's' => $diff->s,
        ];

        $string = [
            'y' => 'Jahr',
            'm' => 'Monat',
            'w' => 'Woche',
            'd' => 'Tag',
            'h' => 'Stunde',
            'i' => 'Minute',
            's' => 'Sekunde',
        ];

        foreach ($string as $k => &$v) {
            if ($values[$k]) {
                $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 'n' : '');
            } else {
                unset($string[$k]);
            }
        }

        // Nur die größte Zeiteinheit anzeigen
        if (!empty($string)) {
            $string = array_slice($string, 0, 1);
        }

        $time_ago = $string ? implode(', ', $string) : 'gerade jetzt';
        return sprintf($full, $time_ago);
    }
}

// Überprüfe ob die benötigten Variablen gesetzt sind
if (!isset($post)) {
    die('Post-Daten nicht verfügbar');
}

// ---- AKTUELLER BENUTZER ----
// Verwende $currentUser aus der einbindenden Datei oder lade ihn aus der Datenbank
if (!isset($currentUser)) {
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    $nutzerVerwaltung = new NutzerVerwaltung();
    $currentUser = $nutzerVerwaltung->getUserById(1); // Hardcoded für Development
}

// Berechtigung zum Löschen prüfen: Ist der Nutzer Admin ODER der Autor des Posts?
$isOwner = (int)$post['userId'] === (int)$currentUser['id'];
$isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
$canDelete = ($isAdmin || $isOwner);

// Relative Zeit berechnen
$time_label = time_ago($post['datumZeit']);

// Mapping von DB-Reaktionstypen zu Emojis
$reactionEmojiMap = [
    'Daumen Hoch' => '👍',
    'Daumen Runter' => '👎',
    'Herz' => '❤️',
    'Lachen' => '🤣',
    'Fragezeichen' => '❓',
    'Ausrufezeichen' => '‼️',
];
?>

<article class="post" id="post-<?php echo $post['id']; ?>" data-post-id="<?php echo $post['id']; ?>">
    <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
        <img src="<?php echo htmlspecialchars($post['profilBild'] ?? 'assets/placeholder-profilbild.jpg'); ?>" class="post-user-image">
    </a>
    <main class="post-main-content">
        <section class="post-user-infos">
            <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="no-post-details">
                <img src="<?php echo htmlspecialchars($post['profilBild'] ?? 'assets/placeholder-profilbild.jpg'); ?>" class="post-user-image-inline">
            </a>
            <div class="post-user-details">
                <a href="Profil.php?userid=<?php echo htmlspecialchars($post['userId']); ?>" class="post-author-name">
                    <?php echo htmlspecialchars($post['autor']); ?>
                </a>
                <time datetime="<?php echo htmlspecialchars($post['datumZeit']); ?>" class="post-timestamp">
                    <?php echo htmlspecialchars($time_label); ?>
                </time>
            </div>
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
                    $reactionTypeFromEmoji = array_search($emoji, $reactionEmojiMap);
                    $isActive = in_array($reactionTypeFromEmoji, $post['currentUserReactions']);
                    ?>
                    <form method="POST" action="php/post_action_handler.php" style="display: inline;" class="reaction-form">
                        <input type="hidden" name="action" value="toggle_reaction">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="emoji" value="<?php echo $emoji; ?>">
                        <button class="reaction-button no-post-details <?php echo $isActive ? 'active' : ''; ?>" type="submit" data-emoji="<?php echo $emoji; ?>">
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