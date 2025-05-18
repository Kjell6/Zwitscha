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
    <link rel="stylesheet" href="css/post.css">


    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">

</head>

<body>
    <?php include 'headerDesktop.php'; ?>

    <main class="container">

        <div class="profil-header">
            <img src="assets/placeholder-profilbild.jpg" alt="Profilbild" class="profilbild">

            <div class="profil-header pb-name-untereinander">
                <div class="profil-main-infos">
                    <h1 class="profil-name">Nutzername</h1>
                    <button id="folgenButton" class="folgen-button" type="button">Folgen</button>
                </div>
                <p class="beitritts-datum"><i class="bi bi-calendar2-fill"></i>  Beigetreten Januar 2025</p>
                <div class="folgen-container">
                    <p class="folge-info"><strong>85</strong> <span>Follower</span></p>
                    <p class="folge-info"><strong>123</strong> <span>Folge ich</span></p>
                    <p class="folge-info"><strong>211</strong> <span>post</span></p>
                </div>
            </div>

            <a href="einstellungen.php" class="einstellungen-link mobile-only">
                <i class="bi bi-gear-fill"></i>
            </a>
        </div>


        <div class="posts">
            <h2>Posts</h2>

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
        </div>

        <?php include 'footerMobile.php'; ?>

    </main>

    <footer>
        <p>&copy; 2025 Zwitscha</p>
    </footer>

</body>
</html>