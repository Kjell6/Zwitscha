/**
 * Gemeinsame Suchfunktionalität für Desktop und Mobile
 * 
 * @param {Object} config - Konfigurationsobjekt
 * @param {string} config.searchInputId - ID des Such-Input-Elements
 * @param {string} config.resultsContainerId - ID des Ergebnis-Containers
 * @param {string} config.resultsListSelector - Selektor für die Ergebnisliste
 * @param {boolean} config.createHeading - Ob eine Überschrift erstellt werden soll
 * @param {boolean} config.enableBlurHide - Ob Ergebnisse bei Blur ausgeblendet werden sollen
 * @param {boolean} config.enableFocusShow - Ob Ergebnisse bei Focus wieder angezeigt werden sollen
 * @param {boolean} config.autoFocus - Ob das Suchfeld automatisch fokussiert werden soll
 */
function initializeSearch(config) {
    const searchInput = document.getElementById(config.searchInputId);
    const resultsContainer = document.getElementById(config.resultsContainerId);
    const resultsList = document.querySelector(config.resultsListSelector);
    const clearButton = document.getElementById('mobile-clear-search');
    const emptyState = document.getElementById('search-empty');
    
    // Prüfen ob alle Elemente existieren
    if (!searchInput || !resultsContainer) {
        console.error('Search elements not found');
        return;
    }

    /**
     * Suchergebnisse als HTML-Elemente darstellen
     * @param {Array} results - Array mit Suchergebnissen
     */
    function displayResults(results) {
        // Container oder Liste leeren
        if (resultsList) {
            resultsList.innerHTML = '';
        } else {
            resultsContainer.innerHTML = '';
        }

        if (results.length === 0) {
            resultsContainer.style.display = 'none';
            if (emptyState) {
                emptyState.style.display = '';
                emptyState.innerHTML = '<p>Keine Nutzer gefunden.</p>';
            }
            return;
        }

        // Überschrift für Desktop optional, auf Mobile verzichten wir zugunsten kompakter Darstellung
        if (config.createHeading) {
            const heading = document.createElement('h3');
            heading.textContent = 'Nutzer gefunden';
            resultsContainer.appendChild(heading);
        }

        // Ergebnisliste erstellen oder vorhandene verwenden (nutzer-list Markup wie Follower-Liste)
        let actualResultsList = resultsList;
        if (!actualResultsList) {
            actualResultsList = document.createElement('div');
            actualResultsList.classList.add('user-list');
            resultsContainer.appendChild(actualResultsList);
        } else {
            actualResultsList.classList.add('user-list');
        }

        // Helper: Suchbegriff hervorheben
        const query = searchInput.value.trim();
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
        const highlightRegex = query.length >= 2 ? new RegExp(`(${escapeRegExp(query)})`, 'ig') : null;
        function getHighlightedName(name) {
            if (!highlightRegex) return name;
            return name.replace(highlightRegex, '<mark>$1</mark>');
        }

        // Ergebnisse durchlaufen und HTML-Elemente erstellen
        results.forEach(user => {
            const link = document.createElement('a');
            link.href = user.profileUrl;
            link.classList.add('user-list-item');

            const img = document.createElement('img');
            img.src = user.avatar;
            img.alt = 'Profilbild';
            img.classList.add('user-avatar');

            const nameSpan = document.createElement('span');
            nameSpan.innerHTML = getHighlightedName(user.name);
            nameSpan.classList.add('user-name');

            link.appendChild(img);
            link.appendChild(nameSpan);
            actualResultsList.appendChild(link);
        });

        resultsContainer.style.display = 'block';
        if (emptyState) emptyState.style.display = 'none';
    }

    /**
     * Führt die Suchanfrage an den Server durch
     * @param {string} query - Der Suchbegriff
     */
    function performSearch(query) {
        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            if (emptyState) emptyState.style.display = '';
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
    }

    // Live-Suche bei Eingabe
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        if (clearButton) clearButton.hidden = query.length === 0;
        performSearch(query);
    });

    // Clear input
    if (clearButton) {
        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            clearButton.hidden = true;
            if (resultsList) resultsList.innerHTML = '';
            resultsContainer.style.display = 'none';
            if (emptyState) emptyState.style.display = '';
            searchInput.focus();
        });
    }

    // Ergebnisse ausblenden bei Klick außerhalb (optional)
    if (config.enableBlurHide) {
        searchInput.addEventListener('blur', () => {
            setTimeout(() => {
                resultsContainer.style.display = 'none';
            }, 150);
        });
    }

    // Ergebnisse wieder anzeigen bei Focus (optional)
    if (config.enableFocusShow) {
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) {
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    }

    // Auto-Focus auf das Suchfeld (optional)
    if (config.autoFocus) {
        searchInput.focus();
    }
}

// Für Desktop-Header
function initializeDesktopSearch() {
    initializeSearch({
        searchInputId: 'header-search-input',
        resultsContainerId: 'header-search-results',
        resultsListSelector: '.header-search-results-list',
        createHeading: true,
        enableBlurHide: true,
        enableFocusShow: true,
        autoFocus: false
    });
}

// Für Mobile-Suche
function initializeMobileSearch() {
    initializeSearch({
        searchInputId: 'mobile-search-input',
        resultsContainerId: 'search-results',
        resultsListSelector: '.mobile-search-results-list',
        createHeading: false,
        enableBlurHide: false,
        enableFocusShow: false,
        autoFocus: true
    });
} 