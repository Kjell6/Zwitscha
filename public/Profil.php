<?php
try {
    require_once __DIR__ . '/php/PostVerwaltung.php';
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    require_once __DIR__ . '/php/session_helper.php';

    // === Initialisierung ===
    $postVerwaltung = new PostVerwaltung();
    $nutzerVerwaltung = new NutzerVerwaltung();

    // Prüfen ob angemeldet
    requireLogin();

    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);

    // Welches Profil soll geladen werden?
    $profileId = isset($_GET['userid']) ? (int)$_GET['userid'] : 0;
    if ($profileId === 0) {
        // Wenn keine ID übergeben wurde, standardmäßig zum eigenen Profil leiten
        header("Location: Profil.php?userid=" . $currentUserId);
        exit();
    }

// === POST-Request-Handling für Follow/Unfollow ===
$postResult = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'toggle_follow') {
    $followeeId = (int)($_POST['followeeId'] ?? 0);
    if ($followeeId > 0 && $followeeId !== $currentUserId) {
        $nutzerVerwaltung->toggleFollow($currentUserId, $followeeId);
        // Redirect, um Form-Neusendung zu verhindern
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// === POST-Request-Handling für Admin-Status-Änderung ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'toggle_admin') {
    // Nur Admins dürfen andere zu Admins machen
    if ($currentUser && $currentUser['istAdministrator']) {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        if ($targetUserId > 0 && $targetUserId !== $currentUserId) {
            // Aktuelle Daten des Ziel-Benutzers holen
            $targetUser = $nutzerVerwaltung->getUserById($targetUserId);
            if ($targetUser) {
                // Admin-Status umschalten
                $newAdminStatus = !$targetUser['istAdministrator'];
                $nutzerVerwaltung->setAdminStatus($targetUserId, $newAdminStatus);
                // Redirect, um Form-Neusendung zu verhindern
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
    }
}

// === Daten aus der Datenbank laden ===
$profile = $nutzerVerwaltung->getUserProfileData($profileId);
$profileUser = $nutzerVerwaltung->getUserById($profileId); // Für Admin-Status
$posts = [];
$isFollowing = false;
$limit = 15;

if ($profile) {
    // Lade nur die erste Seite der Posts
    $posts = $postVerwaltung->getPostsByUserId($profileId, $currentUserId, $limit, 0);
    $isFollowing = $nutzerVerwaltung->isFollowing($currentUserId, $profileId);

    // Konvertiere das Registrierungsdatum in ein lesbares Format
    $date = new DateTime($profile['registrierungsDatum']);
    $months = [
        1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
        5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
    ];
    $month = $months[(int)$date->format('n')];
    $year = $date->format('Y');
    $joinDateLabel = $month . ' ' . $year;
}

} catch (Exception $e) {
    // Fehlerbehandlung: Zeige eine Fehlermeldung statt HTTP 500
    echo '<h1>Fehler beim Laden der Profilseite</h1>';
    echo '<p>Es ist ein Fehler aufgetreten: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Datei: ' . htmlspecialchars($e->getFile()) . ' (Zeile ' . $e->getLine() . ')</p>';
    echo '<a href="index.php">Zurück zur Startseite</a>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <title>
        <?php
        if ($profile) {
            echo 'Profil von ' . htmlspecialchars($profile['nutzerName']);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            <img src="getImage.php?type=user&id=<?php echo $profile['id']; ?>" loading="lazy" 
                 alt="Profilbild" 
                 class="profilbild" 
                 onclick="openLightbox('getImage.php?type=user&id=<?php echo $profile['id']; ?>')"
                 style="cursor: pointer;" />

            <div class="profil-header pb-name-untereinander">
                <div class="profil-main-infos">
                    <!-- Dynamischer Nutzername -->
                    <h1 class="profil-name">
                        <?php echo htmlspecialchars($profile['nutzerName']); ?>
                        <?php if ($profileUser && $profileUser['istAdministrator']): ?>
                            <span class="admin-badge" title="Administrator">
                                <i class="bi bi-shield-fill"></i>
                            </span>
                        <?php endif; ?>
                    </h1>

                    <?php if ($currentUserId !== $profile['id']): ?>
                        <!-- Follow/Unfollow-Form -->
                        <form method="post" action="" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_follow" />
                            <input type="hidden" name="followeeId" value="<?php echo $profile['id']; ?>" />
                            <button type="submit" class="folgen-button <?php echo $isFollowing ? 'gefolgt' : ''; ?>">
                                <?php if ($isFollowing): ?>
                                    <span class="follow-text">Folge ich</span>
                                <?php else: ?>
                                    Folgen
                                <?php endif; ?>
                            </button>
                        </form>

                        <!-- Admin-Status-Button (nur für Admins sichtbar) -->
                        <?php if ($currentUser && $currentUser['istAdministrator'] && $profileUser): ?>
                            <form method="post" action="" style="display: inline; margin-left: 10px;">
                                <input type="hidden" name="action" value="toggle_admin" />
                                <input type="hidden" name="target_user_id" value="<?php echo $profile['id']; ?>" />
                                <button type="submit" class="admin-button <?php echo $profileUser['istAdministrator'] ? 'admin-active' : ''; ?>" 
                                        onclick="return confirm('<?php echo $profileUser['istAdministrator'] ? 'Admin-Rechte entziehen?' : 'Zu Admin machen?'; ?>')">
                                    <?php if ($profileUser['istAdministrator']): ?>
                                        Admin entziehen
                                    <?php else: ?>
                                        Zu Admin machen
                                    <?php endif; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Dynamisches Beitrittsdatum -->
                <p class="beitritts-datum"><i class="bi bi-calendar2-fill"></i> Beigetreten <?php echo htmlspecialchars($joinDateLabel); ?></p>

                <!-- Dynamische Zähler: Follower, Folge ich, Posts -->
                <div class="folgen-container">
                    <a href="followerList.php?userid=<?php echo $profile['id']; ?>&type=followers" class="folge-info-link">
                        <p class="folge-info"><strong><?php echo $profile['followerCount']; ?></strong> <span>Follower</span></p>
                    </a>
                    <a href="followerList.php?userid=<?php echo $profile['id']; ?>&type=following" class="folge-info-link">
                        <p class="folge-info"><strong><?php echo $profile['followingCount']; ?></strong> <span>Folge ich</span></p>
                    </a>
                    <p class="folge-info"><strong><?php echo $profile['postCount']; ?></strong> <span>Posts</span></p>
                </div>
            </div>
            
            <?php if ($currentUserId === $profile['id']): ?>
                <a href="einstellungen.php" class="einstellungen-link mobile-only">
                    <i class="bi bi-gear-fill"></i>
                </a>
                <a href="logout.php" class="logout-link mobile-only" onclick="return confirm('Möchtest du dich wirklich abmelden?')">
                    <img src="assets/custom_icons/LogOut.svg" alt="Logout Icon" class="custom-logout-icon">
                </a>
            <?php endif; ?>
        </div>

        <!-- ============================
              8) Feed-Sektion: Posts dieses Nutzers
             ============================ -->
        <section class="feed" id="posts-container">
            <div class="feed-title-container">
                <span class="feed-title">Posts</span>
            </div>

            <?php
            if (empty($posts)) {
                ?>
                <div class="empty-state">
                    <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Noch keine Posts</h3>
                    <p>Dieser Nutzer hat noch keine Posts verfasst.</p>
                </div>
                <?php
            } else {
                foreach ($posts as $post) {
                    include 'post.php';
                }
            }
            ?>
        </section>

        <!-- "Mehr laden"-Button, nur anzeigen, wenn die initiale Post-Anzahl dem Limit entspricht (deutet auf mehr Posts hin) -->
        <?php if (count($posts) === $limit): ?>
            <div id="mehr-laden-container" style="display: flex; justify-content: center; margin: 20px 0;">
                <button id="mehr-laden-button" class="btn">Mehr laden</button>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2025 Zwitscha</p>
</footer>

<?php include 'lightbox.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("posts-container");
    const buttonContainer = document.getElementById('mehr-laden-container');
    
    // Prüfen, ob der Button überhaupt auf der Seite existiert
    if (!buttonContainer) return;

    const button = document.getElementById("mehr-laden-button");
    let offset = <?php echo $limit; ?>;
    const limit = <?php echo $limit; ?>;
    const profileId = <?php echo $profileId; ?>;

    button.addEventListener("click", () => {
        // Lade-Indikator anzeigen
        button.disabled = true;
        button.textContent = 'Lädt...';

        // Daten von der neuen Lade-Schnittstelle abrufen
        fetch(`php/get-posts.php?context=user&userId=${profileId}&offset=${offset}&limit=${limit}`)
            .then(res => {
                if (!res.ok) throw new Error('Fehler beim Laden der Posts');
                return res.text(); // HTML-String
            })
            .then(html => {
                if (!html.trim()) {
                    // Keine Posts mehr, Button ausblenden
                    if(buttonContainer) buttonContainer.style.display = 'none';
                } else {
                    // HTML an den Container anhängen
                    container.insertAdjacentHTML('beforeend', html);
                    offset += limit; // Offset für die nächste Anfrage erhöhen
                }
            })
            .catch(err => {
                console.error(err);
                button.textContent = 'Fehler!'; // Feedback im Fehlerfall
            })
            .finally(() => {
                // Button-Zustand zurücksetzen
                if(buttonContainer && buttonContainer.style.display !== 'none') {
                    button.disabled = false;
                    button.textContent = 'Mehr laden';
                }
            });
    });
});
</script>

</body>
</html>
