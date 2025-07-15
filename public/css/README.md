# CSS-Struktur

Dieses Verzeichnis enthält alle CSS-Dateien der Anwendung. Die Struktur folgt einem komponentenbasierten Ansatz, bei dem jede Datei für das Styling eines bestimmten Bereichs oder einer bestimmten Komponente der Benutzeroberfläche zuständig ist.

## Globale Stile

### `style.css`
Dies ist die zentrale Stylesheet-Datei. Sie enthält globale Stile, die für die gesamte Anwendung gelten, wie z.B.:
- CSS-Variablen (Custom Properties) für Farben, Schriftgrößen etc.
- grundlegende HTML-Element-Stile (Body, Links, Überschriften).
- allgemeine Layout-Klassen und Hilfsklassen.

## Seiten- und Komponentenspezifische Stile

Diese Dateien enthalten Stile, die nur für eine bestimmte Seite oder Komponente geladen werden, um die Ladezeiten zu optimieren und die Übersichtlichkeit zu wahren.

- `Login.css`: Stile für die Login- und Registrierungsformulare.
- `header.css`: Stile für den Haupt-Header der Desktop-Ansicht.
- `mobileFooter.css`: Stile für die Navigationsleiste in der mobilen Ansicht.
- `post.css`: Stile für die Darstellung von einzelnen Posts Kommentare in Listenansichten (z.B. im Feed) und teilweise Kommentaren.
- `postDetail.css`: Detaillierte Stile für die Einzelansicht eines Posts und seiner Kommentare.
- `profil.css`: Stile für die Benutzerprofilseiten.
- `einstellungen.css`: Stile für die Einstellungsseite.
- `followerList.css`: Stile für die Ansicht der Follower- und Following-Listen.
- `search.css`: Stile für die Suchseite und die Ergebnisdarstellung.
- `kommentarEinzeln.css`: Stile für die Darstellung eines einzelnen Kommentars.
- `index.css`: Spezifische Stile, die nur auf der Hauptseite (Index) benötigt werden. 