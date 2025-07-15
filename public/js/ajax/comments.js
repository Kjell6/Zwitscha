// Kommentar-AJAX-Funktionalität
class CommentAjax {
    constructor() {
        this.setupEventListeners();
    }

    /**
     * Initialisiert Event-Listener für Kommentar-Formulare
     */
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.attachCommentFormHandlers();
            this.attachCommentDeleteHandlers();
            this.attachReplyButtonHandlers();
        });
    }

    /**
     * AJAX-Handler für Kommentar-Erstellung
     */
    attachCommentFormHandlers() {
        // Haupt-Kommentar-Formulare
        const commentForms = document.querySelectorAll('.comment-form');
        commentForms.forEach(form => {
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
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentCreation(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
    }

    /**
     * AJAX-Handler für Kommentar-Löschung
     */
    attachCommentDeleteHandlers() {
        const deleteForms = document.querySelectorAll('.delete-form:not([data-type="post"])');
        deleteForms.forEach(form => {
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentDeletion(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
    }

    /**
     * AJAX-Handler für Antwort-Buttons
     */
    attachReplyButtonHandlers() {
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
     * Kommentar-Erstellung verarbeiten
     */
    async handleCommentCreation(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        try {
            // Button deaktivieren
            AjaxUtils.setButtonLoading(submitButton, 'Wird kommentiert...');
            
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
                AjaxUtils.showFeedbackMessage(result.error || result.message, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            AjaxUtils.showFeedbackMessage('Fehler beim Erstellen des Kommentars', 'error');
        } finally {
            // Button wieder aktivieren
            AjaxUtils.setButtonLoading(submitButton, originalText, false);
        }
    }

    /**
     * Kommentar-Löschung verarbeiten
     */
    async handleCommentDeletion(form) {
        if (!confirm('Kommentar wirklich löschen?')) return;
        
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
                // Kommentar aus DOM entfernen
                this.removeCommentFromDOM(form);
                
            } else {
                AjaxUtils.showFeedbackMessage(result.error || result.message, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            AjaxUtils.showFeedbackMessage('Fehler beim Löschen', 'error');
        }
    }

    /**
     * Kommentar-Formular zurücksetzen
     */
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

    /**
     * Neuen Kommentar in DOM einfügen
     */
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
                AjaxUtils.updateCommentCount(1);
                AjaxUtils.updateCommentsHeading(1);
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
                
                // Antwort-Zähler aktualisieren
                AjaxUtils.updateReplyCount(parentCommentId, 1);
                AjaxUtils.updateCommentCount(1);
            }
        }
        
        // Event-Listener für neue Elemente aktivieren
        this.attachNewCommentEventListeners();
        
        // Reaktions-Handler für neue Elemente aktivieren
        if (window.reactionAjax) {
            window.reactionAjax.setupForNewElements();
        }
    }

    /**
     * Kommentar aus DOM entfernen
     */
    removeCommentFromDOM(form) {
        const commentElement = form.closest('.post.comment-layout') || form.closest('.post.comment-item');
        
        if (commentElement) {
            // Prüfen, ob es sich um eine Antwort handelt
            const isReply = commentElement.closest('.replies-list');
            
            if (isReply) {
                // Es ist eine Antwort
                const replySection = commentElement.closest('.reply-section');
                if (replySection) {
                    const commentId = replySection.id.replace('reply-form-', '');
                    AjaxUtils.updateReplyCount(commentId, -1);
                }
                AjaxUtils.updateCommentCount(-1);
            } else {
                // Es ist ein Haupt-Kommentar
                const repliesList = commentElement.querySelector('.replies-list');
                const replyCount = repliesList ? repliesList.querySelectorAll('.post.comment-layout, .post.comment-item').length : 0;
                
                // Kommentar-Zähler reduzieren (Haupt-Kommentar + alle Antworten)
                AjaxUtils.updateCommentCount(-(1 + replyCount));
                AjaxUtils.updateCommentsHeading(-(1 + replyCount));
            }
            
            commentElement.remove();
            
            // Spezialbehandlung für kommentarEinzeln.php
            if (commentElement.classList.contains('comment-item')) {
                setTimeout(() => {
                    if (window.location.pathname.includes('kommentarEinzeln.php')) {
                        window.history.back();
                    }
                }, 500);
            }
        }
    }

    /**
     * Event-Listener für neue Kommentare aktivieren
     */
    attachNewCommentEventListeners() {
        // Neue Kommentar-Formulare
        const newCommentForms = document.querySelectorAll('.comment-form:not([data-ajax-attached]), .reply-form:not([data-ajax-attached])');
        newCommentForms.forEach(form => {
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentCreation(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
        
        // Neue Lösch-Formulare
        const newDeleteForms = document.querySelectorAll('.delete-form:not([data-type="post"]):not([data-ajax-attached])');
        newDeleteForms.forEach(form => {
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCommentDeletion(form);
            });
            
            form.dataset.ajaxAttached = 'true';
        });
        
        // Neue Antwort-Buttons
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
            
            button.dataset.ajaxAttached = 'true';
        });
    }

    /**
     * Globale Funktion für nachgeladene Inhalte
     */
    setupForNewElements() {
        this.attachCommentFormHandlers();
        this.attachCommentDeleteHandlers();
        this.attachReplyButtonHandlers();
    }
}

// Globale Instanz erstellen
window.commentAjax = new CommentAjax();

// Globale Funktion für nachgeladene Inhalte
window.setupAjaxHandlers = function() {
    if (window.commentAjax) {
        window.commentAjax.setupForNewElements();
    }
}; 