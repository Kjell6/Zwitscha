<?php
// public/php/config.php

// Setzt die Standardzeitzone für die gesamte Anwendung auf Mitteleuropäische Zeit.
// Dies stellt sicher, dass alle Zeitstempel korrekt umgerechnet werden.
date_default_timezone_set('Europe/Berlin');

return [
    'host' => getenv('MYSQL_SERVER'),
    'user' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'database' => getenv('MYSQL_DATABASE')
]; 