<?php
// Dummy-Status speichern wir hier nur temporär in einer Variablen.
// In der echten App würdest du den Status in DB oder Session speichern.
$isFollowing = false;
$postResult = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $followerId = isset($_POST['followerId']) ? $_POST['followerId'] : '';
    $followeeId = isset($_POST['followeeId']) ? $_POST['followeeId'] : '';
    $currentStatus = isset($_POST['currentStatus']) ? $_POST['currentStatus'] : 'not_following'; // neuer Wert, der aktuellen Status beschreibt


    if ($followerId && $followeeId) {
        if ($currentStatus === 'following') {
            // Aktuell folgst du, also jetzt entfolgen
            $isFollowing = false;
            $postResult = "Entfolgt!<br>Follower-ID: $followerId<br>Followee-ID: $followeeId";
        } else {
            // Aktuell folgst du nicht, also jetzt folgen
            $isFollowing = true;
            $postResult = "Gefolgt!<br>Follower-ID: $followerId<br>Followee-ID: $followeeId";
        }
    } else {
        $postResult = "Fehler: Fehlende Daten.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil</title>

    <!-- CSS wie gehabt -->
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/profil.css" />
    <link rel="stylesheet" href="css/post.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">

    <div class="profil-header">
        <img src="assets/placeholder-profilbild.jpg" alt="Profilbild" class="profilbild" />

        <div class="profil-header pb-name-untereinander">
            <div class="profil-main-infos">
                <h1 class="profil-name">Nutzername</h1>

                <form id="follow-form" method="post" action="" style="display: inline;">
                    <input type="hidden" name="followerId" value="123" />
                    <input type="hidden" name="followeeId" value="456" />
                    <input type="hidden" name="currentStatus" value="<?php echo $isFollowing ? 'following' : 'not_following'; ?>" />

                    <button
                            id="folgenButton"
                            class="folgen-button <?php echo $isFollowing ? 'gefolgt' : ''; ?>"
                            type="submit"
                    >
                        <?php echo $isFollowing ? 'Gefolgt' : 'Folgen'; ?>
                    </button>
                </form>
            </div>

            <p class="beitritts-datum"><i class="bi bi-calendar2-fill"></i> Beigetreten Januar 2025</p>
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

    <?php if ($postResult): ?>
        <div class="post-result" style="margin: 1em 0; padding: 1em; border: 1px solid #ccc; background: #f9f9f9;">
            <?php echo $postResult; ?>
        </div>
    <?php endif; ?>

    <div class="posts">
        <h2>Posts</h2>

        <section>
            <ul id="feed-nur-als-test-wie-es-ausseht">
                <li><?php include 'post.php'; ?></li>
                <li><?php include 'post.php'; ?></li>
                <li><?php include 'post.php'; ?></li>
                <li><?php include 'post.php'; ?></li>
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
