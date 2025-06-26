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

// Posts für den Hashtag laden
$posts = $postRepository->getPostsByHashtag($tag, $currentUserId);
$pageTitle = 'Posts mit #' . htmlspecialchars($tag);

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
        if (empty($posts)) {
            ?>
            <div class="empty-state">
                <i class="bi bi-tag" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>Keine Posts gefunden</h3>
                <p>Es gibt noch keine Posts mit dem Hashtag #<?php echo htmlspecialchars($tag); ?>.</p>
                <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
            </div>
            <?php
        } else {
            foreach ($posts as $post) {
                include 'post.php';
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