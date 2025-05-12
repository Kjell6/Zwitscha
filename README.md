# Hallo Team Subway Surfers!

Willkommen in Ihrem Repo für Zwitscha!

Projekt-URL: https://team-25-19.gwprg.mylab.th-luebeck.dev


## Mitglieder
Julian, Kjell, Ferdi

## Ordnerstruktur

    |-public      Auf dem Webserver vorliegende Dateien.
    | |-assets    Unterordner für statische Dateien (Bilder, SVGs, etc.)
    | |-php       Unterordner für PHP-Dateien mit besonderen Funktionen
    | |-css       Unterordner für CSS-Dateien
    | |-js        Unterordner für JavaScript-Dateien
    |
    |-docs        Die ausgearbeitete Dokumentation und zugehörige Dateien.

## Build-Prozess und Docker

Sobald an dem main-Branch dieses Repositories Veränderungen vorgenommen bzw. gepusht werden, wird automatisch ein Build-Prozess angestoßen, welcher das [Deployment Ihres Projektes](https://PROJECTURLTOREPLACE.th-luebeck.dev) aktualisiert. Die Dateien `Dockerfile` und `.gitlab-ci.yml` steuern diesen Build-Prozess, und sollten von Ihnen in der Regel nicht verändert werden!

Um das Deployment inklusive Datenbank und PhpMyAdmin bei Ihnen lokal so nachzubilden, wie es auf dem myLab-Webspace ausgeführt wird, können Sie `docker-compose` verwenden. Die Datei `docker-compose.yml` wurde für Sie entsprechend vorbereitet.
