<?php
// Datenbankverbindung und Zeitzone konfigurieren
date_default_timezone_set('Europe/Berlin');

// Datenbankverbindungsparameter aus Umgebungsvariablen
return [
    'host' => getenv('MYSQL_SERVER'),
    'user' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'database' => getenv('MYSQL_DATABASE')
]; 