// Einfaches @-Erwähnungs-Autocomplete für Textareas mit der Klasse `mentionable`
// Mobile-first ausgelegt: Vorschläge werden unterhalb der Textarea angezeigt und sind touch-freundlich.
(function(){
    const SEARCH_URL = 'php/search_handler.php';
    const DEBOUNCE_MS = 200;

    function createSuggestionBox() {
        const box = document.createElement('div');
        box.className = 'mention-suggestions hidden';
        box.setAttribute('role', 'listbox');
        document.body.appendChild(box);
        return box;
    }

    function positionBox(box, textarea) {
        const rect = textarea.getBoundingClientRect();
        // Positioniere unterhalb der Textarea; bei wenig Platz oben platzieren
        const top = rect.bottom + window.scrollY + 6;
        const left = rect.left + window.scrollX;
        box.style.left = left + 'px';
        box.style.minWidth = (rect.width) + 'px';
        box.style.top = top + 'px';
    }

    function debounce(fn, ms) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    }

    async function fetchUsers(query) {
        try {
            const form = new FormData();
            form.append('query', query);
            const res = await fetch(SEARCH_URL, { method: 'POST', body: form });
            if (!res.ok) return [];
            return await res.json();
        } catch (e) {
            return [];
        }
    }


    function renderSuggestions(box, items, onSelect) {
        box.innerHTML = '';
        if (!items || items.length === 0) {
            box.classList.add('hidden');
            return;
        }

        items.forEach((it, idx) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'mention-suggestion-item';
            row.setAttribute('data-index', idx);
            row.innerHTML = `
                <img src="${it.avatar}" class="mention-suggestion-avatar" alt="" loading="lazy">
                <span class="mention-suggestion-name">${escapeHtml(it.name)}</span>
            `;
            row.addEventListener('click', () => onSelect(it));
            box.appendChild(row);
        });

        box.classList.remove('hidden');
    }

    function escapeHtml(s){
        return String(s).replace(/[&<>\"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    function findMentionToken(value, caretPos) {
        // Finde das letzte @ vor caretPos ohne Leerzeichen dazwischen
        const upto = value.slice(0, caretPos);
        const match = upto.match(/(^|\s)@([a-zA-Z0-9._-]{0,30})$/);
        if (match) {
            return {prefix: match[1] || '', token: match[2], start: caretPos - match[2].length - 1};
        }
        return null;
    }

    function replaceToken(textarea, tokenInfo, username) {
        const val = textarea.value;
        const before = val.slice(0, tokenInfo.start);
        const after = val.slice(textarea.selectionStart);
        const insert = '@' + username + ' ';
        const newPos = (before + insert).length;
        textarea.value = before + insert + after;
        textarea.focus();
        textarea.setSelectionRange(newPos, newPos);
        // Trigger input event so counters/resize update
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function attachToTextarea(textarea, box) {
        let activeIndex = -1;
        let currentItems = [];

        const doSearch = debounce(async () => {
            const caret = textarea.selectionStart;
            const token = findMentionToken(textarea.value, caret);
            if (!token || token.token.length === 0) {
                box.classList.add('hidden');
                return;
            }
            const items = await fetchUsers(token.token);
            currentItems = items;
            activeIndex = -1;
            renderSuggestions(box, items, (it) => {
                replaceToken(textarea, token, it.name);
                box.classList.add('hidden');
            });
            positionBox(box, textarea);
        }, DEBOUNCE_MS);

        textarea.addEventListener('input', doSearch);
        textarea.addEventListener('blur', () => setTimeout(() => box.classList.add('hidden'), 150));

        textarea.addEventListener('keydown', (ev) => {
            if (box.classList.contains('hidden')) return;
            const items = Array.from(box.querySelectorAll('.mention-suggestion-item'));
            if (ev.key === 'ArrowDown') {
                ev.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach(i => i.classList.remove('active'));
                if (items[activeIndex]) items[activeIndex].classList.add('active');
            } else if (ev.key === 'ArrowUp') {
                ev.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach(i => i.classList.remove('active'));
                if (items[activeIndex]) items[activeIndex].classList.add('active');
            } else if (ev.key === 'Enter') {
                if (activeIndex >= 0 && currentItems[activeIndex]) {
                    ev.preventDefault();
                    const caret = textarea.selectionStart;
                    const token = findMentionToken(textarea.value, caret);
                    if (token) {
                        replaceToken(textarea, token, currentItems[activeIndex].name);
                        box.classList.add('hidden');
                    }
                }
            } else if (ev.key === 'Escape') {
                box.classList.add('hidden');
            }
        });
    }

    // Initialisierung: finde alle Textareas mit Klasse mentionable
    document.addEventListener('DOMContentLoaded', () => {
        const textareas = document.querySelectorAll('textarea.mentionable');
        if (!textareas.length) return;
        const box = createSuggestionBox();
        textareas.forEach(t => attachToTextarea(t, box));
    });
})();


