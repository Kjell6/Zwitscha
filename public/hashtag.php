<?php
    require_once __DIR__ . '/php/PostVerwaltung.php';
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    require_once __DIR__ . '/php/session_helper.php';
    require_once __DIR__ . '/php/helpers.php';

// === Initialisierung ===
    requireLogin();

    $tag = $_GET['tag'] ?? null;
    if (empty($tag)) {
        header('Location: index.php');
        exit();
    }

    $postRepository = new PostVerwaltung();
    $nutzerVerwaltung = new NutzerVerwaltung();
    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);

// === Daten für Hashtag-Anzeige laden ===
    $limit = 15;
    $posts = $postRepository->getPostsByHashtag($tag, $currentUserId, $limit, 0);
    $comments = $postRepository->getCommentsByHashtag($tag, $limit, 0);

    // Posts und Kommentare in einem gemeinsamen Array kombinieren
    $feedItems = [];

    // Posts hinzufügen
    foreach ($posts as $post) {
        $feedItems[] = [
            'type' => 'post',
            'data' => $post,
            'timestamp' => $post['datumZeit']
        ];
    }

    // Kommentare hinzufügen
    foreach ($comments as $comment) {
        $feedItems[] = [
            'type' => 'comment',
            'data' => $comment,
            'timestamp' => $comment['datumZeit']
        ];
    }

    // Nach Datum sortieren (neueste zuerst)
    usort($feedItems, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Auf das Limit beschränken (da wir Posts und Kommentare kombinieren)
    $feedItems = array_slice($feedItems, 0, $limit);

    $pageTitle = 'Posts und Kommentare mit #' . htmlspecialchars($tag);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include 'global-header.php'; ?>
    <link rel="stylesheet" href="css/post.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/kommentarEinzeln.css">
</head>
<body>
<?php include 'headerDesktop.php'; ?>

<div class="main-content">
    <h1 class="page-title"><?php echo $pageTitle; ?></h1>

    <!-- Dynamischer Feed -->
    <section class="feed" id="hashtag-feed">
        <?php
        if (empty($feedItems)) {
            ?>
            <div class="empty-state">
                <i class="bi bi-tag" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>Keine Inhalte gefunden</h3>
                <p>Es gibt noch keine Posts oder Kommentare mit dem Hashtag #<?php echo htmlspecialchars($tag); ?>.</p>
                <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
            </div>
            <?php
        } else {
            foreach ($feedItems as $item) {
                if ($item['type'] === 'post') {
                    // Post anzeigen
                    $post = $item['data'];
                    include 'post.php';
                } else {
                    // Kommentar anzeigen
                    $comment = $item['data'];
                    include 'kommentarEinzeln.php';
                }
            }
        }
        ?>
    </section>

    <!-- "Mehr laden"-Button -->
    <?php if (count($feedItems) === $limit): ?>
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
<script src="js/comment-utils.js"></script>
<script src="js/pagination.js"></script>

<!-- AJAX-Funktionalität -->
<script src="js/ajax/utils.js"></script>
<script src="js/ajax/reactions.js"></script>
<script src="js/ajax/posts.js"></script>
<script src="js/ajax/comments.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initial setup für bereits geladene Kommentare
        initializeCommentSystem();

        const moreButton = document.getElementById('mehr-laden-button');
        if (moreButton) {
            // "Mehr laden"-Funktionalität
            const hashtag = '<?php echo htmlspecialchars($tag, ENT_QUOTES); ?>';
            const limit = <?php echo $limit; ?>;
            
            initializePagination({
                containerId: 'hashtag-feed',
                buttonId: 'mehr-laden-button',
                buttonContainerId: 'mehr-laden-container',
                context: 'hashtag',
                limit: limit,
                initialOffset: limit,
                params: { tag: hashtag }
            });
        }
    });
</script>

</body>
</html> 