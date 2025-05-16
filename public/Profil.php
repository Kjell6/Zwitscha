<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css"/>
    <link rel="stylesheet" href="css/profil.css"/>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">

</head>

<body>
    <?php include 'headerDesktop.php'; ?>

    <main class="container">

        <div class="profil-header">
            <!-- Profilbild Platzhalter -->
            <img src="assets/placeholder-profilbild.jpg" alt="Profilbild" width="150" height="150">

            <h1>Nutzername</h1>

            <a href="einstellungen.php" class="settings-link mobile-only">
                <i class="bi bi-gear-fill"></i>
            </a>

        </div>

        <div class="folgen-container">
            <button id="folgenButton" class="folgen-button" type="button">Folgen</button>
            <p class="follower-anzahl">123 Follower</p>
        </div>


        <!-- Beispiel für die vergangene Posts -->
        <section>
            <ul id="feed-nur als test wie es aussieht">
                <li>
                    <?php include 'post.php'; ?>
                </li>

                <li>
                    <?php include 'post.php'; ?>
                </li>
                <li>

                    <?php include 'post.php'; ?>
                </li>

                <li>
                    <?php include 'post.php'; ?>
                </li>
            </ul>
        </section>

        <?php include 'footerMobile.php'; ?>

    </main>

    <footer>
        <p>&copy; 2025 Zwitscha</p>
    </footer>

</body>
</html>