<?php
// Nutzer-Verwaltung für Registrierung, Login, Profile, etc.

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
                (SELECT COUNT(*) FROM post WHERE nutzer_id = n.id) AS postCount,
                (SELECT COUNT(*) FROM kommentar WHERE nutzer_id = n.id) AS commentCount
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
     * Holt die vollständigen Benutzerdaten für einen Nutzer anhand des Nutzernamens.
     *
     * @param string $username Der Nutzername.
     * @return array|null Die Benutzerdaten oder null, wenn nicht gefunden.
     */
    public function getUserByUsername(string $username): ?array {
        $sql = "SELECT id, nutzerName, profilbild, istAdministrator, erstellungsDatum FROM nutzer WHERE nutzerName = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("s", $username);
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
        // Haupt-Admin kann nicht deaktiviert werden
        $user = $this->getUserById($userId);
        if ($user && $user['nutzerName'] === 'admin' && !$isAdmin) {
            return false;
        }

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
     * Löscht einen Nutzer und all seine zugehörigen Daten endgültig.
     * Dies kann nicht rückgängig gemacht werden.
     *
     * @param int $userId Die ID des zu löschenden Nutzers.
     * @return bool True bei Erfolg.
     */
    public function deleteUser(int $userId): bool {
        // Haupt-Admin kann nicht gelöscht werden
        $user = $this->getUserById($userId);
        if ($user && $user['nutzerName'] === 'admin') {
            return false;
        }

        $this->db->begin_transaction();

        try {
            // Alle Posts des Nutzers löschen (inkl. Kommentare)
            $stmt = $this->db->prepare("DELETE FROM post WHERE nutzer_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            // Nutzer selbst löschen (CASCADE löscht automatisch: Folge-Beziehungen, Kommentare, Reaktionen, Tokens)
            $stmt = $this->db->prepare("DELETE FROM nutzer WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            return false;
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
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Benutzername muss mindestens 3 Zeichen lang sein.'];
        }
        if (strlen($username) > 20) {
            return ['success' => false, 'message' => 'Benutzername darf maximal 20 Zeichen lang sein.'];
        }
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            return ['success' => false, 'message' => 'Benutzername darf nur Buchstaben, Zahlen, Punkte, Unterstriche und Bindestriche enthalten.'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Passwort muss mindestens 6 Zeichen lang sein.'];
        }
        if (strlen($password) > 100) {
            return ['success' => false, 'message' => 'Passwort darf maximal 100 Zeichen lang sein.'];
        }
        if (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]+$/', $password)) {
            return ['success' => false, 'message' => 'Passwort enthält unerlaubte Zeichen.'];
        }
        
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Benutzername ist bereits vergeben.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO nutzer (nutzerName, passwort, istAdministrator) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("DB-Fehler bei registerUser prepare: " . $this->db->error);
            return ['success' => false, 'message' => 'Registrierung fehlgeschlagen. Bitte versuchen Sie es später erneut.'];
        }

        $isAdmin = 0;
        $stmt->bind_param("ssi", $username, $hashedPassword, $isAdmin);

        if ($stmt->execute()) {
            $newUserId = $this->db->insert_id;
            $stmt->close();
            return [
                'success' => true, 
                'message' => 'Registrierung erfolgreich!', 
                'userId' => $newUserId
            ];
        } else {
            error_log("DB-Fehler bei registerUser execute: " . $stmt->error);
            $stmt->close();
            return ['success' => false, 'message' => 'Registrierung fehlgeschlagen. Bitte versuchen Sie es später erneut.'];
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
            if (password_verify($password, $user['passwort'])) {
                unset($user['passwort']);
                return $user;
            }
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Holt alle Follower eines bestimmten Nutzers.
     *
     * @param int $userId Die ID des Nutzers, dessen Follower geholt werden sollen.
     * @return array Eine Liste von Nutzern, die dem gegebenen Nutzer folgen.
     */
    public function getFollowers(int $userId): array {
        $sql = "
            SELECT 
                n.id,
                n.nutzerName,
                n.profilbild
            FROM nutzer n
            JOIN folge f ON n.id = f.folgender_id
            WHERE f.gefolgter_id = ?
            ORDER BY n.nutzerName ASC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $followers = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $followers;
    }

    /**
     * Holt alle Nutzer, denen ein bestimmter Nutzer folgt.
     *
     * @param int $userId Die ID des Nutzers.
     * @return array Eine Liste von Nutzern, denen der gegebene Nutzer folgt.
     */
    public function getFollowing(int $userId): array {
        $sql = "
            SELECT 
                n.id,
                n.nutzerName,
                n.profilbild
            FROM nutzer n
            JOIN folge f ON n.id = f.gefolgter_id
            WHERE f.folgender_id = ?
            ORDER BY n.nutzerName ASC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $following = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $following;
    }

    /**
     * Erstellt ein neues "Angemeldet bleiben"-Token für einen Nutzer.
     *
     * @param int $userId Die ID des Nutzers.
     */
    public function createRememberToken(int $userId): void {
        $selektor = bin2hex(random_bytes(6));
        $validator = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $validator);
        $gueltigBis = (new DateTime('+30 days'))->format('Y-m-d H:i:s');

        $sql = "INSERT INTO login_tokens (nutzer_id, selektor, tokenHash, gueltigBis) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isss", $userId, $selektor, $tokenHash, $gueltigBis);
        $stmt->execute();
        $stmt->close();

        setcookie(
            'rememberme',
            $selektor . ':' . $validator,
            [
                'expires' => strtotime($gueltigBis),
                'path' => '/',
                'secure' => true, 
                'httponly' => true, 
                'samesite' => 'Lax' 
            ]
        );
    }

    /**
     * Überprüft ein "Angemeldet bleiben"-Token und loggt den Nutzer bei Erfolg ein.
     *
     * @param string $selektor Der Selector-Teil des Tokens.
     * @param string $validator Der Validator-Teil des Tokens.
     * @return array|null Die Nutzerdaten bei Erfolg, sonst null.
     */
    public function consumeRememberToken(string $selektor, string $validator): ?array {
        $sql = "SELECT nutzer_id, tokenHash, gueltigBis FROM login_tokens WHERE selektor = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $selektor);
        $stmt->execute();
        $result = $stmt->get_result();
        $token_data = $result->fetch_assoc();
        $stmt->close();

        if (!$token_data) {
            return null;
        }

        if (new DateTime() > new DateTime($token_data['gueltigBis'])) {
            $this->deleteRememberToken($selektor); 
            return null;
        }

        if (hash_equals($token_data['tokenHash'], hash('sha256', $validator))) {
            $this->deleteRememberToken($selektor);
            $this->createRememberToken($token_data['nutzer_id']);

            return $this->getUserById($token_data['nutzer_id']);
        }

        return null;
    }

    /**
     * Löscht ein "Angemeldet bleiben"-Token aus der Datenbank.
     *
     * @param string $selektor Der Selector des zu löschenden Tokens.
     */
    public function deleteRememberToken(string $selektor): void {
        $sql = "DELETE FROM login_tokens WHERE selektor = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $selektor);
        $stmt->execute();
        $stmt->close();
    }
} 