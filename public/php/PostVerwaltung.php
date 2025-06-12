<?php

require_once __DIR__ . '/db.php';

class PostVerwaltung {
    private mysqli $db;

    public function __construct() {
        $this->db = db::getInstance();
    }

    /**
     * Holt alle Posts aus der Datenbank, inklusive Autor-Informationen.
     *
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers.
     * @return array Ein Array von assoziativen Arrays, die die Posts repräsentieren.
     */
    public function getAllPosts(int $currentUserId): array {
        $sql = "
            SELECT 
                p.id, p.text, p.bildPfad, p.datumZeit,
                n.nutzerName AS autor, n.profilBild, n.id as userId,
                COUNT(DISTINCT k.id) AS comments,
                SUM(CASE WHEN r.reaktionsTyp = 'Daumen Hoch' THEN 1 ELSE 0 END) AS count_like,
                SUM(CASE WHEN r.reaktionsTyp = 'Daumen Runter' THEN 1 ELSE 0 END) AS count_dislike,
                SUM(CASE WHEN r.reaktionsTyp = 'Herz' THEN 1 ELSE 0 END) AS count_heart,
                SUM(CASE WHEN r.reaktionsTyp = 'Lachen' THEN 1 ELSE 0 END) AS count_laugh,
                SUM(CASE WHEN r.reaktionsTyp = 'Fragezeichen' THEN 1 ELSE 0 END) AS count_question,
                SUM(CASE WHEN r.reaktionsTyp = 'Ausrufezeichen' THEN 1 ELSE 0 END) AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            LEFT JOIN kommentar k ON p.id = k.post_id
            LEFT JOIN Reaktion r ON p.id = r.post_id
            GROUP BY p.id
            ORDER BY p.datumZeit DESC
        ";
        
        return $this->_fetchAndProcessPosts($sql, [$currentUserId], 'i');
    }

    /**
     * Holt nur die Posts von Nutzern, denen der aktuelle Nutzer folgt.
     *
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers.
     * @return array Ein Array von Posts.
     */
    public function getFollowedPosts(int $currentUserId): array {
        // Diese Abfrage ist fast identisch mit getAllPosts, hat aber einen zusätzlichen
        // INNER JOIN auf die 'folge'-Tabelle, um nur relevante Posts zu filtern.
        $sql = "
            SELECT 
                p.id, p.text, p.bildPfad, p.datumZeit,
                n.nutzerName AS autor, n.profilBild, n.id as userId,
                COUNT(DISTINCT k.id) AS comments,
                SUM(CASE WHEN r.reaktionsTyp = 'Daumen Hoch' THEN 1 ELSE 0 END) AS count_like,
                SUM(CASE WHEN r.reaktionsTyp = 'Daumen Runter' THEN 1 ELSE 0 END) AS count_dislike,
                SUM(CASE WHEN r.reaktionsTyp = 'Herz' THEN 1 ELSE 0 END) AS count_heart,
                SUM(CASE WHEN r.reaktionsTyp = 'Lachen' THEN 1 ELSE 0 END) AS count_laugh,
                SUM(CASE WHEN r.reaktionsTyp = 'Fragezeichen' THEN 1 ELSE 0 END) AS count_question,
                SUM(CASE WHEN r.reaktionsTyp = 'Ausrufezeichen' THEN 1 ELSE 0 END) AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            INNER JOIN folge f ON p.nutzer_id = f.gefolgter_id AND f.folgender_id = ?
            LEFT JOIN kommentar k ON p.id = k.post_id
            LEFT JOIN Reaktion r ON p.id = r.post_id
            GROUP BY p.id
            ORDER BY p.datumZeit DESC
        ";
        
        return $this->_fetchAndProcessPosts($sql, [$currentUserId, $currentUserId], 'ii');
    }
    
