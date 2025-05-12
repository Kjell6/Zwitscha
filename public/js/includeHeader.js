const placeholder = document.getElementById('header-placeholder');
const breakpoint = 768;

function loadHeader() {
    if (!placeholder) return;

    const isDesktop = window.innerWidth > breakpoint;
    const file = isDesktop ? 'headerDesktop.html' : 'footerMobile.html';

    fetch(file)
        .then(res => res.ok ? res.text() : Promise.reject(`Fehler: ${res.status}`))
        .then(html => {
            placeholder.innerHTML = html;

            if (isDesktop) {
                const script = document.createElement('script');
                script.src = 'js/search.js';
                script.onload = () => window.initHeaderSearch?.();
                script.onerror = () => console.error('Fehler beim Laden von search.js');
                document.body.appendChild(script);
            }
        })
        .catch(err => {
            console.error('Ladefehler:', err);
            placeholder.innerHTML = `<p>Fehler beim Laden von ${file}.</p>`;
        });
}

document.addEventListener('DOMContentLoaded', loadHeader);

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(loadHeader, 200);
});
