<?php
// ==========================
// 1) POST-Request-Handling für Follow/Unfollow
// ==========================
$isFollowing = false;
$postResult = '';

// TODO: Später: Authentifizierung des aktuellen Benutzers hier (um dessen ID für Follow/Unfollow zu erhalten)
$currentUserId = 123; // Dummy-ID des aktuellen Benutzers

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $followerId    = isset($_POST['followerId'])   ? $_POST['followerId']   : '';
    $followeeId    = isset($_POST['followeeId'])   ? $_POST['followeeId']   : '';
    $currentStatus = isset($_POST['currentStatus']) ? $_POST['currentStatus'] : 'not_following';

    if ($followerId && $followeeId) {
        if ($currentStatus === 'following') {
            $isFollowing = false;
            $postResult = "Entfolgt!<br>Follower-ID: $followerId<br>Followee-ID: $followeeId";
        } else {
            $isFollowing = true;
            $postResult = "Gefolgt!<br>Follower-ID: $followerId<br>Followee-ID: $followeeId";
        }
        // TODO: Später: Speichern des Follow/Unfollow-Status in der Datenbank
        print_r($_POST); // Debugging-Ausgabe der POST-Daten
    } else {
        $postResult = "Fehler: Fehlende Daten.";
    }
}

// TODO: Später: Hier die Profildaten des Benutzers mit der ID $profileId aus der Datenbank laden
// (Ersetzt die Dummy-Daten in $allUsers)
// ==========================
// 2) URL-Parameter: Welches Profil soll geladen werden?
// ==========================
$profileId = isset($_GET['userid']) ? (int)$_GET['userid'] : 0;

// ==========================
// 3) Dummy-Daten: Alle Nutzer
// ==========================
$allUsers = [
    1 => [
        'id'             => 1,
        'username'       => 'Anna Beispiel',
        'joinDateLabel'  => 'Januar 2025',
        'followerCount'  => 120,
        'followingCount' => 45
    ],
    2 => [
        'id'             => 2,
        'username'       => 'Max Mustermann',
        'joinDateLabel'  => 'März 2025',
        'followerCount'  => 85,
        'followingCount' => 123
    ],
    3 => [
        'id'             => 3,
        'username'       => 'Lena Neumann',
        'joinDateLabel'  => 'Februar 2025',
        'followerCount'  => 200,
        'followingCount' => 75
    ]
];

