# JavaScript-Struktur

Diese Ordnerstruktur organisiert die JavaScript-Funktionalität der Anwendung sauber nach Verantwortlichkeiten.

## Refactoring-Übersicht

Die neueren JavaScript-Dateien wurden erstellt, um duplizierte Funktionalität aus den PHP-Dateien zu extrahieren und in wiederverwendbare Module zu organisieren:

- **Problem**: Duplizierte JavaScript-Funktionalität in mehreren PHP-Dateien
- **Lösung**: Auslagern in separate JS-Dateien mit konfigurierbaren Funktionen
- **Vorteil**: Bessere Wartbarkeit, weniger Duplikate, zentralisierte Funktionalität

## Ordnerstruktur

```
js/
├── ajax/                    # AJAX-Funktionalität
│   ├── utils.js            # Gemeinsame Hilfsfunktionen
│   ├── posts.js            # Post-Erstellung und -Verwaltung
│   ├── comments.js         # Kommentar-Funktionalität
│   └── reactions.js        # Reaktions-System
├── comment-utils.js        # Kommentar-Context-Handler
├── image-compression.js    # Bildkomprimierung
├── image-preview.js        # Bildvorschau und -komprimierung
├── navigation.js           # Post-Navigation
├── pagination.js           # "Mehr laden"-Funktionalität
├── search.js              # Suchfunktionalität
├── textarea-utils.js       # Textarea-Funktionalität
└── README.md              # Diese Datei
```

## Dateien

### ajax/utils.js
- **Zweck**: Gemeinsame AJAX-Hilfsfunktionen
- **Klasse**: `AjaxUtils`
- **Funktionen**: 
  - `setButtonLoading()` - Button-Loading-Status
  - `showFeedbackMessage()` - Feedback-Nachrichten
  - `compressImage()` - Bildkomprimierung
  - `updateCommentCount()` - Kommentar-Zähler
  - `updateCommentsHeading()` - Kommentar-Überschriften
  - `updateReplyCount()` - Antwort-Zähler

### ajax/posts.js
- **Zweck**: Post-Erstellung und -Verwaltung
- **Klasse**: `PostAjax`
- **Funktionen**:
  - `handlePostCreation()` - Post erstellen
  - `handlePostDeletion()` - Post löschen
  - `resetPostForm()` - Post-Formular zurücksetzen
  - `prependNewPost()` - Neuen Post einfügen

### ajax/comments.js
- **Zweck**: Kommentar-Funktionalität
- **Klasse**: `CommentAjax`
- **Funktionen**:
  - `handleCommentCreation()` - Kommentar erstellen
  - `handleCommentDeletion()` - Kommentar löschen
  - `insertNewComment()` - Neuen Kommentar einfügen
  - `removeCommentFromDOM()` - Kommentar aus DOM entfernen

### ajax/reactions.js
- **Zweck**: Reaktions-System (bereinigt, ohne Duplikate)
- **Klasse**: `ReactionAjax`
- **Funktionen**:
  - `handleReactionToggle()` - Reaktion umschalten
  - `updateReactionButtons()` - Reaktions-Buttons aktualisieren
  - `getReactionTypeFromEmoji()` - Emoji-Mapping

### image-compression.js
- **Zweck**: Bildkomprimierung für Uploads
- **Klasse**: `ImageCompressor`
- **Status**: Unverändert (bereits gut strukturiert)

### search.js
- **Zweck**: Gemeinsame Suchfunktionalität für Desktop und Mobile
- **Funktionen**:
  - `initializeSearch()` - Konfigurierbare Basis-Suchfunktion
  - `initializeDesktopSearch()` - Desktop-Header-Suchfunktion
  - `initializeMobileSearch()` - Mobile-Seiten-Suchfunktion

### pagination.js
- **Zweck**: Universelle "Mehr laden"-Funktionalität für Posts
- **Funktionen**:
  - `initializePagination()` - Konfigurierbare Pagination-Funktionalität
  - **Unterstützte Kontexte**: `all`, `followed`, `user`, `user_comments`, `hashtag`
- **Konfiguration**: Container-IDs, Button-IDs, Limit, Offset, Parameter

### comment-utils.js
- **Zweck**: Kommentar-bezogene Utility-Funktionen
- **Funktionen**:
  - `setupCommentContextHandlers()` - Verhindert Navigation bei Hashtag-Links in Kommentaren
  - `toggleReplyForm()` - Toggle-Funktionalität für Antwort-Formulare
  - `initializeCommentSystem()` - Vollständige Kommentar-System-Initialisierung
- **Zweck**: Verhindert unerwünschte Navigation bei interaktiven Elementen

### image-preview.js
- **Zweck**: Bildvorschau und -komprimierung für Formulare
- **Funktionen**:
  - `initializeImagePreview()` - Konfigurierbare Bildvorschau-Funktionalität
  - **Features**: Dateityp-Validierung, Komprimierung, Vorschau-Update
- **Konfiguration**: Input-IDs, Vorschau-Container, erlaubte Dateitypen

### textarea-utils.js
- **Zweck**: Textarea-Funktionalität (Zeichenzähler, Auto-Resize)
- **Funktionen**:
  - `initializeTextareaWithCounter()` - Textarea mit Zeichenzähler und Auto-Resize
  - **Features**: Automatische Höhenanpassung, Zeichenzähler, Warnungen
- **Konfiguration**: Textarea-ID, Counter-Selektor, Zeichenlimit

### navigation.js
- **Zweck**: Post-Navigation-Funktionalität
- **Funktionen**:
  - `navigateToPost()` - Navigation zu Post-Detail-Seite
  - `setupPostNavigationHandlers()` - Event-Handler für Post-Navigation
- **Zweck**: Verhindert Navigation bei interaktiven Elementen (Buttons, Links)
