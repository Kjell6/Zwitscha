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
        <input type="text" placeholder="Suche..." class="search-bar" id="header-search-input" autocomplete="off">
        <div class="header-search-results-dropdown"></div>
    </div>

    <!-- === PROFILE SECTION === -->
    <div class="header-section profile-section">
        <!-- Settings Link -->
        <a href="einstellungen.php" class="settings-link">
            <i class="bi bi-gear-fill"></i>
        </a>

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
    <script>
        // === DESKTOP-SUCHFUNKTIONALITÄT ===
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('header-search-input');
            const resultsDropdown = document.querySelector('.header-search-results-dropdown');
            
            // Prüfen ob Elemente existieren
            if (!searchInput || !resultsDropdown) {
                return;
            }

            // Suchergebnisse als HTML-Elemente darstellen
            function displayResults(results) {
                resultsDropdown.innerHTML = '';

                if (results.length === 0) {
                    resultsDropdown.style.display = 'none';
                    return;
                }

                // Überschrift hinzufügen
                const heading = document.createElement('h3');
                heading.textContent = 'Nutzer gefunden';
                resultsDropdown.appendChild(heading);

                // Ergebnisliste erstellen
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

            // Live-Suche bei Eingabe
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim();
                
                if (query.length < 2) {
                    resultsDropdown.style.display = 'none';
                    return;
                }

                // Suchanfrage an Server senden
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

            // Ergebnisse ausblenden bei Klick außerhalb
            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    resultsDropdown.style.display = 'none';
                }, 150);
            });

            // Ergebnisse wieder anzeigen bei Focus (falls bereits Text vorhanden)
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 2) {
                    searchInput.dispatchEvent(new Event('input'));
                }
            });
        });
    </script>
</header>
