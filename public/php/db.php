<?php
// public/php/Database.php

class db {
    /**
     * @var mysqli|null Die einzige Instanz der mysqli-Verbindung.
     */
    private static ?mysqli $instance = null;

    /**
     * Der Konstruktor ist privat, um eine direkte Instanziierung mit 'new' zu verhindern.
     */
    private function __construct() {}

    /**
     * Verhindert das Klonen der Instanz.
     */
    private function __clone() {}

    /**
     * Gibt die einzige Instanz der Datenbankverbindung zurück.
     * Wenn noch keine Instanz existiert, wird sie erstellt.
     *
     * @return mysqli Die mysqli-Verbindungsinstanz.
     */
    public static function getInstance(): mysqli {
        if (self::$instance === null) {
            $config = require(__DIR__ . '/config.php');

            // Prüfen, ob die Konfigurationswerte vorhanden sind
            if (empty($config['host']) || empty($config['user']) || empty($config['database'])) {
                die("Datenbank-Konfigurationsfehler: Nicht alle erforderlichen Werte sind gesetzt.");
            }

            // mysqli-Objekt erstellen
            self::$instance = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);

            // Verbindung prüfen
            if (self::$instance->connect_error) {
                die("Verbindung zur Datenbank fehlgeschlagen: " . self::$instance->connect_error);
            }

            // Zeichensatz auf utf8mb4 setzen für volle Unicode-Unterstützung
            self::$instance->set_charset("utf8mb4");
        }
        return self::$instance;
    }
} 