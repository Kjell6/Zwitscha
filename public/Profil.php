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
                    if ($nutzerVerwaltung->setAdminStatus($targetUserId, $newAdminStatus)) {
                        // Redirect, um Form-Neusendung zu verhindern
                        header("Location: " . $_SERVER['REQUEST_URI']);
                        exit();
                    }
                }
            }
        }
    }

// === POST-Request-Handling für Nutzer-Bann ===
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'ban_user') {
        if ($currentUser && $currentUser['istAdministrator']) {
            $targetUserId = (int)($_POST['target_user_id'] ?? 0);
            $userToBan = $nutzerVerwaltung->getUserById($targetUserId);

            // Sicherheitsprüfungen: Man kann sich nicht selbst bannen, der Haupt-Admin kann nicht gebannt werden.
            if ($targetUserId > 0 && $targetUserId !== $currentUserId && $userToBan && $userToBan['nutzerName'] !== 'admin') {
                if ($nutzerVerwaltung->deleteUser($targetUserId)) {
                    // Erfolgreich gebannt, Weiterleitung zur Startseite.
                    header("Location: index.php?banned=true");
                    exit();
                }
            }
        }
    }


// === Daten aus der Datenbank laden ===
    $profile = $nutzerVerwaltung->getUserProfileData($profileId);
    $profileUser = $nutzerVerwaltung->getUserById($profileId); // Für Admin-Status
    $posts = [];
    $comments = [];
    $isFollowing = false;
    $limit = 15;

