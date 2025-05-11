document.addEventListener('DOMContentLoaded', function() {
    const mobileBreakpoint = 768; // Breakpoint für mobile Ansicht in Pixeln

    function includeHTML(elementId, filePath) {
        fetch(filePath)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status} for ${filePath}`);
                return response.text();
            })
            .then(html => {
                const placeholder = document.getElementById(elementId);
                if (placeholder) {
                    placeholder.innerHTML = html;

                    // Lade search.js nur für den Desktop-Header
                    if (filePath === 'headerDesktop.html') {
                        const script = document.createElement('script');
                        script.src = 'js/search.js';
                        script.onload = () => {
                            if (typeof initHeaderSearch === 'function') initHeaderSearch();
                        };
                        script.onerror = () => console.error('Error loading js/search.js');
                        document.body.appendChild(script);
                    }
                } else {
                    console.warn(`Placeholder with ID '${elementId}' not found.`);
                }
            })
            .catch(error => console.error('Error including HTML content:', error));
    }

    function updateContentBasedOnScreenSize() {
        if (window.innerWidth < mobileBreakpoint) {
            // Inhalt für kleine Bildschirme
            includeHTML('header-placeholder', 'footerMobile.html');
        } else {
            // Inhalt für große Bildschirme
            includeHTML('header-placeholder', 'headerDesktop.html');
        }
    }

    // Event Listener für das "resize"-Event des Fensters
    window.addEventListener('resize', updateContentBasedOnScreenSize);

    // Initialer Aufruf beim Laden der Seite, um den Inhalt sofort anzupassen
    updateContentBasedOnScreenSize();

});