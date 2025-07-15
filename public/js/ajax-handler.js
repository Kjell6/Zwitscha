// Zentrales AJAX-Modul für Posts und Kommentare
class AjaxHandler {
    constructor() {
        this.setupEventListeners();
        this.imageCompression = window.imageCompressor;
    }

    /**
     * Initialisiert alle Event-Listener für AJAX-Formulare
     */
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.attachPostFormHandler();
            this.attachCommentFormHandlers();
            this.attachDeleteHandlers();
        });
    }

    /**
     * AJAX-Handler für Post-Erstellung
     */
    attachPostFormHandler() {
        const postForm = document.querySelector('.create-post-form:not(.comment-form):not(.reply-form)');
        if (!postForm || postForm.dataset.ajaxAttached) return;

        postForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handlePostCreation(postForm);
        });
        
        postForm.dataset.ajaxAttached = 'true';
    }

    /**
     * AJAX-Handler für Kommentar-Erstellung
     */
    attachCommentFormHandlers() {
        // Haupt-Kommentar-Formulare
        const commentForms = document.querySelectorAll('.comment-form');
        commentForms.forEach(form => {
            // Prüfe, ob bereits ein Event-Listener vorhanden ist
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentCreation(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
        
        // Reply-Formulare
        const replyForms = document.querySelectorAll('.reply-form');
        replyForms.forEach(form => {
            // Prüfe, ob bereits ein Event-Listener vorhanden ist
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentCreation(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
        
        // Antwort-Buttons
        const replyButtons = document.querySelectorAll('.reply-button');
        replyButtons.forEach(button => {
            if (button.dataset.ajaxAttached) return;
            
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const commentId = button.dataset.commentId;
                if (commentId && window.toggleReplyForm) {
                    window.toggleReplyForm(commentId);
                }
            });
            
            button.dataset.ajaxAttached = 'true';
        });
    }

    /**
     * AJAX-Handler für Lösch-Aktionen
     */
    attachDeleteHandlers() {
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            // Prüfe, ob bereits ein Event-Listener vorhanden ist
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleDeletion(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
    }

    /**
     * Post-Erstellung verarbeiten
     */
    async handlePostCreation(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        try {
            // Button deaktivieren
            this.setButtonLoading(submitButton, 'Wird gepostet...');
            
            // FormData erstellen
            const formData = new FormData(form);
            
            // Bild komprimieren falls vorhanden
            const imageInput = form.querySelector('input[type="file"]');
            if (imageInput?.files[0]) {
                const compressedFile = await this.compressImage(imageInput.files[0]);
                formData.set('post_image', compressedFile);
            }
            
            // AJAX-Request senden
            const response = await fetch('php/post_action_handler.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Formular zurücksetzen
                this.resetPostForm(form);
                
                // Neuen Post an den Anfang der Liste einfügen
                this.prependNewPost(result.post);
                
            } else {
                this.showFeedbackMessage(result.error, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            this.showFeedbackMessage('Fehler beim Erstellen des Posts', 'error');
        } finally {
            // Button wieder aktivieren
            this.setButtonLoading(submitButton, originalText, false);
        }
    }

    /**
     * Kommentar-Erstellung verarbeiten
     */
    async handleCommentCreation(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        try {
            // Button deaktivieren
            this.setButtonLoading(submitButton, 'Wird kommentiert...');
            
            // FormData erstellen
            const formData = new FormData(form);
            
            // AJAX-Request senden
            const response = await fetch('php/post_action_handler.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Formular zurücksetzen
                this.resetCommentForm(form);
                
                // Neuen Kommentar einfügen
                this.insertNewComment(result.comment, form);
                
            } else {
                this.showFeedbackMessage(result.error, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            this.showFeedbackMessage('Fehler beim Erstellen des Kommentars', 'error');
        } finally {
            // Button wieder aktivieren
            this.setButtonLoading(submitButton, originalText, false);
        }
    }

    /**
     * Lösch-Aktionen verarbeiten
     */
    async handleDeletion(form) {
        const action = form.querySelector('input[name="action"]').value;
        const isPost = action === 'delete_post';
        const confirmMessage = isPost ? 'Post wirklich löschen?' : 'Kommentar wirklich löschen?';
        
        if (!confirm(confirmMessage)) return;
        
        try {
            // FormData erstellen
            const formData = new FormData(form);
            
            // AJAX-Request senden
            const response = await fetch('php/post_action_handler.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Element aus DOM entfernen
                this.removeElementFromDOM(form, isPost);
                
            } else {
                this.showFeedbackMessage(result.error, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            this.showFeedbackMessage('Fehler beim Löschen', 'error');
        }
    }

    /**
     * Hilfsfunktionen
     */
    setButtonLoading(button, text, isLoading = true) {
        button.disabled = isLoading;
        button.textContent = text;
        if (isLoading) {
            button.classList.add('loading');
        } else {
            button.classList.remove('loading');
        }
    }

    async compressImage(file) {
        if (!this.imageCompression) return file;
        
        try {
            return await this.imageCompression.compressFile(file);
        } catch (error) {
            console.warn('Bildkomprimierung fehlgeschlagen:', error);
            return file;
        }
    }

    showFeedbackMessage(message, type) {
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

    resetPostForm(form) {
        // Textarea zurücksetzen
        const textarea = form.querySelector('textarea');
        if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
        }
        
        // Bild-Input zurücksetzen
        const imageInput = form.querySelector('input[type="file"]');
        if (imageInput) {
            imageInput.value = '';
        }
        
        // Bild-Vorschau verstecken
        const imagePreview = document.getElementById('image-preview');
        if (imagePreview) {
            imagePreview.style.display = 'none';
        }
        
        // Zeichenzähler zurücksetzen
        const charCount = form.querySelector('.character-count');
        if (charCount) {
            charCount.textContent = '0/300';
            charCount.style.color = '#6c757d';
        }
    }

    resetCommentForm(form) {
        const textarea = form.querySelector('textarea');
        if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
        }
        
        const charCount = form.querySelector('.character-count');
        if (charCount) {
            charCount.textContent = '0/300';
            charCount.style.color = '#6c757d';
        }
    }

    prependNewPost(postHtml) {
        const postsContainer = document.getElementById('posts-container');
        if (postsContainer) {
            postsContainer.insertAdjacentHTML('afterbegin', postHtml);
            
            // 🔥 WICHTIG: Event-Listener für neue Elemente aktivieren
            this.attachEventListenersToNewElements();
            
            // Event-Handler für neue Posts einrichten
            if (window.setupReactionHandlers) {
                window.setupReactionHandlers();
            }
        }
    }

    insertNewComment(commentHtml, form) {
        const action = form.querySelector('input[name="action"]').value;
        
        if (action === 'create_comment') {
            // Haupt-Kommentar einfügen
            const commentsSection = document.querySelector('.comments-section');
            if (commentsSection) {
                const emptyState = commentsSection.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
                
                let commentsList = commentsSection.querySelector('.comments-list');
                if (!commentsList) {
                    commentsList = document.createElement('div');
                    commentsList.className = 'comments-list';
                    commentsSection.appendChild(commentsList);
                }
                
                commentsList.insertAdjacentHTML('beforeend', commentHtml);
                
                // Kommentar-Zähler aktualisieren
                this.updateCommentCount(1);
                
                // Haupt-Kommentar-Überschrift aktualisieren
                this.updateCommentsHeading(1);
            }
        } else if (action === 'reply_comment') {
            // Antwort einfügen
            const parentCommentId = form.querySelector('input[name="parent_comment_id"]').value;
            const replySection = document.getElementById(`reply-form-${parentCommentId}`);
            
            if (replySection) {
                let repliesList = replySection.querySelector('.replies-list');
                if (!repliesList) {
                    repliesList = document.createElement('div');
                    repliesList.className = 'replies-list';
                    replySection.appendChild(repliesList);
                }
                
                repliesList.insertAdjacentHTML('beforeend', commentHtml);
                
                // Antwort-Zähler für den Parent-Kommentar aktualisieren
                this.updateReplyCount(parentCommentId, 1);
                
                // Kommentar-Zähler für den Post aktualisieren (Antworten zählen auch als Kommentare)
                this.updateCommentCount(1);
            }
        }
        
        // 🔥 WICHTIG: Event-Listener für neue Elemente aktivieren
        this.attachEventListenersToNewElements();
        
        // Reaktions-Handler für neue Elemente aktivieren
        if (window.setupReactionHandlers) {
            window.setupReactionHandlers();
        }
    }

    removeElementFromDOM(form, isPost) {
        if (isPost) {
            // Post entfernen
            const postElement = form.closest('.post');
            if (postElement) {
                postElement.remove();
            }
        } else {
            // Kommentar oder Antwort entfernen
            // Suche nach beiden CSS-Klassen: .comment-layout (normale Kommentare) und .comment-item (kommentarEinzeln.php)
            const commentElement = form.closest('.post.comment-layout') || form.closest('.post.comment-item');
            
            if (commentElement) {
                // Prüfen, ob es sich um eine Antwort handelt
                const isReply = commentElement.closest('.replies-list');
                
                if (isReply) {
                    // Es ist eine Antwort
                    const replySection = commentElement.closest('.reply-section');
                    if (replySection) {
                        const commentId = replySection.id.replace('reply-form-', '');
                        this.updateReplyCount(commentId, -1);
                    }
                    // Antworten zählen auch als Kommentare
                    this.updateCommentCount(-1);
                } else {
                    // Es ist ein Haupt-Kommentar
                    // Zähle alle Antworten, die mit diesem Kommentar gelöscht werden
                    const repliesList = commentElement.querySelector('.replies-list');
                    const replyCount = repliesList ? repliesList.querySelectorAll('.post.comment-layout, .post.comment-item').length : 0;
                    
                    // Kommentar-Zähler reduzieren (Haupt-Kommentar + alle Antworten)
                    this.updateCommentCount(-(1 + replyCount));
                    
                    // Haupt-Kommentar-Überschrift aktualisieren
                    this.updateCommentsHeading(-(1 + replyCount));
                }
                
                commentElement.remove();
                
                // Spezialbehandlung für kommentarEinzeln.php: Zurück zur vorherigen Seite
                if (commentElement.classList.contains('comment-item')) {
                    // Kurze Verzögerung, damit der Benutzer die Löschung sieht
                    setTimeout(() => {
                        // Überprüfe, ob wir auf der kommentarEinzeln.php Seite sind
                        if (window.location.pathname.includes('kommentarEinzeln.php')) {
                            // Gehe zur vorherigen Seite zurück
                            window.history.back();
                        }
                    }, 500);
                }
            }
        }
    }

    /**
     * Aktualisiert den Kommentar-Counter im Post (z.B. "5 Kommentare")
     */
    updateCommentCount(delta) {
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
     * Aktualisiert die Kommentar-Überschrift in der Kommentar-Sektion (z.B. "5 Kommentare")
     */
    updateCommentsHeading(delta) {
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
     * Aktualisiert den Antwort-Counter für einen spezifischen Kommentar
     */
    updateReplyCount(commentId, delta) {
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
     * Aktiviert Event-Listener für neue DOM-Elemente
     * Diese Funktion wird nach dem Hinzufügen neuer Kommentare/Posts aufgerufen
     */
    attachEventListenersToNewElements() {
        // Alle neuen Kommentar-Formulare ohne Event-Listener finden
        const newCommentForms = document.querySelectorAll('.comment-form:not([data-ajax-attached]), .reply-form:not([data-ajax-attached])');
        newCommentForms.forEach(form => {
            // Prüfe, ob bereits ein Event-Listener vorhanden ist
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentCreation(form);
            });
            
            // Markiere als verarbeitet
            form.dataset.ajaxAttached = 'true';
        });
        
        // Alle neuen Lösch-Formulare ohne Event-Listener finden
        const newDeleteForms = document.querySelectorAll('.delete-form:not([data-ajax-attached])');
        newDeleteForms.forEach(form => {
            // Prüfe, ob bereits ein Event-Listener vorhanden ist
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleDeletion(form);
            });
            
            // Markiere als verarbeitet
            form.dataset.ajaxAttached = 'true';
        });
        
        // Alle neuen Antwort-Buttons ohne Event-Listener finden
        const newReplyButtons = document.querySelectorAll('.reply-button:not([data-ajax-attached])');
        newReplyButtons.forEach(button => {
            if (button.dataset.ajaxAttached) return;
            
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const commentId = button.dataset.commentId;
                if (commentId && window.toggleReplyForm) {
                    window.toggleReplyForm(commentId);
                }
            });
            
            // Markiere als verarbeitet
            button.dataset.ajaxAttached = 'true';
        });
    }
}

// Globale Instanz erstellen
window.ajaxHandler = new AjaxHandler();

// Globale Funktion für nachgeladene Inhalte
window.setupAjaxHandlers = function() {
    if (window.ajaxHandler) {
        window.ajaxHandler.attachCommentFormHandlers();
        window.ajaxHandler.attachDeleteHandlers();
    }
}; 