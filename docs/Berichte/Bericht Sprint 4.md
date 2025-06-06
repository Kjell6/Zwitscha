# Bericht Sprint 3

## Kjell

### 1. Erzielte Ergebnisse
- Dynamische Inhalte bei Posts, Kommentare, PostDetails, Profil und dem Feed auf der Startseite
- Post Request bei den Buttons zum Kommentieren, Reagieren und löschen von Posts und Kommentaren
- Post Request für das erstellen von Posts und Kommentaren
- Post Request beim Wechseln der Feeds (alle, gefolgt)

### 2. Hürden
- Die Arbeit mit PHP war bisher das komplizierteste. Ich konnte mir zu beginn nicht vorstellen, wie man es umsetzen sollte, damit später eine Datenbank implementiert werden kann. 

### 3. Überraschungen oder unerwartete Schwierigkeiten
- 

## Ferdi

### 1. Erzielte Ergebnisse



### 2. Hürden


### 3. Überraschungen oder unerwartete Schwierigkeiten



## Julian

### 1. Erzielte Ergebnisse

- Einstellungsseite angepasst: Layout "Profilbild ändern" angepasst und Formular mit Accountlöschung hinzugefügt
- POST Request Einstellungsseite: Website reagiert auf abgeschickte Eingaben mit Bestätigungsnachricht der Eingabe (Bspw: "Name würde zu Klaus geändert werden). Auf dieser Seite gibt es Requests für: Profilbildänderung, Namensänderung, Passwortänderung, Kontolöschung
- POST Request Profil: Beim Profil habe ich die POST-Request für den Folgen-/Entfolgenbutton entwickelt. Wenn man dort nun klickt, wechselt der Button auf entweder gefolgt oder folgen. Weiterhin wird eine Bestätigungsnachricht angezeigt für diese Aktion.
- POST Request Login: Login funktioniert nur noch mit Daten aus einer Dummy-Nutzerliste, ansonsten wird eine Fehlermeldung ausgegeben
- POST Request Registration: An sich selbes Spiel wie beim Login, nur dass dieses Mal die Fehelermeldung nur kommt, wenn man sich mit bereits existierenden Nutzerdaten anmeldet. Wenn man Daten nutzt, die noch nicht verwendet wurden, erscheint zur Überprüfung eine kurze Bestätigung, bevor man nach drei Sekunden zur Startseite weitergeleitet wird.

### 2. Hürden

Mir war zuerst nicht ganz genau klar, wie ich an diese POST-Requests rangehen soll, bzw. was verlangt wird von mir, aber nach Rücksprache mit meiner Gruppe, wurde es mir dann klarer. Auch der Umgang mit php selbst ist noch recht schwerfällig, aber es wird mit jedem Mal besser.

### 3. Überraschungen oder unerwartete Schwierigkeiten
Als ich dann einen Plan davon hatte, was von mir verlangt wird, hat es Spaß gemacht, die POST-Requests zu implementieren. Schwierigkeiten siehe Punkt 2.


