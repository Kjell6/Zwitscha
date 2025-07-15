# JavaScript-Struktur

Diese Ordnerstruktur organisiert die JavaScript-Funktionalität der Anwendung sauber nach Verantwortlichkeiten.

## Ordnerstruktur

```
js/
├── ajax/                    # AJAX-Funktionalität
│   ├── utils.js            # Gemeinsame Hilfsfunktionen
│   ├── posts.js            # Post-Erstellung und -Verwaltung
│   ├── comments.js         # Kommentar-Funktionalität
│   └── reactions.js        # Reaktions-System
├── image-compression.js    # Bildkomprimierung
├── search.js              # Suchfunktionalität
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