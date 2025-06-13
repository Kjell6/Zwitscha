<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zwitscha</title>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Globales Styling -->
  <link rel="stylesheet" href="css/global.css">

  <!-- Mobiler Footer -->
  <link rel="stylesheet" href="css/mobileFooter.css">
</head>




<!-- Mobiler Footer -->
<footer class="mobile-footer">
  <nav class="footer-nav">
    <a href="index.php" class="footer-link" aria-label="Startseite">
        <picture>
            <!-- Dark Mode -->
            <source
                    srcset="assets/custom_icons/home_dark.svg"
                    media="(prefers-color-scheme: dark)"
            >
            <!-- Light Mode (Fallback) -->
            <img
                    src="assets/custom_icons/home.svg"
                    alt="Zwitscha Logo"
                    class="logo-image"
                    width="28"
                    height="28"
            >
        </picture>
    </a>
    <a href="MobileSearch.php" class="footer-link" aria-label="Suche" id="mobile-search-button">
        <picture>
            <!-- Dark Mode -->
            <source
                    srcset="assets/custom_icons/search_dark.svg"
                    media="(prefers-color-scheme: dark)"
            >
            <!-- Light Mode (Fallback) -->
            <img
                    src="assets/custom_icons/search.svg"
                    alt="Zwitscha Logo"
                    class="logo-image"
                    width="28"
                    height="28"
            >
        </picture>
    </a>
    <?php 
    require_once __DIR__ . '/php/session_helper.php';
    $currentUserId = getCurrentUserIdWithFallback();
    ?>
    <a href="Profil.php?userid=<?php echo $currentUserId; ?>" class="footer-link" aria-label="Profil">
        <picture>
            <!-- Dark Mode -->
            <source
                    srcset="assets/custom_icons/profil_dark.svg"
                    media="(prefers-color-scheme: dark)"
            >
            <!-- Light Mode (Fallback) -->
            <img
                    src="assets/custom_icons/profil.svg"
                    alt="Zwitscha Logo"
                    class="logo-image"
                    width="24"
                    height="24"
            >
        </picture>
    </a>
  </nav>
</footer>


