// Beispiel-Daten für den Haupt-Post in der Detail-Ansicht
const detailPost = {
    id: 1,
    nutzer_id: 42,
    text: "Das ist ein Beispiel-Post für die Detailansicht mit ausführlichem Inhalt, der in postdetails angezeigt wird.",
    datumZeit: "2025-04-27T10:30:00Z",
    reactions: { "👍": 4, "👎": 0, "❤️": 7, "🤣": 1, "❓": 0, "‼️": 2 },
    comments: 2
};

// Beispiel-Daten für zwei Kommentare
const detailComments = [
    {
        id: 101,
        nutzer_id: 5,
        text: 'Super Beitrag! 😊',
        datumZeit: '2025-04-27T12:00:00Z',
    },
    {
        id: 102,
        nutzer_id: 8,
        text: 'Sehr interessant, danke fürs Teilen.',
        datumZeit: '2025-04-27T13:15:00Z',
    }
];

// Template-String für Kommentar (fetch ersetzt später)
let commentTemplateHtml = '';
fetch('kommentar.html')
    .then(r => r.text())
    .then(html => {
        commentTemplateHtml = html;
        initDetail();
    })
    .catch(err => console.error('Kommentar-Template-Fehler:', err));

// Hilfsfunktion: „vor x Zeit“
function timeAgo(isoString) {
    const now = new Date();
    const postDate = new Date(isoString);
    const diffMs = now - postDate;
    const minutes = Math.floor(diffMs / 1000 / 60);
    if (minutes < 60) return `vor ${minutes} Min.`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `vor ${hours} Std.`;
    const days = Math.floor(hours / 24);
    return `vor ${days} Tag${days > 1 ? 'en' : ''}`;
}

// Initialisierung der Detail-Seite
function initDetail() {
    renderDetailPost();
    renderDetailComments();
    setupCommentCreation();
    setupReactionToggle();
    setupPostDelete();
}

// Rendert den Haupt-Post in der Detail-Ansicht
function renderDetailPost() {
    const article = document.querySelector('article.detail-post');

    // Autor und Options-Button (Nutzer-ID hier als Platzhalter)
    article.querySelector('.post-author-name').textContent = `User ${detailPost.nutzer_id}`;

    // Inhalt
    article.querySelector('.post-content p').textContent = detailPost.text;

    // Zeitstempel
    const timeEl = article.querySelector('.post-timestamp-detail');
    timeEl.dateTime = detailPost.datumZeit;
    timeEl.textContent = new Date(detailPost.datumZeit)
        .toLocaleString('de-DE', { day:'2-digit', month:'2-digit', year:'2-digit', hour:'2-digit', minute:'2-digit' });

    // Kommentar-Anzahl
    article.querySelector('.number-comments').textContent = `${detailPost.comments} Kommentare`;

    // Reaktionen
    const reactionsContainer = article.querySelector('.post-reactions');
    reactionsContainer.innerHTML = Object.entries(detailPost.reactions)
        .map(([emoji, count]) =>
            `<button class="reaction-button" type="button" data-emoji="${emoji}" aria-pressed="false">
         ${emoji} <span class="reaction-counter">${count}</span>
       </button>`
        ).join('');
}

// Rendert die Beispiel-Kommentare
function renderDetailComments() {
    const list = document.getElementById('comments-list');
    list.innerHTML = '';

    detailComments.forEach(comment => {
        let html = commentTemplateHtml
            .replace(/\[Autorname\]/g, `User ${comment.nutzer_id}`)
            .replace(/<p>\[.*?\]<\/p>/, `<p>${comment.text}</p>`)
            .replace(/datetime="[^"]*"/, `datetime="${comment.datumZeit}"`)
            .replace(/\[[^\]]*\]/, timeAgo(comment.datumZeit));

        // Kommentar-Reaktionen (optional erweiterbar)
        // hier könnte man ähnlich wie beim Post Emoji-Buttons ersetzen

        const li = document.createElement('li');
        li.innerHTML = html;
        list.append(li);
    });

    setupCommentDeletes();
}

// Neue Kommentare hinzufügen
function setupCommentCreation() {
    const input = document.getElementById('comment-input');
    const btn   = document.getElementById('comment-button');

    btn.addEventListener('click', () => {
        const text = input.value.trim();
        if (!text) return;

        const newComment = {
            id: Date.now(),
            nutzer_id: 1,
            text: text,
            datumZeit: new Date().toISOString()
        };

        // Direkt in DOM einfügen
        let html = commentTemplateHtml
            .replace(/\[Autorname\]/g, `User ${newComment.nutzer_id}`)
            .replace(/<p>\[.*?\]<\/p>/, `<p>${newComment.text}</p>`)
            .replace(/datetime="[^"]*"/, `datetime="${newComment.datumZeit}"`)
            .replace(/\[[^\]]*\]/, timeAgo(newComment.datumZeit));

        const li = document.createElement('li');
        li.innerHTML = html;
        document.getElementById('comments-list').prepend(li);

        // Kommentarzähler aktualisieren
        detailPost.comments += 1;
        document.querySelector('.number-comments').textContent = `${detailPost.comments} Kommentare`;

        input.value = '';
        setupCommentDeletes();
    });

    // Automatisches Höhe-Anpassen
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    });
}

// Reaction-Toggle auch in Detail-Ansicht aktivieren
function setupReactionToggle() {
    const userReactions = new Set();
    document.querySelector('section.detail-actions')
        .addEventListener('click', e => {
            const btn = e.target.closest('.reaction-button');
            if (!btn) return;

            const emoji = btn.dataset.emoji;
            const ctr   = btn.querySelector('.reaction-counter');
            let count   = parseInt(ctr.textContent, 10);

            if (userReactions.has(emoji)) {
                userReactions.delete(emoji);
                btn.setAttribute('aria-pressed', 'false');
                btn.classList.remove('active');
                ctr.textContent = count - 1;
            } else {
                userReactions.add(emoji);
                btn.setAttribute('aria-pressed', 'true');
                btn.classList.add('active');
                ctr.textContent = count + 1;
            }
        });
}

function setupPostDelete() {
    const deleteBtn = document.querySelector('.detail-post .post-options-button');
    const postEl    = document.querySelector('.detail-post');

    deleteBtn.addEventListener('click', () => {
        const confirmed = confirm("Möchtest du diesen Post wirklich löschen?");
        if (!confirmed) return;

        postEl.remove();
        document.querySelector('.comments-section')?.remove();

        // Nach kurzem Delay zur Startseite zurück
        window.location.href = 'index.html';
    });
}


function setupCommentDeletes() {
    document.querySelectorAll('#comments-list .post-options-button')
        .forEach(btn => {
            btn.addEventListener('click', () => {
                const confirmed = confirm("Diesen Kommentar löschen?");
                if (!confirmed) return;

                const li = btn.closest('li') || btn.closest('article.post');
                li.remove();

                detailPost.comments -= 1;
                document.querySelector('.number-comments').textContent = `${detailPost.comments} Kommentare`;
            });
        });
}

// Starte alles, sobald DOM bereit ist
document.addEventListener('DOMContentLoaded', () => {
    // initDetail wird aufgerufen, sobald das Kommentar-Template geladen ist
});