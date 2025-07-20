<?php
    require_once __DIR__ . '/php/session_helper.php';

// === POST-Request-Handling für Logout ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
        logout();
    }

// === Session-Daten abrufen ===
    $eingeloggt = isLoggedIn();
    $currentUserId = getCurrentUserId();
    $currentUsername = getCurrentUsername();

    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- === DESKTOP HEADER === -->
<header class="desktop-header">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- === LOGO SECTION === -->
    <div class="header-section logo-section">
        <a href="index.php" class="logo">
            <picture>
                <source srcset="assets/zwitscha_dark.png" media="(prefers-color-scheme: dark)">
                <img src="assets/zwitscha.png" alt="Zwitscha Logo" class="logo-image">
            </picture>
        </a>
    </div>

    <!-- === SEARCH SECTION === -->
    <div class="header-section search-section">
        <input type="text" placeholder="Nach Nutzern suchen..." class="search-bar" id="header-search-input" autocomplete="off">
        <div class="header-search-results-dropdown" id="header-search-results"></div>
    </div>

    <!-- === PROFILE SECTION === -->
    <div class="header-section profile-section">
        <!-- Settings Link -->
        <a href="einstellungen.php" class="settings-link">
            <i class="bi bi-gear-fill"></i>
        </a>
        <?php if ($eingeloggt && $currentUserId === 3): ?>
            <a href="dashboard.php" class="dashboard-link" title="Dashboard">
                <i class="bi bi-speedometer2"></i>
            </a>
        <?php endif; ?>

        <!-- User Authentication State -->
        <?php if ($eingeloggt): ?>
            <?php
            $isOwnProfile = ($currentPage === 'Profil.php' && isset($_GET['userid']) && (int)$_GET['userid'] === $currentUserId);
            ?>
            <?php if ($isOwnProfile): ?>
                <!-- Logout Button (On Own Profile) -->
                <form method="post" style="display:inline;">
                    <button type="submit" name="logout" class="logout-button">Abmelden</button>
                </form>
            <?php else: ?>
                <!-- Profile Link (Other Pages) -->
                <a href="Profil.php?userid=<?php echo $currentUserId; ?>" class="profile-link" title="<?php echo htmlspecialchars($currentUsername); ?>">
                    <i class="bi bi-person-fill"></i>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <!-- Login Button (Not Logged In) -->
            <a href="Login.php">
                <button type="button">Anmelden</button>
            </a>
        <?php endif; ?>
    </div>

    <!-- === SEARCH FUNCTIONALITY === -->
    <script src="js/search.js"></script>
    <script>
        // === DESKTOP-SUCHFUNKTIONALITÄT ===
        document.addEventListener('DOMContentLoaded', () => {
            initializeDesktopSearch();
        });
    </script>
</header>
