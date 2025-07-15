# PHP-Struktur

Diese Ordnerstruktur organisiert die Backend-Funktionalität der Zwitscha-Anwendung sauber nach Verantwortlichkeiten.

## Ordnerstruktur

```
php/
├── config.php              # Konfiguration & Umgebungsvariablen
├── db.php                  # Datenbankverbindung (Singleton)
├── helpers.php             # Gemeinsame Hilfsfunktionen
├── session_helper.php      # Session-Management & Authentifizierung
├── NutzerVerwaltung.php    # Nutzerverwaltung (Registrierung, Profile, etc.)
├── PostVerwaltung.php      # Post-Verwaltung (Posts, Kommentare, Reaktionen)
├── admin_handler.php       # Administrator-Aktionen
├── post_action_handler.php # Post-Aktionen (Erstellen, Löschen)
├── reaction_handler.php    # AJAX-Handler für Reaktionen
├── search_handler.php      # Suchfunktionalität
├── get-posts.php           # Endpoint für das dynamische Laden von Posts & Kommentaren
└── README.md               # Diese Datei
```

## Dateien

### Konfiguration & Basis

#### config.php
- **Zweck**: Zentrale Konfiguration der Anwendung.
- **Funktionen**:
  - Setzt die Standardzeitzone auf 'Europe/Berlin'.
  - Lädt Datenbankverbindungsparameter aus Umgebungsvariablen.
  - Gibt die Konfiguration als Array zurück.
- **Umgebungsvariablen**: `MYSQL_SERVER`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE`.

#### db.php
- **Zweck**: Stellt eine einzige Datenbankverbindung über das Singleton-Pattern bereit.
- **Klasse**: `db`
- **Methode**:
  - `getInstance()`: Gibt die eine `mysqli`-Instanz zurück und erstellt sie bei Bedarf.
- **Features**: Verhindert mehrfache Datenbankverbindungen, konfiguriert den Zeichensatz auf `utf8mb4` und die `time_zone`.

#### helpers.php
- **Zweck**: Anwendungsweite Hilfsfunktionen.
- **Funktionen**:
  - `time_ago()`: Formatiert ein Datum in eine benutzerfreundliche "Vor X Zeit"-Angabe (z.B. "vor 2 Stunden").
  - `getReactionEmojiMap()`: Liefert eine Map, die Datenbank-Reaktionstypen (z.B. "Daumen Hoch") auf Emojis (z.B. '👍') abbildet.
  - `linkify_content()`: Wandelt `@mentions` und `#hashtags` in einem Text in klickbare HTML-Links um.
- **Besonderheit**: Die Zeitangaben sind für die deutsche Sprache angepasst.

#### session_helper.php
- **Zweck**: Session-Management und Authentifizierung.
- **Funktionen**:
  - `ensureSessionStarted()`: Startet die Session und prüft auf ein "Angemeldet bleiben"-Cookie.
  - `isLoggedIn()`: Prüft, ob ein Nutzer angemeldet ist.
  - `getCurrentUserId()`: Holt die ID des aktuellen Nutzers aus der Session.
  - `getCurrentUsername()`: Holt den Nutzernamen des aktuellen Nutzers.
  - `isCurrentUserAdmin()`: Prüft, ob der aktuelle Nutzer Admin-Rechte hat.
  - `requireLogin()`: Erzwingt eine Anmeldung durch Weiterleitung zur Login-Seite.
  - `logout()`: Meldet den Nutzer ab, löscht Session und "Angemeldet bleiben"-Cookie.
- **Features**: Unterstützt eine "Angemeldet bleiben"-Funktion über Cookies.

### Verwaltungsklassen

