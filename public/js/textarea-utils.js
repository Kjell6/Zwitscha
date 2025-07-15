/**
 * Textarea-Utility-Funktionen
 * Zeichenzähler und automatische Höhenanpassung
 */

/**
 * Textarea mit Zeichenzähler und Auto-Resize initialisieren
 * @param {Object} config - Konfigurationsobjekt
 * @param {string} config.textareaId - ID des Textarea-Elements
 * @param {string} config.counterSelector - CSS-Selektor für den Zeichenzähler
 * @param {number} config.maxLength - Maximale Zeichen (Standard: 300)
 * @param {number} config.warningThreshold - Schwelle für Warnung (Standard: 280)
 * @param {string} config.warningColor - Farbe für Warnung (Standard: #dc3545)
 * @param {string} config.normalColor - Normale Farbe (Standard: #6c757d)
 */
function initializeTextareaWithCounter(config) {
    // Validierung der erforderlichen Parameter
    if (!config.textareaId || !config.counterSelector) {
        console.error('TextareaUtils: textareaId und counterSelector sind erforderlich');
        return;
    }

    const textarea = document.getElementById(config.textareaId);
    const counter = document.querySelector(config.counterSelector);

    if (!textarea || !counter) {
        console.error('TextareaUtils: Textarea oder Counter nicht gefunden');
        return;
    }

    // Standard-Konfiguration
    const settings = {
        maxLength: 300,
        warningThreshold: 280,
        warningColor: '#dc3545',
        normalColor: '#6c757d',
        ...config
    };

    /**
     * Zeichenzähler aktualisieren
     */
    function updateCharCount() {
        const count = textarea.value.length;
        counter.textContent = count + '/' + settings.maxLength;
        
        // Warnung bei Überschreitung der Schwelle
        counter.style.color = count > settings.warningThreshold ? settings.warningColor : settings.normalColor;
    }

    /**
     * Textarea-Höhe automatisch anpassen
     */
    function autoResize() {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    // Event-Listener für Input-Ereignisse
    textarea.addEventListener('input', () => {
        autoResize();
        updateCharCount();
    });

    // Initialer Aufruf
    updateCharCount();
    autoResize();
}

/**
 * Einfache Textarea-Initialisierung
 * @param {string} textareaId - ID der Textarea
 * @param {string} counterSelector - CSS-Selektor für den Counter
 * @param {number} maxLength - Maximale Zeichen
 */
function initializeSimpleTextarea(textareaId, counterSelector, maxLength = 300) {
    initializeTextareaWithCounter({
        textareaId: textareaId,
        counterSelector: counterSelector,
        maxLength: maxLength
    });
}

/**
 * Nur Auto-Resize für Textarea (ohne Zeichenzähler)
 * @param {string} textareaId - ID der Textarea
 */
function initializeAutoResizeTextarea(textareaId) {
    const textarea = document.getElementById(textareaId);
    
    if (!textarea) {
        console.error('TextareaUtils: Textarea nicht gefunden');
        return;
    }

    function autoResize() {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    textarea.addEventListener('input', autoResize);
    autoResize(); // Initialer Aufruf
} 