// Check which content to show: posts or comments
    $showComments = isset($_GET['view']) && $_GET['view'] === 'comments';

    if ($profile) {
        if ($showComments) {
            // Lade nur Kommentare
            $comments = $postVerwaltung->getCommentsByUserId($profileId, $limit, 0);
        } else {
            // Lade nur Posts (default)
            $posts = $postVerwaltung->getPostsByUserId($profileId, $currentUserId, $limit, 0);
        }
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
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
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
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/header.css"/>
    <link rel="stylesheet" href="css/profil.css"/>
    <link rel="stylesheet" href="css/post.css"/>
    <link rel="stylesheet" href="css/kommentarEinzeln.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

<!-- === HEADER === -->
<?php include 'headerDesktop.php'; ?>
<?php include 'footerMobile.php'; ?>

<!-- === MAIN CONTAINER === -->
<main class="container">

    <?php if (!$profile): ?>
        <!-- === PROFILE NOT FOUND === -->
        <div class="empty-state" style="text-align: center; margin: 3em 0;">
            <i class="bi bi-person-x" style="font-size: 48px; margin-bottom: 20px;"></i>
            <h3>Profil nicht gefunden</h3>
            <p>Der angeforderte Nutzer existiert nicht oder wurde gelöscht.</p>
            <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
        </div>

    <?php else: ?>
        <!-- === PROFILE HEADER === -->
        <div class="profil-header">
            <!-- Profile Image -->
            <img src="getImage.php?type=user&id=<?php echo $profile['id']; ?>" loading="lazy"
                 alt="Profilbild"
                 class="profilbild"
                 onclick="openLightbox('getImage.php?type=user&id=<?php echo $profile['id']; ?>')"
                 style="cursor: pointer;"/>

            <!-- Profile Info -->
            <div class="profil-header pb-name-untereinander">
                <!-- === PROFILE MAIN INFO === -->
                <div class="profil-main-infos">
                    <!-- User Name with Admin Badge -->
                    <h1 class="profil-name">
                        <?php echo htmlspecialchars($profile['nutzerName']); ?>
                        <?php if ($profileUser && $profileUser['istAdministrator']): ?>
                            <span class="admin-badge" title="Administrator">
                                <i class="bi bi-shield-fill"></i>
                            </span>
                        <?php endif; ?>
                    </h1>

                    <!-- === PROFILE BUTTONS === -->
                    <?php if ($currentUserId !== $profile['id']): ?>
                        <!-- Follow/Unfollow Button -->
                        <form method="post" action="" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_follow"/>
                            <input type="hidden" name="followeeId" value="<?php echo $profile['id']; ?>"/>
                            <button type="submit" class="folgen-button <?php echo $isFollowing ? 'gefolgt' : ''; ?>">
                                <?php if ($isFollowing): ?>
                                    <span class="follow-text">Folge ich</span>
                                <?php else: ?>
                                    Folgen
                                <?php endif; ?>
                            </button>
                        </form>

                        <!-- Admin Controls (Only for Admins) -->
                        <?php if ($currentUser && $currentUser['istAdministrator'] && $profileUser && $profileUser['nutzerName'] !== 'admin'): ?>
                            <!-- Admin Status Toggle -->
                            <form method="post" action="" style="display: inline; margin-left: 10px;">
                                <input type="hidden" name="action" value="toggle_admin"/>
                                <input type="hidden" name="target_user_id" value="<?php echo $profile['id']; ?>"/>
                                <button type="submit"
                                        class="admin-button <?php echo $profileUser['istAdministrator'] ? 'admin-active' : ''; ?>"
                                        onclick="return confirm('<?php echo $profileUser['istAdministrator'] ? 'Admin-Rechte entziehen?' : 'Zu Admin machen?'; ?>')">
                                    <?php if ($profileUser['istAdministrator']): ?>
                                        Admin entziehen
                                    <?php else: ?>
                                        Zu Admin machen
                                    <?php endif; ?>
                                </button>
                            </form>

                            <!-- Ban User Button -->
                            <form method="post" action="" style="display: inline; margin-left: 10px;"
                                  onsubmit="return confirmBan(this);">
                                <input type="hidden" name="action" value="ban_user"/>
                                <input type="hidden" name="target_user_id" value="<?php echo $profile['id']; ?>"/>
                                <button type="submit" class="ban-button">Nutzer bannen</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- === PROFILE METADATA === -->
                <!-- Join Date -->
                <p class="beitritts-datum"><i class="bi bi-calendar2-fill"></i>
                    Beigetreten <?php echo htmlspecialchars($joinDateLabel); ?></p>

                <!-- === PROFILE STATS === -->
                <div class="folgen-container">
                    <a href="followerList.php?userid=<?php echo $profile['id']; ?>&type=followers"
                       class="folge-info-link">
                        <p class="folge-info"><strong><?php echo $profile['followerCount']; ?></strong>
                            <span>Follower</span></p>
                    </a>
                    <a href="followerList.php?userid=<?php echo $profile['id']; ?>&type=following"
                       class="folge-info-link">
                        <p class="folge-info"><strong><?php echo $profile['followingCount']; ?></strong>
                            <span>Folge ich</span></p>
                    </a>
                    <p class="folge-info"><strong><?php echo $profile['postCount']; ?></strong> <span>Posts</span></p>
                    <p class="folge-info">
                        <strong><?php echo $profile['commentCount']; ?></strong><span>Kommentare</span></p>
                </div>
            </div>

            <!-- === MOBILE CONTROLS === -->
            <?php if ($currentUserId === $profile['id']): ?>
                <a href="einstellungen.php" class="einstellungen-link mobile-only">
                    <i class="bi bi-gear-fill"></i>
                </a>
                <a href="logout.php" class="logout-link mobile-only"
                   onclick="return confirm('Möchtest du dich wirklich abmelden?')">
                    <img src="assets/custom_icons/LogOut.svg" alt="Logout Icon" class="custom-logout-icon">
                </a>
            <?php endif; ?>
        </div>

        <!-- === CONTENT NAVIGATION === -->
        <section class="profile-content">
            <!-- Content Toggle -->
            <div class="switch-wrapper">
                <div class="profile-toggle">
                    <input type="radio" id="posts-toggle"
                           name="profile-filter" <?php echo !$showComments ? 'checked' : ''; ?>
                           onchange="window.location.href='Profil.php?userid=<?php echo $profileId; ?>'">
                    <label for="posts-toggle">Posts</label>
                    <input type="radio" id="comments-toggle"
                           name="profile-filter" <?php echo $showComments ? 'checked' : ''; ?>
                           onchange="window.location.href='Profil.php?userid=<?php echo $profileId; ?>&view=comments'">
                    <label for="comments-toggle">Kommentare</label>
                    <span class="switch-indicator"></span>
                </div>
            </div>

            <!-- === CONTENT AREA === -->
            <div class="feed" id="content-container">
                <?php if ($showComments): ?>
                    <!-- Comments View -->
                    <?php
                    if (empty($comments)) {
                        ?>
                        <div class="empty-state">
                            <i class="bi bi-chat-left-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                            <h3>Noch keine Kommentare</h3>
                            <p>Dieser Nutzer hat noch keine Kommentare verfasst.</p>
                        </div>
                        <?php
                    } else {
                        foreach ($comments as $comment) {
                            include 'kommentarEinzeln.php';
                        }
                    }
                    ?>
                    <!-- Posts Section -->
                <?php else: ?>
                    <!-- Posts View -->
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
                <?php endif; ?>
            </div>

            <!-- "Mehr laden"-Button -->
            <?php
            $currentCount = $showComments ? count($comments) : count($posts);
            if ($currentCount === $limit):
                ?>
                <div id="mehr-laden-container" style="display: flex; justify-content: center; margin: 20px 0;">
                    <button id="mehr-laden-button" class="btn">Mehr laden</button>
                </div>
            <?php endif; ?>
        </section>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2025 Zwitscha</p>
</footer>

<?php include 'lightbox.php'; ?>

<!-- Refactored JavaScript-Funktionalität -->
<script src="js/comment-utils.js"></script>
<script src="js/pagination.js"></script>

<!-- AJAX-Funktionalität -->
<script src="js/ajax/utils.js"></script>
<script src="js/ajax/reactions.js"></script>
<script src="js/ajax/posts.js"></script>
<script src="js/ajax/comments.js"></script>

<script>
    // === ADMIN-FUNKTIONEN ===
    function confirmBan(form) {
        const userToBan = "<?php echo htmlspecialchars($profile['nutzerName'], ENT_QUOTES); ?>";
        const message = `Um den Nutzer "${userToBan}" wirklich endgültig zu löschen, gib bitte den Nutzernamen zur Bestätigung ein.`;

        const enteredName = prompt(message);

        if (enteredName === userToBan) {
            return true; // Formular wird gesendet
        } else if (enteredName !== null) { // Wenn der User nicht auf "Abbrechen" klickt
            alert("Die Eingabe war nicht korrekt. Der Vorgang wurde abgebrochen.");
            return false;
        }
        return false; // Formular wird nicht gesendet
    }

    // === SEITENINITIALISIERUNG ===
    document.addEventListener("DOMContentLoaded", () => {
        const moreButton = document.getElementById('mehr-laden-button');

        // "Mehr laden"-Button für Posts und Kommentare
        if (moreButton) {
            const profileId = <?php echo $profileId; ?>;
            const limit = <?php echo $limit; ?>;
            const isCommentsView = <?php echo $showComments ? 'true' : 'false'; ?>;
            const context = isCommentsView ? 'user_comments' : 'user';
            const params = { userId: profileId };
            
            initializePagination({
                containerId: 'content-container',
                buttonId: 'mehr-laden-button',
                buttonContainerId: 'mehr-laden-container',
                context: context,
                limit: limit,
                initialOffset: limit,
                params: params
            });
        }

        // Initial setup für bereits geladene Kommentare
        initializeCommentSystem();
    });
</script>

</body>
</html>
