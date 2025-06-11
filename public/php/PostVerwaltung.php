<?php
// public/php/PostVerwaltung.php

require_once __DIR__ . '/db.php';

class PostVerwaltung {
    private mysqli $db;

    public function __construct() {
        $this->db = db::getInstance();
    }

    /**
     * Holt alle Posts aus der Datenbank, inklusive Autor-Informationen.
     *
     * @return array Ein Array von assoziativen Arrays, die die Posts repräsentieren.
     */
    public function getAllPosts(): array {
        $sql = "
            SELECT 
                p.id,
                p.text,
                p.bildPfad,
                p.datumZeit,
                n.nutzerName AS autor,
                n.profilBild,
                n.id as userId
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            ORDER BY p.datumZeit DESC
        ";
        
        $result = $this->db->query($sql);

        if (!$result) {
            // Im Fehlerfall ein leeres Array zurückgeben.
            // Optional: error_log($this->db->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Erstellt einen neuen Post in der Datenbank.
     *
     * @param int $userId Die ID des Nutzers, der den Post erstellt.
     * @param string $text Der Inhalt des Posts.
     * @param string|null $imagePath Der Pfad zum hochgeladenen Bild (optional).
     * @return bool True bei Erfolg, false bei einem Fehler.
     */
    public function createPost(int $userId, string $text, ?string $imagePath): bool {
        $sql = "INSERT INTO post (nutzer_id, text, bildPfad) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            // Optional: error_log($this->db->error);
            return false;
        }

        // 'iss' steht für integer, string, string
        $stmt->bind_param("iss", $userId, $text, $imagePath);
        
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
} 