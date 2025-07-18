<?php
// Post-Verwaltung für Posts, Kommentare, Reaktionen, etc.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/NotificationManager.php';

class PostVerwaltung {
    private mysqli $db;
    private NotificationManager $notificationManager;

    public function __construct() {
        $this->db = db::getInstance();
        $this->notificationManager = new NotificationManager();
    }



    /**
     * Holt alle Posts aus der Datenbank, inklusive Autor-Informationen.
     *
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers.
     * @return array Ein Array von assoziativen Arrays, die die Posts repräsentieren.
     */
    public function getAllPosts(int $currentUserId, int $limit = 15, int $offset = 0): array {
        $sql = "
            SELECT 
                p.id, p.text, p.bildDaten, p.datumZeit,
                n.nutzerName AS autor, n.profilbild, n.id as userId,
                (SELECT COUNT(*) FROM kommentar WHERE post_id = p.id) AS comments,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Hoch') AS count_like,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Runter') AS count_dislike,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Herz') AS count_heart,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Lachen') AS count_laugh,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Fragezeichen') AS count_question,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Ausrufezeichen') AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            ORDER BY p.datumZeit DESC
            LIMIT ? OFFSET ?
        ";
        
        return $this->_fetchAndProcessPosts($sql, [$currentUserId, $limit, $offset], 'iii');
    }

    /**
     * Holt nur die Posts von Nutzern, denen der aktuelle Nutzer folgt.
     *
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers.
     * @return array Ein Array von Posts.
     */
    public function getFollowedPosts(int $currentUserId, int $limit = 15, int $offset = 0): array {
        $sql = "
            SELECT 
                p.id, p.text, p.bildDaten, p.datumZeit,
                n.nutzerName AS autor, n.profilbild, n.id as userId,
                (SELECT COUNT(*) FROM kommentar WHERE post_id = p.id) AS comments,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Hoch') AS count_like,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Runter') AS count_dislike,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Herz') AS count_heart,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Lachen') AS count_laugh,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Fragezeichen') AS count_question,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Ausrufezeichen') AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            INNER JOIN folge f ON p.nutzer_id = f.gefolgter_id AND f.folgender_id = ?
            ORDER BY p.datumZeit DESC
            LIMIT ? OFFSET ?
        ";
        
        return $this->_fetchAndProcessPosts($sql, [$currentUserId, $currentUserId, $limit, $offset], 'iiii');
    }

    public function getPostsWithOffset(int $currentUserId, int $offset, int $limit): array {
        return $this->getAllPosts($currentUserId, $limit, $offset);
    }
    
    /**
     * Holt alle Posts, die einen bestimmten Hashtag enthalten.
     *
     * @param string $hashtag Der Hashtag, nach dem gesucht werden soll (ohne #).
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers.
     * @return array Ein Array von Posts.
     */
    public function getPostsByHashtag(string $hashtag, int $currentUserId, int $limit = 15, int $offset = 0): array {
        $sql = "
            SELECT 
                p.id, p.text, p.bildDaten, p.datumZeit,
                n.nutzerName AS autor, n.profilbild, n.id as userId,
                (SELECT COUNT(*) FROM kommentar WHERE post_id = p.id) AS comments,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Hoch') AS count_like,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Runter') AS count_dislike,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Herz') AS count_heart,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Lachen') AS count_laugh,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Fragezeichen') AS count_question,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Ausrufezeichen') AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            WHERE p.text REGEXP ?
            ORDER BY p.datumZeit DESC
            LIMIT ? OFFSET ?
        ";
        
        $escapedHashtag = $this->db->real_escape_string($hashtag);
        $regexp = '(^|[[:space:]])#' . $escapedHashtag . '[[:>:]]';

        return $this->_fetchAndProcessPosts($sql, [$currentUserId, $regexp, $limit, $offset], 'isii');
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
        // Eingabe validieren
        if ($userId <= 0 || $postId <= 0 || empty($emoji) || strlen($emoji) > 10) {
            return false;
        }
        
        // Post-Existenz prüfen
        $postExists = $this->findPostById($postId);
        if (!$postExists) {
            return false;
        }
        
        // Emoji zu DB-Reaktionstyp umwandeln
        $reactionMap = array_flip(getReactionEmojiMap());
        if (!isset($reactionMap[$emoji])) {
            return false;
        }
        $reactionType = $reactionMap[$emoji];

        // Prüfen ob Reaktion bereits existiert
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM Reaktion WHERE nutzer_id = ? AND post_id = ? AND reaktionsTyp = ?");
        $stmt->bind_param("iis", $userId, $postId, $reactionType);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            // Reaktion entfernen
            $stmt = $this->db->prepare("DELETE FROM Reaktion WHERE nutzer_id = ? AND post_id = ? AND reaktionsTyp = ?");
            $stmt->bind_param("iis", $userId, $postId, $reactionType);
            $stmt->execute();
            $stmt->close();
        } else {
            // Reaktion hinzufügen
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
     * @param string|null $imageData Die binären Daten des hochgeladenen Bildes (optional).
     * @return int|false Die ID des neuen Posts bei Erfolg, sonst false.
     */
    public function createPost(int $userId, string $text, ?string $imageData): int|false {
        $sql = "INSERT INTO post (nutzer_id, text, bildDaten) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iss", $userId, $text, $imageData);
        
        if ($stmt->execute()) {
            $newPostId = $this->db->insert_id;
            $stmt->close();
            
            // Benachrichtigungen für Follower auslösen
            $this->notificationManager->handleNewPost($newPostId, $userId);

            // Benachrichtigungen für Erwähnungen auslösen
            $this->notificationManager->handleMentions($text, $userId, $newPostId, 'mention_in_post');

            return $newPostId;
        }

        $stmt->close();
        return false;
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
                p.id, p.text, p.bildDaten, p.datumZeit,
                n.nutzerName AS autor, n.profilbild, n.id as userId,
                (SELECT COUNT(*) FROM kommentar WHERE post_id = p.id) AS comments,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Hoch') AS count_like,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Runter') AS count_dislike,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Herz') AS count_heart,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Lachen') AS count_laugh,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Fragezeichen') AS count_question,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Ausrufezeichen') AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            WHERE p.id = ?
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

        // Parameter binden und Abfrage ausführen
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Reaktionen für alle Posts verarbeiten
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
            k.id, k.text, k.datumZeit, k.parent_comment_id,
            n.nutzerName AS autor,
            n.profilbild,
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

    public function getMainCommentsByPostId(int $postId): array {
        $sql = "
        SELECT 
            k.id, k.text, k.datumZeit,
            n.nutzerName AS autor,
            n.profilbild,
            n.id as userId
        FROM kommentar k
        JOIN nutzer n ON k.nutzer_id = n.id
        WHERE k.post_id = ? AND k.parent_comment_id IS NULL
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

    public function getRepliesByParentCommentId(int $parentCommentId): array {
        $sql = "
        SELECT 
            k.id, k.text, k.datumZeit,
            n.nutzerName AS autor,
            n.profilbild,
            n.id as userId
        FROM kommentar k
        JOIN nutzer n ON k.nutzer_id = n.id
        WHERE k.parent_comment_id = ?
        ORDER BY k.datumZeit ASC
    ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $parentCommentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $replies = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $replies;
    }





    /**
     * Erstellt einen neuen Kommentar.
     *
     * @param int $postId Die ID des Posts.
     * @param int $userId Die ID des Autors.
     * @param string $text Der Kommentartext.
     * @param int|null $parentCommentId Die ID des übergeordneten Kommentars (für Antworten).
     * @return int|false Die ID des neuen Kommentars bei Erfolg, false bei Fehler.
     */
    public function createComment(int $postId, int $userId, string $text, ?int $parentCommentId = null): int|false {
        // Zuerst den Autor des Posts ermitteln
        $post = $this->findPostById($postId);
        if (!$post) {
            return false; // Post nicht gefunden
        }
        $postAuthorId = $post['nutzer_id'];

        $sql = "INSERT INTO kommentar (post_id, nutzer_id, text, parent_comment_id) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisi", $postId, $userId, $text, $parentCommentId);
        
        if ($stmt->execute()) {
            $newCommentId = $this->db->insert_id;
            $stmt->close();

            // Benachrichtigung für den Post-Autor auslösen (nur bei Hauptkommentaren)
            if ($parentCommentId === null) {
                $this->notificationManager->handleNewComment($newCommentId, $postId, $userId, $postAuthorId);
            } else {
                // Es ist eine Antwort, Benachrichtigung für den Autor des Eltern-Kommentars auslösen
                $parentComment = $this->findCommentById($parentCommentId);
                if ($parentComment) {
                    $parentCommentAuthorId = $parentComment['nutzer_id'];
                    $this->notificationManager->handleNewReply($newCommentId, $userId, $parentCommentAuthorId);
                }
            }

            // Benachrichtigungen für Erwähnungen im Kommentar auslösen
            $this->notificationManager->handleMentions($text, $userId, $newCommentId, 'mention_in_comment');

            return $newCommentId;
        }

        $stmt->close();
        return false;
    }

    /**
     * Holt einen einzelnen Kommentar mit Nutzer-Informationen.
     *
     * @param int $commentId Die ID des Kommentars.
     * @return array|null Die Kommentar-Daten oder null, wenn nicht gefunden.
     */
    public function getCommentById(int $commentId): ?array {
        $sql = "
            SELECT 
                k.id, k.text, k.datumZeit, k.post_id, k.parent_comment_id, k.nutzer_id,
                n.nutzerName AS nutzer_name, n.profilbild
            FROM kommentar k
            JOIN nutzer n ON k.nutzer_id = n.id
            WHERE k.id = ?
        ";

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

    /**
     * Holt alle Posts für einen bestimmten Nutzer.
     *
     * @param int $userId Die ID des Nutzers, dessen Posts geholt werden sollen.
     * @param int $currentUserId Die ID des aktuell eingeloggten Nutzers (für Reaktions-Status).
     * @return array Ein Array von Posts.
     */
    public function getPostsByUserId(int $userId, int $currentUserId, int $limit = 15, int $offset = 0): array {
        $sql = "
            SELECT 
                p.id, p.text, p.bildDaten, p.datumZeit,
                n.nutzerName AS autor, n.profilbild, n.id as userId,
                (SELECT COUNT(*) FROM kommentar WHERE post_id = p.id) AS comments,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Hoch') AS count_like,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Daumen Runter') AS count_dislike,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Herz') AS count_heart,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Lachen') AS count_laugh,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Fragezeichen') AS count_question,
                (SELECT COUNT(*) FROM Reaktion WHERE post_id = p.id AND reaktionsTyp = 'Ausrufezeichen') AS count_exclamation,
                (SELECT GROUP_CONCAT(reaktionsTyp) FROM Reaktion WHERE post_id = p.id AND nutzer_id = ?) AS currentUserReactions
            FROM post p
            JOIN nutzer n ON p.nutzer_id = n.id
            WHERE p.nutzer_id = ?
            ORDER BY p.datumZeit DESC
            LIMIT ? OFFSET ?
        ";
        
        return $this->_fetchAndProcessPosts($sql, [$currentUserId, $userId, $limit, $offset], 'iiii');
    }

    /**
     * Holt alle Kommentare für einen bestimmten Nutzer mit zugehörigen Post-Informationen.
     *
     * @param int $userId Die ID des Nutzers, dessen Kommentare geholt werden sollen.
     * @param int $limit Maximale Anzahl der Kommentare (Standard: 15).
     * @param int $offset Anzahl der zu überspringenden Kommentare (Standard: 0).
     * @return array Ein Array von Kommentaren mit Post-Informationen.
     */
    public function getCommentsByUserId(int $userId, int $limit = 15, int $offset = 0): array {
        $sql = "
            SELECT 
                k.id, k.text, k.datumZeit, k.parent_comment_id, k.post_id,
                n.nutzerName AS autor,
                n.profilbild,
                n.id as userId,
                p.text AS postText,
                p.bildDaten AS postBildDaten,
                p.datumZeit AS postDatum,
                pn.nutzerName AS postAutor,
                pn.id AS postAutorId
            FROM kommentar k
            JOIN nutzer n ON k.nutzer_id = n.id
            JOIN post p ON k.post_id = p.id
            JOIN nutzer pn ON p.nutzer_id = pn.id
            WHERE k.nutzer_id = ?
            ORDER BY k.datumZeit DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $comments;
    }

    /**
     * Holt alle Kommentare, die einen bestimmten Hashtag enthalten.
     *
     * @param string $hashtag Der Hashtag, nach dem gesucht werden soll (ohne #).
     * @param int $limit Maximale Anzahl der Kommentare (Standard: 15).
     * @param int $offset Anzahl der zu überspringenden Kommentare (Standard: 0).
     * @return array Ein Array von Kommentaren mit Post-Informationen.
     */
    public function getCommentsByHashtag(string $hashtag, int $limit = 15, int $offset = 0): array {
        $sql = "
            SELECT 
                k.id, k.text, k.datumZeit, k.parent_comment_id, k.post_id,
                n.nutzerName AS autor,
                n.profilbild,
                n.id as userId,
                p.text AS postText,
                p.bildDaten AS postBildDaten,
                p.datumZeit AS postDatum,
                pn.nutzerName AS postAutor,
                pn.id AS postAutorId
            FROM kommentar k
            JOIN nutzer n ON k.nutzer_id = n.id
            JOIN post p ON k.post_id = p.id
            JOIN nutzer pn ON p.nutzer_id = pn.id
            WHERE k.text REGEXP ?
            ORDER BY k.datumZeit DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $escapedHashtag = $this->db->real_escape_string($hashtag);
        $regexp = '(^|[[:space:]])#' . $escapedHashtag . '[[:>:]]';

        $stmt->bind_param("sii", $regexp, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $comments;
    }


} 