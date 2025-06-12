<?php

require_once __DIR__ . '/db.php';

class NutzerVerwaltung {
    private mysqli $db;

    public function __construct() {
        $this->db = db::getInstance();
    }

    /**
     * Holt die Profildaten für einen bestimmten Nutzer.
     *
     * @param int $userId Die ID des Nutzers.
     * @return array|null Die Profildaten oder null, wenn nicht gefunden.
     */
    public function getUserProfileData(int $userId): ?array {
        $sql = "
            SELECT 
                n.id,
                n.nutzerName,
                IF(n.profilBild = '' OR n.profilBild IS NULL, 'assets/placeholder-profilbild.jpg', n.profilBild) as profilBild,
                n.erstellungsDatum AS registrierungsDatum,
                (SELECT COUNT(*) FROM folge WHERE gefolgter_id = n.id) AS followerCount,
                (SELECT COUNT(*) FROM folge WHERE folgender_id = n.id) AS followingCount,
                (SELECT COUNT(*) FROM post WHERE nutzer_id = n.id) AS postCount
            FROM nutzer n
            WHERE n.id = ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user ?: null;
    }

    /**
     * Prüft, ob ein Nutzer einem anderen folgt.
     *
     * @param int $followerId Der Nutzer, der folgt.
     * @param int $followeeId Der Nutzer, dem gefolgt wird.
     * @return bool True, wenn eine Follow-Beziehung besteht.
     */
    public function isFollowing(int $followerId, int $followeeId): bool {
        $sql = "SELECT COUNT(*) as count FROM folge WHERE folgender_id = ? AND gefolgter_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ii", $followerId, $followeeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result['count'] > 0;
    }

    /**
     * Schaltet den Follow-Status zwischen zwei Nutzern um.
     *
     * @param int $followerId Der Nutzer, der folgt.
     * @param int $followeeId Der Nutzer, dem gefolgt wird.
     * @return bool True bei Erfolg.
     */
    public function toggleFollow(int $followerId, int $followeeId): bool {
        if ($this->isFollowing($followerId, $followeeId)) {
            // Unfollow
            $sql = "DELETE FROM folge WHERE folgender_id = ? AND gefolgter_id = ?";
        } else {
            // Follow
            $sql = "INSERT INTO folge (folgender_id, gefolgter_id) VALUES (?, ?)";
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ii", $followerId, $followeeId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Sucht nach Nutzern anhand ihres Namens.
     *
     * @param string $searchTerm Der Suchbegriff.
     * @param int $limit Maximale Anzahl der Ergebnisse (Standard: 10).
     * @return array Array von Nutzerdaten.
     */
    public function searchUsers(string $searchTerm, int $limit = 10): array {
        if (empty(trim($searchTerm))) {
            return [];
        }

        $sql = "
            SELECT 
                n.id,
                n.nutzerName,
                IF(n.profilBild = '' OR n.profilBild IS NULL, 'assets/placeholder-profilbild.jpg', n.profilBild) as profilBild,
                (SELECT COUNT(*) FROM folge WHERE gefolgter_id = n.id) AS followerCount
            FROM nutzer n
            WHERE n.nutzerName LIKE ?
            ORDER BY n.nutzerName ASC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $searchPattern = '%' . $searchTerm . '%';
        $stmt->bind_param("si", $searchPattern, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $users;
    }
} 