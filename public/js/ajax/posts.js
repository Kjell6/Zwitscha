// Post-AJAX-Funktionalität
class PostAjax {
    constructor() {
        this.setupEventListeners();
    }

    /**
     * Initialisiert Event-Listener für Post-Formulare
     */
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.attachPostFormHandler();
            this.attachPostDeleteHandlers();
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
     * AJAX-Handler für Post-Löschung
     */
    attachPostDeleteHandlers() {
        const deleteForms = document.querySelectorAll('.delete-form[data-type="post"]');
        deleteForms.forEach(form => {
            if (form.dataset.ajaxAttached) return;
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handlePostDeletion(form);
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
            AjaxUtils.setButtonLoading(submitButton, 'Wird gepostet...');
            
            // Login-Status vor Aktion validieren
            const isValid = await AjaxUtils.validateLoginBeforeAction();
            if (!isValid) {
                return;
            }
            
            // FormData erstellen
            const formData = new FormData(form);
            
            // Bild komprimieren falls vorhanden
            const imageInput = form.querySelector('input[type="file"]');
            if (imageInput?.files[0]) {
                const compressedFile = await AjaxUtils.compressImage(imageInput.files[0]);
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
                AjaxUtils.showFeedbackMessage(result.error || result.message, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            AjaxUtils.showFeedbackMessage('Fehler beim Erstellen des Posts', 'error');
        } finally {
            // Button wieder aktivieren
            AjaxUtils.setButtonLoading(submitButton, originalText, false);
        }
    }

    /**
     * Post-Löschung verarbeiten
     */
    async handlePostDeletion(form) {
        if (!confirm('Post wirklich löschen?')) return;
        
        try {
            // Login-Status vor Aktion validieren
            const isValid = await AjaxUtils.validateLoginBeforeAction();
            if (!isValid) {
                return;
            }
            
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
                // Post aus DOM entfernen
                const postElement = form.closest('.post');
                if (postElement) {
                    postElement.remove();
                } else {
                    // Auf Detailseite gibt es keinen .post-Wrapper → zurück navigieren
                    const isPostDetails = window.location.pathname.includes('postDetails.php');
                    if (isPostDetails) {
                        // Erst versuchen zurückzugehen, sonst zur Startseite
                        if (window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.location.href = 'index.php';
                        }
                        return;
                    }
                }
                
            } else {
                AjaxUtils.showFeedbackMessage(result.error || result.message, 'error');
            }
            
        } catch (error) {
            console.error('AJAX-Fehler:', error);
            AjaxUtils.showFeedbackMessage('Fehler beim Löschen', 'error');
        }
    }

    /**
     * Post-Formular zurücksetzen
     */
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

    /**
     * Neuen Post an den Anfang der Liste einfügen
     */
    prependNewPost(postHtml) {
        const postsContainer = document.getElementById('posts-container');
        if (postsContainer) {
            postsContainer.insertAdjacentHTML('afterbegin', postHtml);
            
            // Event-Listener für neue Elemente aktivieren
            this.attachNewPostEventListeners();
            
            // Reaktions-Handler für neue Posts aktivieren
            if (window.reactionAjax) {
                window.reactionAjax.setupForNewElements();
            }
        }
    }

    /**
     * Event-Listener für neue Posts aktivieren
     */
    attachNewPostEventListeners() {
        // Neue Lösch-Formulare für Posts
        const newDeleteForms = document.querySelectorAll('.delete-form[data-type="post"]:not([data-ajax-attached])');
        newDeleteForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handlePostDeletion(form);
            });
            form.dataset.ajaxAttached = 'true';
        });
    }
}

// Globale Instanz erstellen
window.postAjax = new PostAjax(); 