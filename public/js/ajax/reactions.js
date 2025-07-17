// Reaktions-AJAX-Funktionalität
class ReactionAjax {
    constructor() {
        // Mapping von Emojis zu ihren Reaktionstypen (entspricht dem PHP-Mapping)
        this.reactionEmojiMap = {
            'Daumen Hoch': '👍',
            'Daumen Runter': '👎',
            'Herz': '❤️',
            'Lachen': '🤣',
            'Fragezeichen': '❓',
            'Ausrufezeichen': '‼️'
        };
        
        this.setupEventListeners();
    }

    /**
     * Initialisiert Event-Listener für Reaktions-Formulare
     */
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.attachReactionHandlers();
        });
    }

    /**
     * AJAX-Handler für Reaktions-Formulare
     */
    attachReactionHandlers() {
        const reactionForms = document.querySelectorAll('form.reaction-form:not([data-handler-attached])');
        
        reactionForms.forEach(form => {
            form.setAttribute('data-handler-attached', 'true');
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleReactionToggle(form);
            });
        });
    }

    /**
     * Reaktion umschalten
     */
    async handleReactionToggle(form) {
        const button = form.querySelector('.reaction-button');
        const postId = form.querySelector('input[name="post_id"]').value;
        const emoji = form.querySelector('input[name="emoji"]').value;
        
        // Button während der Verarbeitung deaktivieren
        button.disabled = true;
        button.classList.add('loading');
        
        try {
            // Login-Status vor Aktion validieren
            const isValid = await AjaxUtils.validateLoginBeforeAction();
            if (!isValid) {
                return;
            }
            
            // AJAX-Request senden
            const response = await fetch('php/reaction_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&emoji=${encodeURIComponent(emoji)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // UI aktualisieren
                this.updateReactionButtons(data.post_id, data.reactions, data.currentUserReactions);
            } else {
                console.error('Reaktion fehlgeschlagen:', data.error);
                AjaxUtils.showFeedbackMessage(data.error || 'Fehler bei der Reaktion', 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            AjaxUtils.showFeedbackMessage('Fehler bei der Reaktion', 'error');
        } finally {
            // Button wieder aktivieren
            button.disabled = false;
            button.classList.remove('loading');
        }
    }

    /**
     * Reaktions-Buttons für einen Post aktualisieren
     */
    updateReactionButtons(postId, reactions, currentUserReactions) {
        const postReactionForms = document.querySelectorAll('form.reaction-form');
        
        postReactionForms.forEach(form => {
            const postIdInput = form.querySelector('input[name="post_id"]');
            if (postIdInput && postIdInput.value == postId) {
                const button = form.querySelector('.reaction-button');
                const emoji = form.querySelector('input[name="emoji"]').value;
                const counter = button.querySelector('.reaction-counter');
                
                // Zähler aktualisieren
                const count = reactions[emoji] || 0;
                counter.textContent = count;
                
                // Aktiv-Status aktualisieren
                const reactionType = this.getReactionTypeFromEmoji(emoji);
                const userReactionsArray = Array.isArray(currentUserReactions) ? 
                                          currentUserReactions : 
                                          (currentUserReactions || '').split(',').filter(r => r.trim());
                const isActive = userReactionsArray.includes(reactionType);
                
                if (isActive) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            }
        });
    }

    /**
     * Reaktionstyp von einem Emoji ermitteln
     */
    getReactionTypeFromEmoji(emoji) {
        for (const [type, emojiChar] of Object.entries(this.reactionEmojiMap)) {
            if (emojiChar === emoji) {
                return type;
            }
        }
        return null;
    }

    /**
     * Event-Handler für neue Reaktions-Formulare einrichten
     */
    setupForNewElements() {
        this.attachReactionHandlers();
    }
}

// Globale Instanz erstellen
window.reactionAjax = new ReactionAjax();

// Globale Funktion für nachgeladene Inhalte
window.setupReactionHandlers = function() {
    if (window.reactionAjax) {
        window.reactionAjax.setupForNewElements();
    }
}; 