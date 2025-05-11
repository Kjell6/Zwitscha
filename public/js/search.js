// Verpacke den gesamten Initialisierungscode in eine Funktion
function initHeaderSearch() {
    // Hole Referenzen zu den benötigten HTML-Elementen *jetzt*, da der Header im DOM ist
    const searchInput = document.getElementById('header-search-input');
    const resultsDropdown = document.querySelector('.header-search-results-dropdown');

    // Simuliere Benutzerdaten für die Suche
    const users = [
        { name: 'Beispiel Benutzer 1', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
        { name: 'Ein Anderer Benutzer', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
        { name: 'Test Nutzer Drei', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
        { name: 'Max Mustermann', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
        { name: 'Anna Beispiel', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
        { name: 'Developer User', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
        { name: 'Frontend Guru', profileUrl: 'Profil.html', avatar: 'assets/placeholder-profilbild.jpg' },
    ];

    // Funktion zum Anzeigen der Suchergebnisse
    function displayResults(results) {
        resultsDropdown.innerHTML = '';

        if (results.length === 0) {
            resultsDropdown.style.display = 'none';
            return;
        }

        const heading = document.createElement('h3');
        heading.textContent = 'Suchergebnisse';
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

            const span = document.createElement('span');
            span.textContent = user.name;

            link.appendChild(img);
            link.appendChild(span);
            listItem.appendChild(link);
            resultsList.appendChild(listItem);
        });

        resultsDropdown.appendChild(resultsList);
        resultsDropdown.style.display = 'block';
    }

    // Funktion zum verzögerten Ausblenden
    function hideResultsDelayed() {
        setTimeout(() => {
            resultsDropdown.style.display = 'none';
        }, 100);
    }

    // automatische Ausblenden bei blur wieder zu entfernen
    function disableAutoHideOnBlur() {
        if (searchInput) {
            searchInput.removeEventListener('blur', hideResultsDelayed);
            console.log('Auto-hide on blur disabled (MobileSearch.html)');
        }
    }

    // Füge Event-Listener hinzu, aber erst NACHDEM die Elemente gefunden wurden
    if (searchInput && resultsDropdown) {
        searchInput.addEventListener('input', (event) => {
            const searchTerm = event.target.value.toLowerCase();
            if (searchTerm.length === 0) {
                resultsDropdown.style.display = 'none';
                return;
            }
            const filteredUsers = users.filter(user =>
                user.name.toLowerCase().includes(searchTerm)
            );
            displayResults(filteredUsers);
        });

        searchInput.addEventListener('focus', () => {
            const searchTerm = searchInput.value.toLowerCase();
            if (searchTerm.length > 0) {
                const filteredUsers = users.filter(user =>
                    user.name.toLowerCase().includes(searchTerm)
                );
                displayResults(filteredUsers);
            }
        });

        // Standardmäßig: hide on blur
        searchInput.addEventListener('blur', hideResultsDelayed);
    } else {
        console.error("Header search elements not found after initHeaderSearch was called.");
    }

    // KEIN document.addEventListener('DOMContentLoaded', ...) mehr hier!

    // Wenn auf MobileSearch.html sind, deaktiviert automatische Schließen
    if (window.location.pathname.endsWith('MobileSearch.html')) {
        disableAutoHideOnBlur();
    }
}
