// Beispiel-Daten
const posts = [
    {
        id: 1,
        nutzer_id: 42,
        author: "Max Mustermann",        // später aus Nutzer-Tabelle
        avatar: "assets/placeholder-profilbild.jpg",
        datumZeit: "2025-04-27T10:30:00Z",
        text: "Beispiel-Post mit anfänglichen Reaktionen.",
        reactions: { "👍":3, "👎":1, "❤️":5, "🤣":2, "❓":0, "‼️":1 },
        comments: 2
    },
    {
        id: 2,
        nutzer_id: 7,
        author: "Erika Musterfrau",
        avatar: "assets/placeholder-profilbild.jpg",
        datumZeit: "2025-05-11T12:00:00Z",
        text: "Noch ein Post, der bereits Reaktionen hat!",
        reactions: { "👍":0, "👎":0, "❤️":2, "🤣":1, "❓":1, "‼️":0 },
        comments: 0
    }
];

// Hilfsfunktion: "vor x Zeit"
function timeAgo(iso) {
    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 1000 / 60);
    if (minutes < 60) {
        return `vor ${minutes} Min.`;
    }
    const hours = Math.floor(minutes / 60);
    if (hours < 24) {
        return `vor ${hours} Std.`;
    }
    const days = Math.floor(hours / 24);
    return `vor ${days} Tag${days > 1 ? 'en' : ''}`;
}


// Lade externes Template (post.html) einmalig
let postTemplateHtml = '';
fetch('post.html')
    .then(r => r.text())
    .then(html => {
        postTemplateHtml = html;
        initFeed();
    })
    .catch(err => console.error('Template-Fehler:', err));

function initFeed() {
    loadFeed();
    setupPostCreation();
}

function renderPost(post) {
    // Platzhalter ersetzen
    let html = postTemplateHtml
        .replace(/\[Autorname\]/g, `User ${post.nutzer_id}`)
        .replace(/datetime="[^"]*"/, `datetime="${post.datumZeit}"`)
        .replace(/\[[^\]]*\]/, timeAgo(post.datumZeit))
        .replace(/<p>\[.*?\]<\/p>/, `<p>${post.text}</p>`);

    // Reactions
    const reactionsHtml = Object.entries(post.reactions)
        .map(([emoji, count]) =>
            `<button class="reaction-button no-post-details" type="button" data-emoji="${emoji}" aria-pressed="false">
         ${emoji} <span class="reaction-counter">${count}</span>
       </button>`
        ).join('');
    html = html.replace(/<div class="post-reactions">[\s\S]*?<\/div>/, `<div class="post-reactions">${reactionsHtml}</div>`);

    // Kommentare
    html = html.replace(/<i class="bi bi-chat-dots-fill"><\/i> \d+ Kommentare/, `<i class="bi bi-chat-dots-fill"></i> ${post.comments} Kommentare`);

    const li = document.createElement('li');
    li.classList.add('posts');
    li.dataset.postId = post.id;
    li.innerHTML = html;
    return li;
}

function loadFeed() {
    const feed = document.getElementById('feed');
    feed.innerHTML = '';
    posts.forEach(p => feed.append(renderPost(p)));
}

function setupPostCreation() {
    const input = document.getElementById('post-input');
    const btn = document.getElementById('post-button');
    btn.addEventListener('click', () => {
        const text = input.value.trim();
        if (!text) return;
        posts.unshift({
            id: Date.now(),
            nutzer_id: 1,
            author: "Du",
            avatar: "assets/placeholder-profilbild.jpg",
            datumZeit: new Date().toISOString(),
            text: text,
            reactions: { "👍":0, "👎":0, "❤️":0, "🤣":0, "❓":0, "‼️":0 },
            comments: 0
        });
        input.value = '';
        loadFeed();
    });
}

// Reaction-Toggle
const userReactions = new Map();
document.addEventListener('click', e => {
    const btn = e.target.closest('.reaction-button');
    if (!btn) return;

    const postId = Number(btn.closest('li').dataset.postId);
    const emoji = btn.dataset.emoji;
    const ctr = btn.querySelector('.reaction-counter');
    let reactedSet = userReactions.get(postId) || new Set();
    let count = parseInt(ctr.textContent, 10);

    if (reactedSet.has(emoji)) {
        // Bereits ausgewählt: zurücksetzen
        reactedSet.delete(emoji);
        btn.setAttribute('aria-pressed', 'false');
        btn.classList.remove('active');
        ctr.textContent = count - 1;
    } else {
        // Neu auswählen
        reactedSet.add(emoji);
        btn.setAttribute('aria-pressed', 'true');
        btn.classList.add('active');
        ctr.textContent = count + 1;
    }

    userReactions.set(postId, reactedSet);
});

// Löschen eines Posts im Feed
document.addEventListener('click', e => {
    const deleteBtn = e.target.closest('.post-options-button');
    if (!deleteBtn) return;

    const postEl = deleteBtn.closest('li.posts');
    if (!postEl) return;

    const postId = Number(postEl.dataset.postId);
    const confirmed = confirm("Möchtest du diesen Post wirklich löschen?");
    if (!confirmed) return;

    // Aus dem Array entfernen
    const index = posts.findIndex(p => p.id === postId);
    if (index !== -1) posts.splice(index, 1);

    // Neu rendern
    loadFeed();
});
