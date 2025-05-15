<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'headerDesktop.php'; ?>

    <a href="index.php">
        <button type="button">Zurück zur Startseite</button>
    </a>
    <!-- Abmelden Button -->
    <a href="Login.html">
        <button type="button">Abmelden</button>
    </a>

    <h1>Nutzername</h1>


    <!-- Profilbild Platzhalter -->
    <img src="assets/placeholder-profilbild.jpg" alt="Profilbild" width="60" height="60">

    <!-- Anzeige der Posts -->
    <h2>Posts</h2>

    <!-- Beispiel für die vergangene Posts -->
    <section>
        <ul id="feed-nur als test wie es aussieht">
            <li class="posts">
                <div id="post-placeholder"></div>
            </li>
        </ul>
    </section>

    <?php include 'footerMobile.php'; ?>

    <script>
        fetch('post.html')
            .then(response => response.text())
            .then(data => {
                document.getElementById('post-placeholder').innerHTML = data;
            });
    </script>


</body>
</html>