// TODO: Später: Hier die Posts des Benutzers mit der ID $profileId aus der Datenbank laden
// (Ersetzt die Dummy-Daten in $allPosts und die Filterung)
// ==========================
// 4) Dummy-Daten: Alle Posts (mit userId zum Filtern)
// ==========================
$allPosts = [
    [
        'id'         => 1,
        'userId'     => 1,
        'autor'      => 'Anna Beispiel',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit'  => '2025-04-26T14:15:00Z',
        'time_label' => 'vor 1 Tag',
        'text'       => '👍 Dieses neue Feature ist wirklich großartig! Es macht die Bedienung so viel einfacher.',
        'bildPfad'   => '',
        'reactions'  => ['👍'=>2,'👎'=>0,'❤️'=>1,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments'   => 3
    ],
    [
        'id'         => 2,
        'userId'     => 2,
        'autor'      => 'Max Mustermann',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit'  => '2025-04-27T10:30:00Z',
        'time_label' => 'vor 2 Stunden',
        'text'       => 'Wie findet ihr dieses neue Logo von Zwitscha? Ich finde es super! Es ist modern und frisch. Was denkt ihr?',
        'bildPfad'   => 'assets/zwitscha_green.jpg',
        'reactions'  => ['👍'=>5,'👎'=>1,'❤️'=>3,'🤣'=>0,'❓'=>0,'‼️'=>2],
        'comments'   => 2
    ],
    [
        'id'         => 3,
        'userId'     => 3,
        'autor'      => 'Lena Neumann',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit'  => '2025-04-27T08:00:00Z',
        'time_label' => 'vor 4 Stunden',
        'text'       => 'Guten Morgen! 🌞 Heute starte ich mit frischem Kaffee und neuen Ideen in den Tag. Manchmal reicht ein bisschen Ruhe, um wieder kreative Energie zu tanken. Was motiviert euch am Morgen?',
        'bildPfad'   => '',
        'reactions'  => ['👍'=>8,'👎'=>0,'❤️'=>5,'🤣'=>1,'❓'=>0,'‼️'=>0],
        'comments'   => 4
    ],
    // Ein zusätzlicher Post, um zu zeigen, dass ein User mehrere Posts haben könnte
    [
        'id'         => 4,
        'userId'     => 2,
        'autor'      => 'Max Mustermann',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit'  => '2025-05-01T12:00:00Z',
        'time_label' => 'vor 6 Tagen',
        'text'       => 'Heute gibt’s einen neuen Artikel auf meinem Blog! Schaut doch mal rein und lasst Feedback da.',
        'bildPfad'   => '',
        'reactions'  => ['👍'=>7,'👎'=>0,'❤️'=>4,'🤣'=>2,'❓'=>0,'‼️'=>1],
        'comments'   => 1
    ]
];

// ==========================
// 5) Profil-Existenz prüfen
// ==========================
if (isset($allUsers[$profileId])) {
    $profile         = $allUsers[$profileId];
    $username        = $profile['username'];
    $joinDateLabel   = $profile['joinDateLabel'];
    $followerCount   = $profile['followerCount'];
    $followingCount  = $profile['followingCount'];
} else {
    $profile = null;
}

// ==========================
// 6) Ladezustand der Posts steuern (data, empty, error)
//    Kann per URL-Parameter gesetzt werden: ?state=empty oder ?state=error
// ==========================
$loadingState = isset($_GET['state']) ? $_GET['state'] : 'data';

// Wenn Profil existiert und Zustand = data, dann filtere die Posts …
$posts = [];
if ($profile && $loadingState === 'data') {
    foreach ($allPosts as $p) {
        if ($p['userId'] === $profileId) {
            $posts[] = $p;
        }
    }
}
// Wenn $posts leer ist, bleibt es leer (-> im Switch weiter unten wird dasselbe Layout wie 'empty' gezeigt).

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>
        <?php
        if ($profile) {
            echo 'Profil – ' . htmlspecialchars($username);
        } else {
            echo 'Profil nicht gefunden';
        }
        ?>
    </title>

    <!-- CSS-Dateien -->
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/profil.css" />
    <link rel="stylesheet" href="css/post.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>

<?php include 'headerDesktop.php'; ?>
<?php include 'footerMobile.php'; ?>

<main class="container">

    <?php if (!$profile): ?>
        <!-- ===== Fall: Profil nicht gefunden ===== -->
        <div class="empty-state" style="text-align: center; margin: 3em 0;">
            <i class="bi bi-person-x" style="font-size: 48px; margin-bottom: 20px;"></i>
            <h3>Profil nicht gefunden</h3>
            <p>Der angeforderte Nutzer existiert nicht oder wurde gelöscht.</p>
            <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
        </div>

    <?php else: ?>
        <!-- ============================
              7) Profil-Kopfbereich (dynamisch)
             ============================ -->
        <div class="profil-header">
            <img src="assets/placeholder-profilbild.jpg" alt="Profilbild" class="profilbild" />

            <div class="profil-header pb-name-untereinander">
                <div class="profil-main-infos">
                    <!-- Dynamischer Nutzername -->
                    <h1 class="profil-name"><?php echo htmlspecialchars($username); ?></h1>

                    <!-- Follow/Unfollow-Form (Dummy-Daten) -->
                    <form id="follow-form" method="post" action="" style="display: inline;">
                        <input type="hidden" name="followerId" value="123" />
                        <input type="hidden" name="followeeId" value="<?php echo $profileId; ?>" />
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

                <!-- Dynamisches Beitrittsdatum -->
                <p class="beitritts-datum"><i class="bi bi-calendar2-fill"></i> Beigetreten <?php echo htmlspecialchars($joinDateLabel); ?></p>

                <!-- Dynamische Zähler: Follower, Folge ich, Posts -->
                <div class="folgen-container">
                    <p class="folge-info"><strong><?php echo $followerCount; ?></strong> <span>Follower</span></p>
                    <p class="folge-info"><strong><?php echo $followingCount; ?></strong> <span>Folge ich</span></p>
                    <p class="folge-info"><strong><?php echo count($posts); ?></strong> <span>Posts</span></p>
                </div>
            </div>

            <a href="einstellungen.php" class="einstellungen-link mobile-only">
                <i class="bi bi-gear-fill"></i>
            </a>
        </div>

        <!-- Anzeige einer Erfolg-/Fehlermeldung nach POST -->
        <?php if ($postResult): ?>
            <div class="post-result" style="margin: 1em 0; padding: 1em; border: 1px solid #ccc; background: #f9f9f9;">
                <?php echo $postResult; ?>
            </div>
        <?php endif; ?>


        <!-- ============================
              8) Feed-Sektion: Posts dieses Nutzers
             ============================ -->
        <section class="feed">
            <h2>Posts von <?php echo htmlspecialchars($username); ?></h2>

            <?php
            switch ($loadingState) {
                case 'empty':
                    // Explizit leerer Zustand via ?state=empty
                    ?>
                    <div class="empty-state">
                        <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>Noch keine Posts vorhanden</h3>
                        <p>Der Nutzer hat noch keine Posts erstellt.</p>
                    </div>
                    <?php
                    break;

                case 'error':
                    // Explizit Fehlerzustand via ?state=error
                    ?>
                    <div class="error-state">
                        <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>Fehler beim Laden der Posts</h3>
                        <p>Die Beiträge konnten nicht geladen werden. Bitte versuche es später erneut.</p>
                        <button onclick="window.location.reload()" class="btn btn-primary">Neu laden</button>
                    </div>
                    <?php
                    break;

                case 'data':
                default:
                    // Datenzustand: Wenn $posts leer, zeige ebenfalls den leeren Zustand
                    if (empty($posts)) {
                        ?>
                        <div class="empty-state">
                            <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                            <h3>Noch keine Posts vorhanden</h3>
                            <p>Der Nutzer hat noch keine Posts erstellt.</p>
                        </div>
                        <?php
                    } else {
                        // Jeden Post rendern – hier wird 'post.php' eingebunden. Achte darauf, dass post.php
                        // auf die Variable $post zugreift und sie korrekt rendert.
                        foreach ($posts as $post) {
                            include 'post.php';
                        }
                    }
                    break;
            }
            ?>
        </section>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2025 Zwitscha</p>
</footer>

</body>
</html>
