/**
 * Navigation-Funktionalität für Posts
 * Verhindert Navigation bei interaktiven Elementen
 */

/**
 * Navigation zu einem Post
 * @param {Event} event - Das Click-Event
 * @param {number} postId - ID des Posts
 */
function navigateToPost(event, postId) {
    // Verhindere Navigation bei interaktiven Elementen (Buttons, Links)
    if (event.target.closest(".no-post-details")) {
        return;
    }
    // Navigiere zur Post-Detail-Seite
    window.location.href = "postDetails.php?id=" + postId;
}

/**
 * Navigation-Event-Handler für Posts einrichten
 * Kann nach dem Laden neuer Posts aufgerufen werden
 */
function setupPostNavigationHandlers() {
    const posts = document.querySelectorAll('.post[data-post-id]');
    
    posts.forEach(post => {
        const postId = post.getAttribute('data-post-id');
        if (postId && !post.hasAttribute('data-nav-initialized')) {
            post.addEventListener('click', (event) => {
                navigateToPost(event, postId);
            });
            post.setAttribute('data-nav-initialized', 'true');
        }
    });
}

/**
 * Navigation-System initialisieren
 */
function initializeNavigation() {
    setupPostNavigationHandlers();
} 