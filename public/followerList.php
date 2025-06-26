<?php
require_once __DIR__ . '/php/NutzerVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';

// Prüfen ob angemeldet
requireLogin();

$nutzerVerwaltung = new NutzerVerwaltung();

// Parameter aus der URL holen
$profileId = isset($_GET['userid']) ? (int)$_GET['userid'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'followers'; // Standardmäßig 'followers'

if ($profileId === 0) {
    header("Location: index.php");
    exit();
}

// Profildaten des Nutzers laden, für den die Liste angezeigt wird
$profileUser = $nutzerVerwaltung->getUserById($profileId);
if (!$profileUser) {
    // Wenn der Nutzer nicht existiert, umleiten
    header("Location: index.php");
    exit();
}

// Daten basierend auf dem Typ laden
if ($type === 'following') {
    $title = "Wem " . htmlspecialchars($profileUser['nutzerName']) . " folgt";
    $userList = $nutzerVerwaltung->getFollowing($profileId);
} else {
    $title = "Follower von " . htmlspecialchars($profileUser['nutzerName']);
    $userList = $nutzerVerwaltung->getFollowers($profileId);
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/followerList.css">
</head>
<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <div class="page-header-container">
        <button onclick="history.back()" class="back-button" type="button">Zurück</button>
        <h1><?php echo $title; ?></h1>
    </div>

    <div class="user-list">
        <?php if (empty($userList)): ?>
            <div class="empty-state">
                <p>Hier gibt es noch nichts zu sehen.</p>
            </div>
        <?php else: ?>
            <?php foreach ($userList as $user): ?>
                <a href="Profil.php?userid=<?php echo $user['id']; ?>" class="user-list-item">
                    <img src="getImage.php?type=user&id=<?php echo $user['id']; ?>" alt="Profilbild" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($user['nutzerName']); ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'footerMobile.php'; ?>

</body>
</html> 