#### NutzerVerwaltung.php
- **Zweck**: Umfassende Verwaltung aller nutzerbezogenen Datenbankoperationen.
- **Klasse**: `NutzerVerwaltung`
- **Hauptfunktionen**:
  - `registerUser()`: Registriert einen neuen Nutzer.
  - `authenticateUser()`: Überprüft Anmeldedaten und loggt einen Nutzer ein.
  - `getUserProfileData()`: Holt öffentliche Profildaten inklusive Statistiken (Follower, Posts etc.).
  - `getUserById()` / `getUserByUsername()`: Holt vollständige Nutzerdaten.
  - `updateUserName()`, `updatePassword()`, `updateProfileImage()`: Aktualisiert einzelne Profil-Aspekte.
  - `deleteUser()`: Löscht einen Nutzeraccount.
  - `toggleFollow()`: Schaltet den "Folgen"-Status zwischen zwei Nutzern um.
  - `getFollowers()` / `getFollowing()`: Ruft die Follower-Listen ab.
  - `searchUsers()`: Sucht nach Nutzern.
  - `setAdminStatus()`: Ändert den Admin-Status eines Nutzers.
  - `createRememberToken()`, `consumeRememberToken()`, `deleteRememberToken()`: Verwalten die Tokens für die "Angemeldet bleiben"-Funktion.

#### PostVerwaltung.php
- **Zweck**: Verwaltung von Posts, Kommentaren und Reaktionen.
- **Klasse**: `PostVerwaltung`
- **Hauptfunktionen**:
  - **Posts**: `createPost()`, `deletePost()`, `getPostById()`.
  - **Post-Listen**: `getAllPosts()`, `getFollowedPosts()`, `getPostsByUserId()`, `getPostsByHashtag()`.
  - **Kommentare & Antworten**: `createComment()`, `deleteComment()`, `getCommentById()`, `getCommentsByPostId()`, `getRepliesByParentCommentId()`.
  - **Kommentar-Listen**: `getCommentsByUserId()`, `getCommentsByHashtag()`.
  - **Reaktionen**: `toggleReaction()` zum Hinzufügen/Entfernen einer Reaktion auf einen Post.
- **Features**: Bietet den kompletten Lebenszyklus für Posts und Kommentare, ein Reaktionssystem und Hashtag-Unterstützung.

### Handler (Aktions-Skripte)

#### admin_handler.php
- **Zweck**: Verarbeitet Administrator-spezifische Aktionen.
- **Aktionen**:
  - `toggle_admin`: Schaltet den Admin-Status für einen Zielnutzer um.
- **Sicherheit**: Nur via POST, prüft auf eingeloggten Admin.

#### post_action_handler.php
- **Zweck**: Zentraler Handler für Aktionen rund um Posts und Kommentare.
- **Aktionen**:
  - `create_post`, `delete_post`
  - `create_comment`, `delete_comment`
  - `create_reply`, `delete_reply` (Antworten auf Kommentare)
- **Features**: AJAX-Unterstützung mit JSON-Responses, Handling von Bilduploads.
- **Sicherheit**: Prüft Login-Status und Berechtigungen (z.B. Eigentümer oder Admin beim Löschen).

#### reaction_handler.php
- **Zweck**: AJAX-Handler für Post-Reaktionen.
- **Funktionen**:
  - Schaltet eine Reaktion für einen Post um (`toggleReaction`).
  - Validiert das übergebene Emoji.
  - Gibt die aktualisierten Reaktionszähler als JSON zurück.
- **Erlaubte Emojis**: 👍, 👎, ❤️, 🤣, ❓, ‼️
- **Features**: Reine JSON-API für Echtzeit-Updates im Frontend.

#### search_handler.php
- **Zweck**: Stellt Suchfunktionalität für Nutzer bereit.
- **Funktionen**:
  - Verarbeitet eine Suchanfrage (`query`).
  - Gibt eine JSON-Liste von bis zu 8 passenden Nutzern zurück.
- **Features**: JSON-API, die für Autocomplete-Suchen im Frontend optimiert ist (inkl. Profilbild-URL).

#### get-posts.php
- **Zweck**: Dynamisches Laden von Inhalten via AJAX
- **Gibt HTML zurück**, kein JSON.
- **Unterstützte `context`-Parameter**:
  - `all`: Alle Posts.
  - `followed`: Posts von gefolgten Nutzern.
  - `user`: Posts eines bestimmten Nutzers.
  - `user_comments`: Kommentare eines bestimmten Nutzers.
  - `hashtag`: Ein gemischter Feed aus Posts und Kommentaren mit einem bestimmten Hashtag, nach Datum sortiert.
- **Weitere Parameter**: `offset`, `limit`, `userId`, `tag`.