    /**
     * Schaltet eine spezifische Reaktion für einen Post an oder aus.
     *
     * @param int $userId Die ID des Nutzers.
     * @param int $postId Die ID des Posts.
     * @param string $emoji Das Emoji der Reaktion.
     * @return bool True bei Erfolg.
     */
    public function toggleReaction(int $userId, int $postId, string $emoji): bool {
        $reactionMap = [
            '👍' => 'Daumen Hoch', '👎' => 'Daumen Runter', '❤️' => 'Herz',
            '🤣' => 'Lachen', '❓' => 'Fragezeichen', '‼️' => 'Ausrufezeichen',
        ];

        if (!isset($reactionMap[$emoji])) {
            return false;
        }
        $reactionType = $reactionMap[$emoji];

        // 1. Prüfen, ob genau diese Reaktion bereits existiert.
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM Reaktion WHERE nutzer_id = ? AND post_id = ? AND reaktionsTyp = ?");
        $stmt->bind_param("iis", $userId, $postId, $reactionType);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            // 2. Reaktion existiert, also entfernen.
            $stmt = $this->db->prepare("DELETE FROM Reaktion WHERE nutzer_id = ? AND post_id = ? AND reaktionsTyp = ?");
            $stmt->bind_param("iis", $userId, $postId, $reactionType);
            $stmt->execute();
            $stmt->close();
        } else {
            // 3. Reaktion existiert nicht, also hinzufügen.
            $stmt = $this->db->prepare("INSERT INTO Reaktion (nutzer_id, post_id, reaktionsTyp) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $userId, $postId, $reactionType);
            $stmt->execute();
            $stmt->close();
        }

        return true;
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

    /**
     * Löscht einen Post aus der Datenbank.
     *
     * @param int $postId Die ID des zu löschenden Posts.
     * @return bool True bei Erfolg, false bei einem Fehler.
     */
    public function deletePost(int $postId): bool {
        $sql = "DELETE FROM post WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        // 'i' steht für integer
        $stmt->bind_param("i", $postId);
        
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Findet einen einzelnen Post anhand seiner ID.
     *
     * @param int $postId Die ID des gesuchten Posts.
     * @return array|null Die Post-Daten als assoziatives Array oder null, wenn nicht gefunden.
     */
    public function findPostById(int $postId): ?array {
        $sql = "SELECT * FROM post WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();

        return $post ?: null;
    }

    /**
     * Holt einen einzelnen Post anhand seiner ID, inklusive aller zugehörigen Informationen.
     *
     * @param int $postId Die ID des gesuchten Posts.
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers.
     * @return array|null Die Post-Daten oder null, wenn nicht gefunden.
     */
    public function getPostById(int $postId, int $currentUserId): ?array {
        $sql = "
            SELECT 
                p.id, p.text, p.bildPfad, p.datumZeit,
                n.nutzerName AS autor, n.profilBild, n.id as userId,
                COUNT(DISTINCT k.id) AS comments,
                SUM(CASE WHEN r.reaktionsTyp = 'Daumen Hoch' THEN 1 ELSE 0 END) AS count_like,
                SUM(CASE WHEN r.reaktionsTyp = 'Daumen Runter' THEN 1 ELSE 0 END) AS count_dislike,
                SUM(CASE WHEN r.reaktionsTyp = 'Herz' THEN 1 ELSE 0 END) AS count_heart,
                SUM(CASE WHEN r.reaktionsTyp = 'Lachen' THEN 1 ELSE 0 END) AS count_laugh,
                SUM(CASE WHEN r.reaktionsTyp = 'Fragezeichen' THEN 1 ELSE 0 END) AS count_question,
                SUM(CASE WHEN r.reaktionsTyp = 'Ausrufezeichen' THEN 1 ELSE 0 END) AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            LEFT JOIN kommentar k ON p.id = k.post_id
            LEFT JOIN Reaktion r ON p.id = r.post_id
            WHERE p.id = ?
            GROUP BY p.id
        ";
        
        $posts = $this->_fetchAndProcessPosts($sql, [$currentUserId, $postId], 'ii');

        return $posts[0] ?? null;
    }

    /**
     * Private Hilfsfunktion zum Ausführen und Verarbeiten von Post-Abfragen.
     *
     * @param string $sql Die SQL-Abfrage mit Platzhaltern.
     * @param array $params Die Parameter für die Abfrage.
     * @param string $types Die Typen-Deklaration für bind_param (z.B. 'i', 'ii').
     * @return array Das verarbeitete Array von Posts.
     */
    private function _fetchAndProcessPosts(string $sql, array $params, string $types): array {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        // Binden der Parameter an die Abfrage
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Gleiche Nachbearbeitung für alle Post-Abfragen
        return array_map(function($post) {
            $post['reactions'] = [
                '👍' => (int) $post['count_like'],
                '👎' => (int) $post['count_dislike'],
                '❤️' => (int) $post['count_heart'],
                '🤣' => (int) $post['count_laugh'],
                '❓' => (int) $post['count_question'],
                '‼️' => (int) $post['count_exclamation'],
            ];
            $post['currentUserReactions'] = $post['currentUserReactions'] ? explode(',', $post['currentUserReactions']) : [];
            unset(
                $post['count_like'], $post['count_dislike'], $post['count_heart'],
                $post['count_laugh'], $post['count_question'], $post['count_exclamation']
            );
            return $post;
        }, $posts);
    }

    /**
     * Holt alle Kommentare für einen bestimmten Post.
     *
     * @param int $postId Die ID des Posts.
     * @return array Ein Array von Kommentaren.
     */
    public function getCommentsByPostId(int $postId): array {
        $sql = "
            SELECT 
                k.id, k.text, k.datumZeit,
                n.nutzerName AS autor,
                IF(n.profilBild = '', 'assets/placeholder-profilbild.jpg', n.profilBild) as profilBild,
                n.id as userId
            FROM kommentar k
            JOIN nutzer n ON k.nutzer_id = n.id
            WHERE k.post_id = ?
            ORDER BY k.datumZeit ASC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $comments;
    }

    /**
     * Erstellt einen neuen Kommentar.
     *
     * @param int $postId Die ID des Posts.
     * @param int $userId Die ID des Autors.
     * @param string $text Der Kommentartext.
     * @return bool True bei Erfolg.
     */
    public function createComment(int $postId, int $userId, string $text): bool {
        $sql = "INSERT INTO kommentar (post_id, nutzer_id, text) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iis", $postId, $userId, $text);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Findet einen einzelnen Kommentar anhand seiner ID.
     *
     * @param int $commentId Die ID des gesuchten Kommentars.
     * @return array|null Die Kommentar-Daten als assoziatives Array oder null, wenn nicht gefunden.
     */
    public function findCommentById(int $commentId): ?array {
        $sql = "SELECT * FROM kommentar WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment = $result->fetch_assoc();
        $stmt->close();

        return $comment ?: null;
    }

    /**
     * Löscht einen Kommentar aus der Datenbank.
     *
     * @param int $commentId Die ID des zu löschenden Kommentars.
     * @return bool True bei Erfolg, false bei einem Fehler.
     */
    public function deleteComment(int $commentId): bool {
        $sql = "DELETE FROM kommentar WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $commentId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
} 