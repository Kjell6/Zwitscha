<?php
    require_once __DIR__ . '/php/PostVerwaltung.php';
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    require_once __DIR__ . '/php/session_helper.php';
    require_once __DIR__ . '/php/helpers.php';

// === Initialisierung ===
    requireLogin();

    $currentUserId = getCurrentUserId();
    $nutzerVerwaltung = new NutzerVerwaltung();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);

    $postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$postId) {
        header("Location: index.php");
        exit();
    }

    $repository = new PostVerwaltung();

    // === Daten für Detailansicht laden ===
    $post = $repository->getPostById($postId, $currentUserId);
    $comments = $repository->getMainCommentsByPostId($postId);

    // Wenn der Post nicht gefunden wurde, zur Startseite umleiten.
    if (!$post) {
        header("Location: index.php");
        exit();
    }

// === Template-Variablen ===
    $reactionEmojiMap = getReactionEmojiMap();
    $pageTitle = $post ? 'Post von ' . htmlspecialchars($post['autor']) : 'Post Details';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include 'global-header.php'; ?>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/postDetail.css">
    <link rel="stylesheet" href="css/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <?php include 'pwa-header.php'; ?>
</head>
<body>

<!-- === HEADER === -->
<?php include 'headerDesktop.php'; ?>
<?php include 'headerMobile.php'; ?>

<!-- === MAIN CONTAINER === -->
<main class="container">
    <!-- === PAGE HEADER === -->
    <div class="page-header no-divider">
        <a href="#" class="back-link" onclick="history.back(); return false;" aria-label="Zurück zur vorherigen Seite">
            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
        </a>
        <h1 class="page-title">Post</h1>
    </div>

    <?php
    ?>
    <article class="detail-post">
        <!-- === DETAIL POST === -->
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
            <!-- Post Options -->
            <?php
            // Berechtigung zum Löschen prüfen
            $isOwner = (int)$post['userId'] === (int)$currentUser['id'];
            $isAdmin = isset($currentUser['istAdministrator']) && $currentUser['istAdministrator'];
            $canDeletePost = ($isAdmin || $isOwner);
            if ($canDeletePost):
                ?>
                <form class="delete-form" data-type="post" data-post-id="<?php echo $post['id']; ?>" style="display: inline;">
                    <input type="hidden" name="action" value="delete_post">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button class="post-options-button no-post-details" type="submit" aria-label="Post löschen">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            <?php endif; ?>
        </section>

            <!-- === POST CONTENT === -->
            <div class="post-content-detail">
                <p><?php echo linkify_content($post['text'], $nutzerVerwaltung); ?></p>

                <!-- Post Image -->
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

            <!-- === POST ACTIONS === -->
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
                        <form style="display:inline" class="reaction-form">
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

        <!-- === COMMENT CREATION FORM (AJAX) === -->
        <form class="create-post-form comment-form" data-post-id="<?php echo $post['id']; ?>">
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

    <!-- === COMMENTS SECTION === -->
    <section class="comments-section">
        <!-- Empty State -->
        <?php if (empty($comments)): ?>
            <div class="empty-state">
                <i class="bi bi-chat-dots" style="font-size: 32px; margin-bottom: 15px;"></i>
                <h3>Noch keine Kommentare</h3>
                <p>Sei der Erste, der einen Kommentar schreibt!</p>
            </div>
        <!-- Comments List -->
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

<!-- === FOOTER === -->
<?php include 'footerMobile.php'; ?>

<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<?php include 'lightbox.php'; ?>

<!-- Refactored JavaScript-Funktionalität -->
<script src="js/textarea-utils.js"></script>
<script src="js/comment-utils.js"></script>

<!-- AJAX-Funktionalität -->
<script src="js/ajax/utils.js"></script>
<script src="js/ajax/reactions.js"></script>
<script src="js/ajax/posts.js"></script>
<script src="js/ajax/comments.js"></script>

<script>
    // === SEITENINITIALISIERUNG ===
    document.addEventListener('DOMContentLoaded', () => {
        // Initialisiert das Kommentar-System (Antworten, Löschen etc.)
        initializeCommentSystem();
        
        // Initialisiert die Textarea mit Zeichenzähler und automatischer Höhenanpassung
        initializeTextareaWithCounter({
            textareaId: 'post-input',
            counterSelector: '.character-count',
            maxLength: 300,
            warningThreshold: 280
        });

        // Initialisiert die Textarea mit Zeichenzähler und automatischer Höhenanpassung
        initializeTextareaWithCounter({
            textareaId: 'answer-input',
            counterSelector: '.character-count-answer',
            maxLength: 300,
            warningThreshold: 280
        });
    });
</script>

</body>
</html>