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
                n.profilbild,
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
                n.profilbild,
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

    /**
     * Holt die vollständigen Benutzerdaten für einen Nutzer (inklusive Admin-Status).
     *
     * @param int $userId Die ID des Nutzers.
     * @return array|null Die Benutzerdaten oder null, wenn nicht gefunden.
     */
    public function getUserById(int $userId): ?array {
        $sql = "SELECT id, nutzerName, profilbild, istAdministrator, erstellungsDatum FROM nutzer WHERE id = ?";
        
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
     * Ändert den Admin-Status eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers, dessen Admin-Status geändert werden soll.
     * @param bool $isAdmin Der neue Admin-Status (true = Admin, false = normaler User).
     * @return bool True bei Erfolg.
     */
    public function setAdminStatus(int $userId, bool $isAdmin): bool {
        $adminValue = $isAdmin ? 1 : 0;
        $sql = "UPDATE nutzer SET istAdministrator = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ii", $adminValue, $userId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Ändert den Namen eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers.
     * @param string $newName Der neue Name.
     * @return bool True bei Erfolg.
     */
    public function updateUserName(int $userId, string $newName): bool {
        $sql = "UPDATE nutzer SET nutzerName = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $newName, $userId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Überprüft das aktuelle Passwort eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers.
     * @param string $currentPassword Das eingegebene aktuelle Passwort.
     * @return bool True wenn das Passwort korrekt ist.
     */
    public function verifyCurrentPassword(int $userId, string $currentPassword): bool {
        $sql = "SELECT passwort FROM nutzer WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) return false;

        // Passwort-Hash mit dem eingegebenen Passwort vergleichen
        return password_verify($currentPassword, $user['passwort']);
    }

    /**
     * Ändert das Passwort eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers.
     * @param string $newPassword Das neue Passwort.
     * @return bool True bei Erfolg.
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE nutzer SET passwort = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $hashedPassword, $userId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Aktualisiert das Profilbild eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers.
     * @param string $imageData Die binären Daten des neuen Profilbilds.
     * @return bool True bei Erfolg.
     */
    public function updateProfileImage(int $userId, string $imageData): bool {
        $sql = "UPDATE nutzer SET profilbild = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $imageData, $userId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Löscht einen Nutzer-Account komplett aus der Datenbank.
     *
     * @param int $userId Die ID des Nutzers.
     * @return bool True bei Erfolg.
     */
    public function deleteUser(int $userId): bool {
        // Da es möglicherweise Foreign Key Constraints gibt, müssen wir die 
        // zugehörigen Daten in der richtigen Reihenfolge löschen
        
        $this->db->autocommit(false); // Transaction starten
        
        try {
            // 1. Reaktionen des Nutzers löschen
            $stmt = $this->db->prepare("DELETE FROM Reaktion WHERE nutzer_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            
            // 2. Kommentare des Nutzers löschen
            $stmt = $this->db->prepare("DELETE FROM kommentar WHERE nutzer_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            
            // 3. Follow-Beziehungen löschen (als Follower und als Gefolgter)
            $stmt = $this->db->prepare("DELETE FROM folge WHERE folgender_id = ? OR gefolgter_id = ?");
            $stmt->bind_param("ii", $userId, $userId);
            $stmt->execute();
            $stmt->close();
            
            // 4. Posts des Nutzers löschen (inklusive zugehörige Reaktionen/Kommentare durch CASCADE)
            $stmt = $this->db->prepare("DELETE FROM post WHERE nutzer_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            
            // 5. Schließlich den Nutzer selbst löschen
            $stmt = $this->db->prepare("DELETE FROM nutzer WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            
            $this->db->commit(); // Alles erfolgreich, Transaction bestätigen
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback(); // Bei Fehler alles rückgängig machen
            return false;
        } finally {
            $this->db->autocommit(true); // Autocommit wieder aktivieren
        }
    }

    /**
     * Registriert einen neuen Nutzer.
     *
     * @param string $username Der gewünschte Benutzername.
     * @param string $password Das Passwort (Klartext).
     * @return array Ergebnis mit ['success' => bool, 'message' => string, 'userId' => int|null]
     */
    public function registerUser(string $username, string $password): array {
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Benutzername ist bereits vergeben.'];
        }

        // Passwort sicher hashen
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO nutzer (nutzerName, passwort, istAdministrator) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Datenbankfehler bei der Vorbereitung.'];
        }

        $isAdmin = 0;
        $stmt->bind_param("ssi", $username, $hashedPassword, $isAdmin);

        if ($stmt->execute()) {
            $newUserId = $this->db->insert_id;
            return [
                'success' => true, 
                'message' => 'Registrierung erfolgreich!', 
                'userId' => $newUserId
            ];
        } else {
            return ['success' => false, 'message' => 'Registrierung fehlgeschlagen.'];
        }
    }

    /**
     * Prüft, ob ein Benutzername bereits existiert.
     *
     * @param string $username Der zu prüfende Benutzername.
     * @return bool True wenn der Benutzername bereits existiert.
     */
    public function usernameExists(string $username): bool {
        $sql = "SELECT COUNT(*) as count FROM nutzer WHERE nutzerName = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) return false;

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result['count'] > 0;
    }

    /**
     * Authentifiziert einen Nutzer mit Benutzername und Passwort.
     *
     * @param string $username Der Benutzername.
     * @param string $password Das Passwort (Klartext).
     * @return array|null Die Benutzerdaten bei erfolgreicher Authentifizierung oder null.
     */
    public function authenticateUser(string $username, string $password): ?array {
        $sql = "SELECT id, nutzerName, passwort, profilbild, istAdministrator, erstellungsDatum FROM nutzer WHERE nutzerName = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("DB-Fehler bei authenticateUser prepare: " . $this->db->error);
            return null;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Vergleiche das eingegebene Passwort mit dem Hash in der Datenbank
            if (password_verify($password, $user['passwort'])) {
                // Passwort aus den zurückgegebenen Daten entfernen, bevor es zurückgegeben wird
                unset($user['passwort']);
                return $user;
            }
        }
        
        $stmt->close();
        return null;
    }
} 