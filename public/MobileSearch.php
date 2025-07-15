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
    <script>
        // === MOBILE-SUCHFUNKTIONALITÄT ===
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('mobile-search-input');
            const resultsContainer = document.getElementById('search-results');
            const resultsList = document.querySelector('.mobile-search-results-list');

            // Suchergebnisse als HTML-Elemente darstellen
            function displayResults(results) {
                resultsList.innerHTML = '';

                if (results.length === 0) {
                    resultsContainer.style.display = 'none';
                    return;
                }

                // Ergebnisse durchlaufen und HTML-Elemente erstellen
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

                resultsContainer.style.display = 'block';
            }

            // Live-Suche bei Eingabe
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim();
                
                if (query.length < 2) {
                    resultsContainer.style.display = 'none';
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
                    resultsContainer.style.display = 'none';
                });
            });

            // Auto-Focus auf das Suchfeld
            searchInput.focus();
        });
    </script>

    <?php include 'footerMobile.php'; ?>

</body>
</html>