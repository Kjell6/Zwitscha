<?php
    require_once __DIR__ . '/php/session_helper.php';
    $eingeloggt = isLoggedIn();
    $currentUsername = getCurrentUsername();
?>
<link rel="stylesheet" href="css/mobileHeader.css" />

<header class="mobile-header" role="banner">
    <div class="mobile-header__bar">
        <a href="index.php" class="mobile-header__brand" aria-label="Startseite">
            <img src="assets/favicon.png" alt="Zwitscha" width="20" height="20" />
            <span class="mobile-header__title">Zwitscha</span>
        </a>
        <div class="mobile-header__actions">
            <a href="einstellungen.php" class="icon-button" aria-label="Einstellungen">
                <i class="bi bi-gear-fill"></i>
            </a>
            <?php if (!$eingeloggt): ?>
                <a href="Login.php" class="cta-login">Anmelden</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
// Fügt beim Scrollen einen Schatten hinzu, wenn nicht ganz oben
document.addEventListener('scroll', function() {
  const header = document.querySelector('.mobile-header');
  if (!header) return;
  if (window.scrollY > 1) {
    header.classList.add('is-stuck');
  } else {
    header.classList.remove('is-stuck');
  }
}, { passive: true });
</script>


