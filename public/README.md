# Web-Root (`public`)

Dieses Verzeichnis ist das Hauptverzeichnis der Web-Anwendung und enthält alle öffentlich zugänglichen Dateien, Skripte und Ressourcen.

## Verzeichnisstruktur

- **`assets/`**: Enthält alle statischen Medien.
  - Bilddateien wie Logos, Favicons und Platzhalter.
  - `custom_icons/`: Eigene SVG-Icons für die Benutzeroberfläche.

- **`css/`**: Beinhaltet alle CSS-Dateien für das Styling der Anwendung.
- **`js/`**: Enthält die clientseitige JavaScript-Logik.
- **`php/`**: Enthält die serverseitige PHP-Logik.

## Hauptdateien (PHP)

Die folgenden PHP-Dateien im Stammverzeichnis (`public/`) stellen die Hauptansichten und -seiten der Anwendung dar. Sie sind in der Regel für das Rendern der Benutzeroberfläche zuständig und binden die notwendigen CSS-, JS- und PHP-Module ein.

- **`index.php`**: Die Startseite, die den Haupt-Feed anzeigt.
- **`Login.php` / `Register.php`**: Seiten für die Benutzeranmeldung und -registrierung.
- **`Profil.php`**: Zeigt die Profilseite eines Benutzers an.
- **`postDetails.php`**: Detaillierte Ansicht eines einzelnen Posts mit Kommentaren.
- **`einstellungen.php`**: Seite für die Benutzereinstellungen.
- **`hashtag.php`**: Zeigt einen Feed von Posts an, die einen bestimmten Hashtag enthalten.
- **`followerList.php`**: Zeigt die Liste der Follower oder der gefolgten Benutzer an.
- **`logout.php`**: Skript zur Abmeldung des Benutzers.
- **`MobileSearch.php`**: Stellt die Suchoberfläche für mobile Endgeräte bereit.
- **`getImage.php`**: Dient zur sicheren Auslieferung von Bildern (z.B. Profilbildern).

## Wiederverwendbare Komponenten (PHP)
Diese PHP-Dateien sind keine eigenständigen Seiten, sondern werden in die Hauptdateien eingebunden, um wiederkehrende UI-Elemente darzustellen.
- **`headerDesktop.php`**: Der Header für die Desktop-Ansicht.
- **`footerMobile.php`**: Die Navigationsleiste für die mobile Ansicht.
- **`post.php`**: Template für die Darstellung eines einzelnen Posts.
- **`kommentar.php` / `kommentarEinzeln.php`**: Templates für die Darstellung von Kommentaren.
- **`lightbox.php`**: Stellt eine Lightbox zur vergrößerten Anzeige von Bildern bereit.
