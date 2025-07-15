// Ajax-Reaktions-Funktionalität
document.addEventListener('DOMContentLoaded', function() {
    // Mapping von Emojis zu ihren Reaktionstypen (entspricht dem PHP-Mapping)
    const reactionEmojiMap = {
        'Daumen Hoch': '👍',
        'Daumen Runter': '👎',
        'Herz': '❤️',
        'Lachen': '🤣',
        'Fragezeichen': '❓',
        'Ausrufezeichen': '‼️'
    };

    // Alle Reaktions-Formulare finden und Event-Listener hinzufügen
    const reactionForms = document.querySelectorAll('form.reaction-form');
    
    reactionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Verhindert das normale Formular-Submit
            
            const button = form.querySelector('.reaction-button');
            const postId = form.querySelector('input[name="post_id"]').value;
            const emoji = form.querySelector('input[name="emoji"]').value;
            
            // Button während der Verarbeitung deaktivieren
            button.disabled = true;
            button.classList.add('loading');
            
            // Ajax-Request senden
            const url = window.location.pathname.includes('postDetails.php') ? 
                       'php/reaction_handler.php' : 
                       'php/reaction_handler.php';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&emoji=${encodeURIComponent(emoji)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // UI aktualisieren
                    updateReactionButtons(data.post_id, data.reactions, data.currentUserReactions);
                } else {
                    console.error('Reaktion fehlgeschlagen:', data.error);
                }
            })
            .catch(error => {
                console.error('Ajax-Fehler:', error);
            })
            .finally(() => {
                // Button wieder aktivieren
                button.disabled = false;
                button.classList.remove('loading');
            });
        });
    });

    // Funktion zum Ermitteln des Reaktionstyps von einem Emoji
    function getReactionTypeFromEmoji(emoji) {
        for (const [type, emojiChar] of Object.entries(reactionEmojiMap)) {
            if (emojiChar === emoji) {
                return type;
            }
        }
        return null;
    }

    function updateReactionButtons(postId, reactions, currentUserReactions) {
        // Alle Reaktions-Formulare für diesen Post finden
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
                const reactionType = getReactionTypeFromEmoji(emoji);
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
});

// Globale Funktion für nachgeladene Posts
window.setupReactionHandlers = function() {
    // Mapping von Emojis zu ihren Reaktionstypen (entspricht dem PHP-Mapping)
    const reactionEmojiMap = {
        'Daumen Hoch': '👍',
        'Daumen Runter': '👎',
        'Herz': '❤️',
        'Lachen': '🤣',
        'Fragezeichen': '❓',
        'Ausrufezeichen': '‼️'
    };

    // Event-Handler für neue Reaktions-Formulare
    const newReactionForms = document.querySelectorAll('form.reaction-form:not([data-handler-attached])');
    
    newReactionForms.forEach(form => {
        form.setAttribute('data-handler-attached', 'true');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = form.querySelector('.reaction-button');
            const postId = form.querySelector('input[name="post_id"]').value;
            const emoji = form.querySelector('input[name="emoji"]').value;
            
            // Button während der Verarbeitung deaktivieren
            button.disabled = true;
            button.classList.add('loading');
            
            // Ajax-Request senden
            fetch('php/reaction_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&emoji=${encodeURIComponent(emoji)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // UI aktualisieren
                    updateReactionButtonsForPost(data.post_id, data.reactions, data.currentUserReactions);
                } else {
                    console.error('Reaktion fehlgeschlagen:', data.error);
                }
            })
            .catch(error => {
                console.error('Ajax-Fehler:', error);
            })
            .finally(() => {
                // Button wieder aktivieren
                button.disabled = false;
                button.classList.remove('loading');
            });
        });
    });

    // Funktion zum Ermitteln des Reaktionstyps von einem Emoji
    function getReactionTypeFromEmoji(emoji) {
        for (const [type, emojiChar] of Object.entries(reactionEmojiMap)) {
            if (emojiChar === emoji) {
                return type;
            }
        }
        return null;
    }

    // Funktion zum Aktualisieren der Reaktions-Buttons für einen Post
    function updateReactionButtonsForPost(postId, reactions, currentUserReactions) {
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
                const reactionType = getReactionTypeFromEmoji(emoji);
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
}; 