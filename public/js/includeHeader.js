// Get placeholder element
const headerPlaceholder = document.getElementById('header-placeholder');

// Define mobile breakpoint
const mobileBreakpoint = 768;

// Function to load content based on screen width
function loadDynamicContent() {
    if (!headerPlaceholder) return; // Exit if placeholder not found

    let filePath = '';
    // Choose file based on width
    if (window.innerWidth > mobileBreakpoint) {
        filePath = 'headerDesktop.html';

        const script = document.createElement('script');
        script.src = 'js/search.js';
        script.onload = () => {
            if (typeof initHeaderSearch === 'function') initHeaderSearch();
        };
        script.onerror = () => console.error('Error loading js/search.js');
        document.body.appendChild(script);

    } else {
        filePath = 'footerMobile.html';
    }

    // Fetch and insert content
    fetch(filePath)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.text();
        })
        .then(html => {
            headerPlaceholder.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading content:', error);
            headerPlaceholder.innerHTML = `<p>Fehler beim Laden von ${filePath}.</p>`;
        });
}

// --- Event Listeners ---

// Load on page load
document.addEventListener('DOMContentLoaded', loadDynamicContent);

// Load on window resize (with debounce)
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(loadDynamicContent, 200);
});