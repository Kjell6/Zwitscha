<!-- Styles speziell für den mobilen Footer -->
<link rel="stylesheet" href="css/mobileFooter.css" />

<!-- Mobiler Footer -->
<footer class="mobile-footer" role="navigation">
  <nav class="footer-nav">
    <a href="index.php" class="footer-link" aria-label="Startseite" data-active-on="index.php">
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
    <a href="MobileSearch.php" class="footer-link" aria-label="Suche" id="mobile-search-button" data-active-on="MobileSearch.php">
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
    $currentUserId = getCurrentUserId();
    ?>
    <a href="Profil.php?userid=<?php echo $currentUserId; ?>" class="footer-link" aria-label="Profil" data-active-on="Profil.php">
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

<script>
// Aktiven Footer-Link markieren und Safe-Area berücksichtigen
document.addEventListener('DOMContentLoaded', function() {
  const links = document.querySelectorAll('.mobile-footer .footer-link');
  links.forEach(link => {
    const activeOn = link.getAttribute('data-active-on');
    if (activeOn && window.location.pathname.endsWith(activeOn)) {
      link.classList.add('active');
    }
  });
});
</script>


