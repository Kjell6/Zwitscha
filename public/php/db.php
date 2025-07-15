<?php
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

            // Konfigurationswerte prüfen
            if (empty($config['host']) || empty($config['user']) || empty($config['database'])) {
                die("Datenbank-Konfigurationsfehler: Nicht alle erforderlichen Werte sind gesetzt.");
            }

            // MySQLi-Verbindung erstellen
            self::$instance = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);

            // Verbindung prüfen
            if (self::$instance->connect_error) {
                error_log("Datenbankverbindung fehlgeschlagen: " . self::$instance->connect_error);
                die("Datenbankfehler. Bitte versuchen Sie es später erneut.");
            }

            // UTF8-Zeichensatz und Zeitzone setzen
            self::$instance->set_charset("utf8mb4");
            self::$instance->query("SET time_zone = 'Europe/Berlin'");
        }
        return self::$instance;
    }
} 