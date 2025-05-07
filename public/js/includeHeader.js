document.addEventListener('DOMContentLoaded', function() {
    function includeHTML(elementId, filePath) {
        fetch(filePath)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.text();
            })
            .then(html => {
                const placeholder = document.getElementById(elementId);
                if (placeholder) {
                    placeholder.innerHTML = html;

                    if (filePath === 'headerDesktop.html') {
                        const script = document.createElement('script');
                        script.src = 'js/headerSearch.js';
                        script.onload = () => {
                            if (typeof initHeaderSearch === 'function') {
                                initHeaderSearch();
                            }
                        };
                        document.body.appendChild(script);
                    }
                }
            })
    }

    includeHTML('header-placeholder', 'headerDesktop.html');
});