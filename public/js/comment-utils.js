/**
 * Kommentar-bezogene Utility-Funktionen
 * Verhindert die Navigation zu Posts bei Klicks auf Links innerhalb von Kommentaren
 */

/**
 * Event-Handler für Kommentar-Kontexte einrichten
 * Verhindert, dass Link-Klicks innerhalb von Kommentaren die Post-Navigation triggern
 */
function setupCommentContextHandlers() {
    const commentContexts = document.querySelectorAll('.comment-context');
    
    commentContexts.forEach(context => {
        const hashtagLinks = context.querySelectorAll('a.link');
        hashtagLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    });
}

/**
 * Event-Handler für Reply-Formulare (Toggle-Funktionalität)
 * @param {number} commentId - ID des Kommentars
 */
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (!form) return;

    form.classList.toggle('hidden');

    // Zustand in sessionStorage speichern
    let openReplies = JSON.parse(sessionStorage.getItem('openReplies')) || [];
    const isOpen = !form.classList.contains('hidden');

    if (isOpen) {
        if (!openReplies.includes(commentId)) {
            openReplies.push(commentId);
        }
    } else {
        openReplies = openReplies.filter(id => id !== commentId);
    }

    sessionStorage.setItem('openReplies', JSON.stringify(openReplies));
}

/**
 * Reply-Formulare aus sessionStorage wiederherstellen
 * Sollte beim DOM-Load aufgerufen werden
 */
function restoreReplyFormsState() {
    const openReplies = JSON.parse(sessionStorage.getItem('openReplies')) || [];
    openReplies.forEach(commentId => {
        const form = document.getElementById('reply-form-' + commentId);
        if (form) {
            form.classList.remove('hidden');
        }
    });
}

/**
 * Kommentar-System initialisieren
 * @param {Object} config - Konfiguration (optional)
 */
function initializeCommentSystem(config = {}) {
    // Comment-Context-Handler einrichten
    setupCommentContextHandlers();
    
    // Reply-Formulare-Status wiederherstellen
    if (config.restoreReplyForms !== false) {
        restoreReplyFormsState();
    }
}

/**
 * Kommentar-spezifische Event-Handler für dynamisch geladene Inhalte
 * Sollte nach dem Laden neuer Inhalte aufgerufen werden
 */
function setupCommentHandlersForNewContent() {
    setupCommentContextHandlers();
} 