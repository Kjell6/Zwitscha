<?php
    require_once __DIR__ . '/php/PostVerwaltung.php';
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    require_once __DIR__ . '/php/session_helper.php';

// === Initialisierung ===
    $postRepository = new PostVerwaltung();
    $nutzerVerwaltung = new NutzerVerwaltung();

// === Daten für Feed-Anzeige laden ===

    requireLogin();

    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
    $limit = 15;

    $showFollowedOnly = isset($_GET['filter']) && $_GET['filter'] === 'followed';

    // Posts aus der Datenbank laden
    if ($showFollowedOnly) {
        $posts = $postRepository->getFollowedPosts($currentUserId, $limit, 0);
    } else {
        $posts = $postRepository->getAllPosts($currentUserId, $limit, 0);
    }
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <title>Startseite</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="js/image-compression.js"></script>
</head>
<body>
<!-- === HEADER === -->
<?php include 'headerDesktop.php'; ?>

<!-- === MAIN CONTENT === -->
<div class="main-content">
    <!-- === FEEDBACK MESSAGES (AJAX) === -->
    <div id="feedback-container" class="feedback-container" style="display: none;">
        <div id="feedback-message" class="feedback-message">
            <span id="feedback-text"></span>
        </div>
    </div>

    <!-- === CREATE POST FORM === -->
    <form enctype="multipart/form-data" class="create-post-form">
        <input type="hidden" name="action" value="create_post">

        <!-- Form Header -->
        <div class="form-header">
            <img class="user-avatar" src="getImage.php?type=user&id=<?php echo $currentUserId; ?>" loading="lazy" alt="Dein Profilbild">
            <textarea name="post_text" id="post-input" placeholder="Was gibt's Neues?" maxlength="300" required></textarea>
        </div>

        <!-- === IMAGE PREVIEW === -->
        <div class="image-preview" id="image-preview" style="display: none;">
            <img id="preview-img" src="#" alt="Bildvorschau">
            <button id="remove-image" type="button" aria-label="Bild entfernen"><i class="bi bi-x-lg"></i></button>
        </div>

        <!-- === FORM FOOTER === -->
        <div class="form-footer">
            <div class="form-actions">
                <label for="image-input" class="action-button" aria-label="Bild hinzufügen">
                    <i class="bi bi-image"></i>
                </label>
                <input type="file" name="post_image" id="image-input" accept="image/*" style="display: none;">
            </div>
            <div class="form-submit-area">
                <p class="character-count">0/300</p>
                <button id="post-button" type="submit">Posten</button>
            </div>
        </div>
    </form>

    <!-- === POST TOGGLE === -->
    <div class="switch-wrapper">
        <div class="post-toggle">
            <input type="radio" id="all-posts" name="post-filter" <?php echo !$showFollowedOnly ? 'checked' : ''; ?>
                   onchange="window.location.href='index.php'">
            <label for="all-posts">Alle Posts</label>
            <input type="radio" id="followed-posts" name="post-filter" <?php echo $showFollowedOnly ? 'checked' : ''; ?>
                   onchange="window.location.href='index.php?filter=followed'">
            <label for="followed-posts">Gefolgt</label>
            <span class="switch-indicator"></span>
        </div>
    </div>

    <!-- === FEED CONTAINER === -->
    <section class="feed" id="posts-container">
        <?php
        if (empty($posts)) {
            if ($showFollowedOnly) {
                ?>
                <!-- Empty State - Followed Posts -->
                <div class="empty-state">
                    <i class="bi bi-people" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Keine Posts von gefolgten Nutzern</h3>
                    <p>Du folgst noch niemandem oder deine gefolgten Nutzer haben noch keine Posts veröffentlicht.</p>
                    <a href="index.php" class="btn btn-primary">Alle Posts anzeigen</a>
                </div>
                <?php
            } else {
                ?>
                <!-- Empty State - All Posts -->
                <div class="empty-state">
                    <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Noch keine Posts vorhanden</h3>
                    <p>Verfasse den ersten Post, um die Community zu starten!</p>
                </div>
                <?php
            }
        } else {
            // Dynamic Post Content
            foreach ($posts as $post) {
                include 'post.php';
            }
        }
        ?>
    </section>

    <!-- === LOAD MORE SECTION === -->
    <?php if (count($posts) === $limit): ?>
    <div id="mehr-laden-container" style="display: flex; justify-content: center; margin: 20px 0;">
        <button id="mehr-laden-button" class="btn">Mehr laden</button>
    </div>
    <?php endif; ?>
</div>


<?php include 'footerMobile.php'; ?>
<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<?php include 'lightbox.php'; ?>

<!-- Refactored JavaScript-Funktionalität -->
<script src="js/textarea-utils.js"></script>
<script src="js/image-preview.js"></script>
<script src="js/pagination.js"></script>

<!-- AJAX-Funktionalität -->
<script src="js/ajax/utils.js"></script>
<script src="js/ajax/reactions.js"></script>
<script src="js/ajax/posts.js"></script>
<script src="js/ajax/comments.js"></script>

<script>
    // === SEITENINITIALISIERUNG ===
    document.addEventListener("DOMContentLoaded", () => {
        // "Mehr laden"-Funktionalität
        const context = "<?php echo $showFollowedOnly ? 'followed' : 'all'; ?>";
        const limit = <?php echo $limit; ?>;
        
        initializePagination({
            containerId: 'posts-container',
            buttonId: 'mehr-laden-button',
            buttonContainerId: 'mehr-laden-container',
            context: context,
            limit: limit,
            initialOffset: limit
        });
        
        // Zeichenzähler für Post-Textarea
        initializeTextareaWithCounter({
            textareaId: 'post-input',
            counterSelector: '.character-count',
            maxLength: 300,
            warningThreshold: 280
        });

        // Bildvorschau und -komprimierung
        initializeImagePreview({
            inputId: 'image-input',
            previewId: 'preview-img',
            previewContainerId: 'image-preview',
            removeButtonId: 'remove-image'
        });
    });
</script>
</body>
</html>