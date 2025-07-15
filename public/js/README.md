# JavaScript Dokumentation

Diese Dokumentation beschreibt die clientseitige JavaScript-Logik der Anwendung. Die Skripte sind modular aufgebaut und nach ihrer Zuständigkeit in `js/` und `js/ajax/` unterteilt.

## Ordnerstruktur

```
js/
├── ajax/
│   ├── utils.js            # AJAX-Hilfsfunktionen
│   ├── posts.js            # AJAX für Posts (Erstellen, Löschen)
│   ├── comments.js         # AJAX für Kommentare (Erstellen, Löschen)
│   └── reactions.js        # AJAX für Reaktionen
├── comment-utils.js        # Hilfsfunktionen für die Kommentar-Sektion
├── image-compression.js    # Clientseitige Bildkomprimierung
├── image-preview.js        # Bildvorschau für Formulare
├── navigation.js           # Navigation für Posts
├── pagination.js           # "Mehr laden"-Funktionalität
├── search.js               # Globale Suchfunktionalität
├── textarea-utils.js       # Hilfsfunktionen für Textareas
└── README.md               # Diese Datei
```

---

## AJAX-Module (`ajax/`)

### `ajax/utils.js`
Stellt eine `AjaxUtils`-Klasse mit statischen Hilfsmethoden bereit, die von anderen AJAX-Modulen verwendet werden.

- **Klasse**: `AjaxUtils`
- **Funktionen**:
  - `setButtonLoading(button, text, isLoading)`: Setzt den Ladezustand eines Buttons.
  - `showFeedbackMessage(message, type)`: Zeigt eine globale Feedback-Nachricht an.
  - `compressImage(file)`: Komprimiert ein Bild vor dem Upload.
  - `updateCommentCount(delta)`: Aktualisiert den Kommentar-Zähler eines Posts.
  - `updateCommentsHeading(delta)`: Aktualisiert die Überschrift der Kommentar-Sektion.
  - `updateReplyCount(commentId, delta)`: Aktualisiert den Antwort-Zähler eines Kommentars.

### `ajax/posts.js`
Verwaltet das Erstellen und Löschen von Posts via AJAX.

- **Klasse**: `PostAjax`
- **Hauptfunktionen**:
  - `handlePostCreation(form)`: Verarbeitet die Erstellung eines neuen Posts.
  - `handlePostDeletion(form)`: Verarbeitet das Löschen eines Posts.
  - `resetPostForm(form)`: Setzt das Post-Formular zurück.
  - `prependNewPost(postHtml)`: Fügt den neuen Post-HTML-Code am Anfang der Liste ein.

### `ajax/comments.js`
Verwaltet das Erstellen und Löschen von Kommentaren und Antworten via AJAX.

- **Klasse**: `CommentAjax`
- **Hauptfunktionen**:
  - `handleCommentCreation(form)`: Verarbeitet die Erstellung eines Kommentars oder einer Antwort.
  - `handleCommentDeletion(form)`: Verarbeitet das Löschen eines Kommentars.
  - `insertNewComment(commentHtml, form)`: Fügt einen neuen Kommentar in den DOM ein.
  - `removeCommentFromDOM(form)`: Entfernt einen Kommentar aus dem DOM.

### `ajax/reactions.js`
Verwaltet das Umschalten von Reaktionen auf Posts und Kommentare.

- **Klasse**: `ReactionAjax`
- **Hauptfunktionen**:
  - `handleReactionToggle(form)`: Verarbeitet das Hinzufügen/Entfernen einer Reaktion.
  - `updateReactionButtons(postId, reactions, currentUserReactions)`: Aktualisiert die UI der Reaktions-Buttons.
  - `getReactionTypeFromEmoji(emoji)`: Konvertiert ein Emoji-Symbol in den entsprechenden Reaktionstyp.

---

## Utility-Module (`js/`)

### `comment-utils.js`
Enthält globale Hilfsfunktionen zur Verwaltung der Kommentar-Interaktionen.

- **Funktionen**:
  - `setupCommentContextHandlers()`: Verhindert, dass Klicks auf Links in Kommentaren zur Post-Detailseite navigieren.
  - `toggleReplyForm(commentId)`: Zeigt das Antwortformular für einen Kommentar an oder verbirgt es.
  - `restoreReplyFormsState()`: Stellt den Zustand der geöffneten Antwortformulare aus der `sessionStorage` wieder her.
  - `initializeCommentSystem()`: Initialisiert alle Kommentar-Funktionen auf einer Seite.
  - `setupCommentHandlersForNewContent()`: Richtet Event-Handler für dynamisch nachgeladene Kommentare ein.

### `image-compression.js`
Stellt eine globale `ImageCompressor`-Instanz zur Verfügung, um Bilder clientseitig zu komprimieren.

- **Klasse**: `ImageCompressor`
- **Hauptfunktionen**:
  - `compressImage(file)`: Komprimiert eine einzelne Bilddatei.
  - `handleFileInput(fileInput, previewElement, callback)`: Komprimiert das Bild aus einem File-Input und zeigt eine Vorschau an.

### `image-preview.js`
Bietet Funktionen zur Initialisierung von Bild-Vorschauen in Formularen, inklusive Validierung und Komprimierung.

- **Funktionen**:
  - `initializeImagePreview(config)`: Eine hochgradig konfigurierbare Funktion zur Initialisierung einer Bildvorschau.
  - `initializeSimpleImagePreview(...)`: Eine vereinfachte Wrapper-Funktion für Standard-Anwendungsfälle.
  - `initializeAvatarImagePreview(...)`: Eine spezialisierte Funktion für die Vorschau von Avataren (z.B. im Profil).

### `navigation.js`
Verwaltet die Klick-Navigation auf Posts, um zur Detailansicht zu gelangen.

- **Funktionen**:
  - `navigateToPost(event, postId)`: Navigiert zur Post-Detailseite, ignoriert dabei Klicks auf interaktive Elemente.
  - `setupPostNavigationHandlers()`: Richtet die Klick-Listener für alle Posts auf der Seite ein.
  - `initializeNavigation()`: Initialisiert das Navigationssystem.

### `pagination.js`
Stellt eine universelle "Mehr laden"-Funktionalität für verschiedene Inhaltstypen bereit.

- **Funktionen**:
  - `initializePagination(config)`: Initialisiert die Paginierung mit einer detaillierten Konfiguration.
  - `initializeSimplePagination(...)`: Eine vereinfachte Wrapper-Funktion für Standard-Paginierungen.
  - **Unterstützte Kontexte**: `all`, `followed`, `user`, `user_comments`, `hashtag`.

### `search.js`
Stellt eine wiederverwendbare Suchfunktionalität bereit.

- **Funktionen**:
  - `initializeSearch(config)`: Eine konfigurierbare Basisfunktion für die Live-Suche.
  - `initializeDesktopSearch()`: Initialisiert die Suche im Desktop-Header.
  - `initializeMobileSearch()`: Initialisiert die Suche auf der mobilen Suchseite.

### `textarea-utils.js`
Enthält Hilfsfunktionen zur Verbesserung von `textarea`-Elementen.

- **Funktionen**:
  - `initializeTextareaWithCounter(config)`: Fügt einer Textarea einen Zeichenzähler und eine automatische Höhenanpassung hinzu.
  - `initializeSimpleTextarea(...)`: Eine vereinfachte Wrapper-Funktion für `initializeTextareaWithCounter`.
  - `initializeAutoResizeTextarea(textareaId)`: Aktiviert nur die automatische Höhenanpassung für eine Textarea.
