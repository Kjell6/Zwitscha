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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/kommentarEinzeln.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Funktion für comment-context Event-Handler
        function setupCommentContextHandlers() {
            const commentContexts = document.querySelectorAll('.comment-context');
            
            commentContexts.forEach(context => {
                const hashtagLinks = context.querySelectorAll('a.link');
                hashtagLinks.forEach(link => {
                    link.addEventListener('click', function(event) {
                        event.stopPropagation();
                    });
                });
            });
        }
        
        // Initial setup für bereits geladene Kommentare
        setupCommentContextHandlers();

        // "Mehr laden"-Funktionalität
        const feedContainer = document.getElementById('hashtag-feed');
        const buttonContainer = document.getElementById('mehr-laden-container');
        
        if (buttonContainer) {
            const button = document.getElementById('mehr-laden-button');
            const hashtag = '<?php echo htmlspecialchars($tag, ENT_QUOTES); ?>';
            let offset = <?php echo $limit; ?>;
            const limit = <?php echo $limit; ?>;

            button.addEventListener('click', function() {
                // Button-Status während des Ladens
                button.disabled = true;
                button.textContent = 'Lädt...';

                // Hashtag-Inhalte vom Server laden
                fetch(`php/get-posts.php?context=hashtag&tag=${encodeURIComponent(hashtag)}&offset=${offset}&limit=${limit}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Fehler beim Laden der Inhalte');
                        return response.text();
                    })
                    .then(html => {
                        if (!html.trim()) {
                            // Keine weiteren Inhalte vorhanden
                            if (buttonContainer) buttonContainer.style.display = 'none';
                        } else {
                            // Neue Inhalte hinzufügen und Offset aktualisieren
                            feedContainer.insertAdjacentHTML('beforeend', html);
                            offset += limit;
                            
                            // Event-Handler für neue Posts und Kommentare einrichten
                            setupCommentContextHandlers();
                            setupReactionHandlers();
                            
                            // AJAX-Handler für neue Inhalte einrichten
                            if (window.setupAjaxHandlers) {
                                window.setupAjaxHandlers();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Fehler beim Laden:', error);
                        button.textContent = 'Fehler!';
                    })
                    .finally(() => {
                        // Button-Status zurücksetzen
                        if (buttonContainer && buttonContainer.style.display !== 'none') {
                            button.disabled = false;
                            button.textContent = 'Mehr laden';
                        }
                    });
            });
        }
    });
</script>

<!-- AJAX-Funktionalität -->
<script src="js/ajax/utils.js"></script>
<script src="js/ajax/reactions.js"></script>
<script src="js/ajax/posts.js"></script>
<script src="js/ajax/comments.js"></script>

</body>
</html> 