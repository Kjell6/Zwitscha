<?php
session_start();


// Abmelden: Session löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: Login.php");
    echo 'test';
    exit;
}

$eingeloggt = isset($_SESSION['eingeloggt']) && $_SESSION['eingeloggt'] === true;

$currentPage = basename($_SERVER['PHP_SELF']); // z.B. 'Profil.php'
?>

<header class="desktop-header">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <div class="header-section logo-section">
        <a href="index.php" class="logo">
            <picture>
                <source srcset="assets/zwitscha_dark.png" media="(prefers-color-scheme: dark)">
                <img src="assets/zwitscha.png" alt="Zwitscha Logo" class="logo-image">
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

        <?php if ($eingeloggt): ?>
            <?php if ($currentPage === 'Profil.php'): ?>
                <!-- Auf Profilseite: Abmelden -->
                <form method="post" style="display:inline;">
                    <button type="submit" name="logout">Abmelden</button>
                </form>
            <?php else: ?>
                <!-- Auf anderen Seiten: Link zum Profil -->
                <a href="Profil.php?userid=1" class="profile-link">
                    <i class="bi bi-person-fill"></i>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <!-- Nicht eingeloggt: Anmelden -->
            <a href="Login.php">
                <button type="button">Anmelden</button>
            </a>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('header-search-input');
            const resultsDropdown = document.querySelector('.header-search-results-dropdown');

            // Funktion zum Anzeigen der Suchergebnisse
            function displayResults(results) {
                resultsDropdown.innerHTML = '';

                if (results.length === 0) {
                    resultsDropdown.style.display = 'none';
                    return;
                }

                const heading = document.createElement('h3');
                heading.textContent = 'Nutzer gefunden';
                resultsDropdown.appendChild(heading);

                const resultsList = document.createElement('ul');
                resultsList.classList.add('header-search-results-list');

                results.forEach(user => {
                    const listItem = document.createElement('li');
                    listItem.classList.add('search-result-item');

                    const link = document.createElement('a');
                    link.href = user.profileUrl;

                    const img = document.createElement('img');
                    img.src = user.avatar;
                    img.alt = 'Profilbild';

                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = user.name;
                    nameSpan.classList.add('user-name');

                    link.appendChild(img);
                    link.appendChild(nameSpan);
                    listItem.appendChild(link);
                    resultsList.appendChild(listItem);
                });

                resultsDropdown.appendChild(resultsList);
                resultsDropdown.style.display = 'block';
            }

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim();
                
                if (query.length < 2) {
                    resultsDropdown.style.display = 'none';
                    return;
                }

                fetch('php/search_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'query=' + encodeURIComponent(query)
                })
                .then(response => response.json())
                .then(results => {
                    displayResults(results);
                })
                .catch(error => {
                    console.error('Fehler bei der Suche:', error);
                    resultsDropdown.style.display = 'none';
                });
            });

            // Suchergebnisse ausblenden bei Klick außerhalb
            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    resultsDropdown.style.display = 'none';
                }, 150);
            });

            // Suchergebnisse wieder anzeigen bei Focus (falls bereits Text vorhanden)
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 2) {
                    searchInput.dispatchEvent(new Event('input'));
                }
            });
        });
    </script>
</header>
