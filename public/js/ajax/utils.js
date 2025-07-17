// Gemeinsame AJAX-Hilfsfunktionen
class AjaxUtils {
    /**
     * Button-Loading-Status setzen
     */
    static setButtonLoading(button, text, isLoading = true) {
        button.disabled = isLoading;
        button.textContent = text;
        if (isLoading) {
            button.classList.add('loading');
        } else {
            button.classList.remove('loading');
        }
    }

    /**
     * Feedback-Nachricht anzeigen
     */
    static showFeedbackMessage(message, type) {
        // Entferne vorhandene Feedback-Nachrichten
        const existingFeedback = document.querySelector('.feedback-message');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        // Erstelle neue Feedback-Nachricht
        const feedbackDiv = document.createElement('div');
        feedbackDiv.className = `feedback-message feedback-${type}`;
        feedbackDiv.textContent = message;
        
        // Füge Nachricht am Anfang des Main-Contents ein
        const mainContent = document.querySelector('.main-content') || document.body;
        mainContent.insertBefore(feedbackDiv, mainContent.firstChild);
        
        // Entferne Nachricht nach 5 Sekunden
        setTimeout(() => {
            feedbackDiv.remove();
        }, 5000);
    }

    /**
     * Bild komprimieren falls vorhanden
     */
    static async compressImage(file) {
        if (!window.imageCompressor) return file;
        
        try {
            return await window.imageCompressor.compressFile(file);
        } catch (error) {
            console.warn('Bildkomprimierung fehlgeschlagen:', error);
            return file;
        }
    }

    /**
     * Kommentar-Counter im Post aktualisieren
     */
    static updateCommentCount(delta) {
        const commentButton = document.querySelector('.comment-button');
        if (commentButton) {
            const currentText = commentButton.textContent;
            const match = currentText.match(/(\d+)/);
            if (match) {
                const currentCount = parseInt(match[1]);
                const newCount = Math.max(0, currentCount + delta);
                commentButton.innerHTML = `<i class="bi bi-chat-dots-fill"></i> ${newCount} Kommentar${newCount !== 1 ? 'e' : ''}`;
            }
        }
    }
    
    /**
     * Kommentar-Überschrift in der Kommentar-Sektion aktualisieren
     */
    static updateCommentsHeading(delta) {
        const commentsSection = document.querySelector('.comments-section');
        if (commentsSection) {
            const heading = commentsSection.querySelector('h2');
            if (heading) {
                const currentText = heading.textContent;
                const match = currentText.match(/(\d+)/);
                if (match) {
                    const currentCount = parseInt(match[1]);
                    const newCount = Math.max(0, currentCount + delta);
                    heading.textContent = `${newCount} Kommentar${newCount !== 1 ? 'e' : ''}`;
                }
            } else if (delta > 0) {
                // Erste Kommentar-Überschrift erstellen
                const heading = document.createElement('h2');
                heading.textContent = `${delta} Kommentar${delta !== 1 ? 'e' : ''}`;
                commentsSection.insertBefore(heading, commentsSection.firstChild);
            }
        }
    }
    
    /**
     * Antwort-Counter für einen spezifischen Kommentar aktualisieren
     */
    static updateReplyCount(commentId, delta) {
        const replyButton = document.querySelector(`button[data-comment-id="${commentId}"]`);
        if (replyButton) {
            const currentText = replyButton.textContent;
            
            // Suche nach der aktuellen Zahl in Klammern
            const match = currentText.match(/\((\d+)\)/);
            let currentCount = 0;
            
            if (match) {
                currentCount = parseInt(match[1]);
            }
            
            const newCount = Math.max(0, currentCount + delta);
            
            // Button-Text aktualisieren
            if (newCount > 0) {
                replyButton.innerHTML = `<i class="bi bi-chat-dots-fill"></i> Antworten (${newCount})`;
            } else {
                replyButton.innerHTML = `<i class="bi bi-chat-dots-fill"></i> Antworten`;
            }
        }
    }

    /**
     * Validiert den aktuellen Login-Status vor einer Aktion
     * @returns {Promise<boolean>} True wenn angemeldet und gültig, false sonst
     */
    static async validateLoginBeforeAction() {
        try {
            const response = await fetch('php/session_helper.php?action=validate', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.status === 401 || response.status === 403) {
                window.location.href = 'Login.php';
                return false;
            }
            
            const result = await response.json();
            
            if (!result.valid || result.error === 'Nicht angemeldet') {
                window.location.href = 'Login.php';
                return false;
            }
            
            return true;
        } catch (error) {
            console.error('Login-Validierung fehlgeschlagen:', error);
            // Bei Netzwerkfehlern nicht weiterleiten, aber false zurückgeben
            return false;
        }
    }
}

// Globale Verfügbarkeit
window.AjaxUtils = AjaxUtils; 