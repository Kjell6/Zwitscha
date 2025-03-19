# Hallo Team TEAMDENOMTOREPLACE!

Willkommen in Ihrem Repo für VERANSTALTUNGTOREPLACE!

Projekt-URL: https://PROJECTURLTOREPLACE.th-luebeck.dev

Der Inhalt dieser Datei (`README.md`) wird in GitLab auf der Hauptseite Ihres Projektes angezeigt. Entsprechend ist hier ein guter Ort, ein paar grundlegende Angaben zu Ihrem Projekt zu machen, beispielsweise zu Besonderheiten Ihrer Ordnerstruktur und ähnliches. Diesen Absatz können Sie durch eine (kurze) Beschreibung Ihres Projektes ersetzen. Passen Sie bitte auch die Überschrift und die Mitgliederliste entsprechend an.

## Mitglieder

MITGLIEDERLISTTOREPLACE

## Ordnerstruktur

    |-public      Auf dem Webserver vorliegende Dateien.
    | |-assets    Unterordner für statische Dateien (Bilder, SVGs, etc.)
    | |-php       Unterordner für PHP-Dateien mit besonderen Funktionen
    | |-css       Unterordner für CSS-Dateien
    | |-js        Unterordner für JavaScript-Dateien
    |
    |-docs        Die ausgearbeitete Dokumentation und zugehörige Dateien.

## Build-Prozess und Docker

Sobald an dem main-Branch dieses Repositories Veränderungen vorgenommen bzw. gepusht werden, wird automatisch ein Build-Prozess angestoßen, welcher das [Deployment Ihres Projektes](https://PROJECTURLTOREPLACE.mylab.th-luebeck.de) aktualisiert. Die Dateien `Dockerfile` und `.gitlab-ci.yml` steuern diesen Build-Prozess, und sollten von Ihnen in der Regel nicht verändert werden!

Um das Deployment inklusive Datenbank und PhpMyAdmin bei Ihnen lokal so nachzubilden, wie es auf dem myLab-Webspace ausgeführt wird, können Sie `docker-compose` verwenden. Die Datei `docker-compose.yml` wurde für Sie entsprechend vorbereitet.

Weitergehende Informationen und Anleitungen zu Docker finden Sie z.B. [hier](https://gwprg.mylab.th-luebeck.de).
