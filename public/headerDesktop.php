<?php
    $currentPage = basename($_SERVER['PHP_SELF']); // gibt z.B. 'profil.php' zurück
?>

<header class="desktop-header">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <div class="header-section logo-section">
        <a href="index.php" class="logo">
            <picture>
                <!-- Dark Mode -->
                <source
                        srcset="assets/zwitscha_dark.png"
                        media="(prefers-color-scheme: dark)"
                >
                <!-- Light Mode (Fallback) -->
                <img
                        src="assets/zwitscha.png"
                        alt="Zwitscha Logo"
                        class="logo-image"
                >
            </picture>
        </a>
    </div>

    <div class="header-section search-section">
        <input type="text" placeholder="Suche..." class="search-bar" id="header-search-input" autocomplete="off">
        <div class="header-search-results-dropdown"></div>
    </div>

    <div class="header-section profile-section">

        <a href="einstellungen.php" class="settings-link">
            <i class="bi bi-gear-fill"></i>
        </a>

        <?php if ($currentPage === 'Profil.php'): ?>
            <!-- Abmelden Button -->
            <a href="Login.php">
                <button type="button">Abmelden</button>
            </a>
        <?php else: ?>
            <a href="Profil.php" class="profile-link">
                <i class="bi bi-person-fill"></i>
            </a>
        <?php endif; ?>



    </div>

    <!-- JavaScript Code für Live ergebnisse -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('header-search-input');

            searchInput.addEventListener('input', () => {
                const query = searchInput.value;

                fetch('suchanfrage.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'query=' + encodeURIComponent(query)
                })
                    .then(response => response.text())
                    .then(data => {
                        // Ergebnisse anzeigen
                        document.querySelector('.header-search-results-dropdown').innerHTML = data;
                    })
                    .catch(error => console.error('Fehler bei der Suche:', error));
            });
        });
    </script>

</header>
