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

    /**
     * Holt die vollständigen Benutzerdaten für einen Nutzer (inklusive Admin-Status).
     *
     * @param int $userId Die ID des Nutzers.
     * @return array|null Die Benutzerdaten oder null, wenn nicht gefunden.
     */
    public function getUserById(int $userId): ?array {
        $sql = "SELECT id, nutzerName, profilBild, istAdministrator, erstellungsDatum FROM nutzer WHERE id = ?";
        
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

        return $currentPassword === $user['passwort'];
    }

    /**
     * Ändert das Passwort eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers.
     * @param string $newPassword Das neue Passwort.
     * @return bool True bei Erfolg.
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        $sql = "UPDATE nutzer SET passwort = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $newPassword, $userId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Aktualisiert das Profilbild eines Nutzers.
     *
     * @param int $userId Die ID des Nutzers.
     * @param string $imagePath Der Pfad zum neuen Profilbild.
     * @return bool True bei Erfolg.
     */
    public function updateProfileImage(int $userId, string $imagePath): bool {
        $sql = "UPDATE nutzer SET profilBild = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $imagePath, $userId);
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
        // Eingabe validieren
        $username = trim($username);
        if (empty($username)) {
            return ['success' => false, 'message' => 'Benutzername darf nicht leer sein.', 'userId' => null];
        }
        
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Benutzername muss mindestens 3 Zeichen lang sein.', 'userId' => null];
        }
        
        if (strlen($username) > 15) {
            return ['success' => false, 'message' => 'Benutzername darf maximal 15 Zeichen lang sein.', 'userId' => null];
        }
        
        if (empty($password)) {
            return ['success' => false, 'message' => 'Passwort darf nicht leer sein.', 'userId' => null];
        }

        // Prüfen ob Benutzername bereits existiert
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Benutzername ist bereits vergeben.', 'userId' => null];
        }

        // Nutzer in Datenbank einfügen
        $sql = "INSERT INTO nutzer (nutzerName, passwort, profilBild, istAdministrator) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Datenbankfehler beim Erstellen des Accounts.', 'userId' => null];
        }

        $defaultProfileImage = 'assets/placeholder-profilbild.jpg';
        $isAdmin = 0; // Neue Nutzer sind standardmäßig keine Admins
        
        $stmt->bind_param("sssi", $username, $password, $defaultProfileImage, $isAdmin);
        
        if ($stmt->execute()) {
            $userId = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Account erfolgreich erstellt!', 'userId' => $userId];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Fehler beim Erstellen des Accounts.', 'userId' => null];
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
        $sql = "SELECT id, nutzerName, passwort, profilBild, istAdministrator, erstellungsDatum FROM nutzer WHERE nutzerName = ? AND passwort = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) return null;

        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Passwort aus den zurückgegebenen Daten entfernen
            unset($user['passwort']);
            return $user;
        }

        return null;
    }
} 