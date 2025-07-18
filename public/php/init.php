<?php
// Zentrales Initialisierungs-Skript

// Fehler-Reporting für die Entwicklung (kann in der Produktion deaktiviert werden)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Composer Autoloader laden
require_once __DIR__ . '/../vendor/autoload.php';

// Session sicher starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Alle notwendigen Klassen und Helfer laden
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/PostVerwaltung.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php'; 