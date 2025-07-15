/**
 * Universelle Pagination-Funktionalität für "Mehr laden"-Buttons
 * 
 * @param {Object} config - Konfigurationsobjekt
 * @param {string} config.containerId - ID des Containers, in den neue Inhalte eingefügt werden
 * @param {string} config.buttonId - ID des "Mehr laden"-Buttons
 * @param {string} config.buttonContainerId - ID des Button-Containers
 * @param {string} config.context - Kontext für die Anfrage ('all', 'followed', 'user', 'user_comments', 'hashtag')
 * @param {number} config.limit - Anzahl der Elemente pro Anfrage
 * @param {number} config.initialOffset - Anfangs-Offset (normalerweise gleich dem Limit)
 * @param {Object} config.params - Zusätzliche Parameter für die Anfrage
 * @param {Function} config.onSuccess - Callback nach erfolgreichem Laden (optional)
 * @param {Function} config.onError - Callback bei Fehler (optional)
 */
function initializePagination(config) {
    // Validierung der erforderlichen Parameter
    if (!config.containerId || !config.buttonId || !config.context) {
        console.error('Pagination: Erforderliche Parameter fehlen');
        return;
    }

    const container = document.getElementById(config.containerId);
    const button = document.getElementById(config.buttonId);
    const buttonContainer = document.getElementById(config.buttonContainerId);

    if (!container || !button) {
        console.error('Pagination: Container oder Button nicht gefunden');
        return;
    }

    let offset = config.initialOffset || config.limit;
    const limit = config.limit;
    const context = config.context;
    const params = config.params || {};

    button.addEventListener('click', () => {
        // Button-Status während des Ladens
        button.disabled = true;
        button.textContent = 'Lädt...';

        // URL und Parameter für die Anfrage zusammenstellen
        const url = buildFetchUrl(context, offset, limit, params);

        // Inhalte vom Server laden
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Fehler beim Laden der Inhalte');
                }
                return response.text();
            })
            .then(html => {
                if (!html.trim()) {
                    // Keine weiteren Inhalte vorhanden
                    if (buttonContainer) {
                        buttonContainer.style.display = 'none';
                    }
                } else {
                    // Neue Inhalte hinzufügen und Offset aktualisieren
                    container.insertAdjacentHTML('beforeend', html);
                    offset += limit;
                    
                    // Event-Handler für neue Inhalte einrichten
                    setupNewContentHandlers();
                    
                    // Erfolgs-Callback ausführen
                    if (config.onSuccess) {
                        config.onSuccess(html, offset);
                    }

                    // Prüfen, ob weniger Inhalte geladen wurden als erwartet
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const loadedItems = tempDiv.querySelectorAll('.post, .comment-item').length;

                    if (loadedItems < limit) {
                        // Weniger als erwartet geladen - keine weiteren vorhanden
                        if (buttonContainer) {
                            buttonContainer.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Fehler beim Laden:', error);
                button.textContent = 'Fehler!';
                
                // Fehler-Callback ausführen
                if (config.onError) {
                    config.onError(error);
                }
            })
            .finally(() => {
                // Button-Status zurücksetzen
                if (buttonContainer && buttonContainer.style.display !== 'none') {
                    button.disabled = false;
                    button.textContent = 'Mehr laden';
                }
            });
    });
}

/**
 * URL für die Fetch-Anfrage zusammenstellen
 * @param {string} context - Kontext der Anfrage
 * @param {number} offset - Aktueller Offset
 * @param {number} limit - Limit pro Anfrage
 * @param {Object} params - Zusätzliche Parameter
 * @returns {string} Vollständige URL
 */
function buildFetchUrl(context, offset, limit, params) {
    const baseUrl = 'php/get-posts.php';
    const urlParams = new URLSearchParams();
    
    urlParams.append('context', context);
    urlParams.append('offset', offset);
    urlParams.append('limit', limit);
    
    // Zusätzliche Parameter hinzufügen
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            urlParams.append(key, value);
        }
    });
    
    return `${baseUrl}?${urlParams.toString()}`;
}

/**
 * Event-Handler für neue Inhalte einrichten
 */
function setupNewContentHandlers() {
    // Reaktions-Handler für neue Posts einrichten
    if (typeof setupReactionHandlers === 'function') {
        setupReactionHandlers();
    }
    
    // AJAX-Handler für neue Inhalte einrichten
    if (window.setupAjaxHandlers) {
        window.setupAjaxHandlers();
    }
    
    // Comment-Context-Handler für neue Kommentare einrichten
    if (typeof setupCommentContextHandlers === 'function') {
        setupCommentContextHandlers();
    }
}

/**
 * Hilfsfunktion für einfache Pagination-Initialisierung
 * @param {string} containerId - ID des Containers
 * @param {string} buttonId - ID des Buttons
 * @param {string} context - Kontext
 * @param {number} limit - Limit
 * @param {Object} params - Zusätzliche Parameter
 */
function initializeSimplePagination(containerId, buttonId, context, limit, params = {}) {
    initializePagination({
        containerId: containerId,
        buttonId: buttonId,
        buttonContainerId: 'mehr-laden-container',
        context: context,
        limit: limit,
        initialOffset: limit,
        params: params
    });
} 