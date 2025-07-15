<?php
    require_once __DIR__ . '/php/session_helper.php';
    require_once __DIR__ . '/php/NutzerVerwaltung.php';

// === Initialisierung ===
    requireLogin();

    $nutzerVerwaltung = new NutzerVerwaltung();
    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);

    if (!$currentUser) {
        header("Location: Login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Zwitscha - Suche</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/search.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

    <!-- === MAIN CONTENT === -->
    <div class="main-content">
        <!-- === SEARCH HEADER === -->
        <div class="search-header">
            <h2>Benutzer suchen</h2>
        </div>

        <!-- === MOBILE SEARCH SECTION === -->
        <div class="mobile-search-section">
            <!-- Search Input -->
            <div class="search-input-container">
                <input type="text" placeholder="Nach Nutzern suchen..." class="mobile-search-input" id="mobile-search-input" autocomplete="off">
            </div>
            
            <!-- === SEARCH RESULTS === -->
            <div class="search-results-container" id="search-results" style="display: none;">
                <h3>Nutzer gefunden</h3>
                <ul class="mobile-search-results-list"></ul>
            </div>
        </div>
    </div>

    <!-- === SEARCH FUNCTIONALITY === -->
    <script src="js/search.js"></script>
    <script>
        // === MOBILE-SUCHFUNKTIONALITÄT ===
        document.addEventListener('DOMContentLoaded', () => {
            initializeMobileSearch();
        });
    </script>

    <?php include 'footerMobile.php'; ?>

</body>
</html>