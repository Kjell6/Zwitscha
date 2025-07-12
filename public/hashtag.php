<?php
require_once __DIR__ . '/php/PostVerwaltung.php';
require_once __DIR__ . '/php/NutzerVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';
require_once __DIR__ . '/php/helpers.php';


// Prüfen ob angemeldet
requireLogin();

$tag = $_GET['tag'] ?? null;
if (empty($tag)) {
    header('Location: index.php');
    exit();
}

// Verwaltung instanziieren
$postRepository = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();

$currentUserId = getCurrentUserId();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

// Posts und Kommentare für den Hashtag laden
$posts = $postRepository->getPostsByHashtag($tag, $currentUserId);
$comments = $postRepository->getCommentsByHashtag($tag);

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
    <section class="feed">
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
</div>

<?php include 'footerMobile.php'; ?>
<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<?php include 'lightbox.php'; ?>

</body>
</html> 