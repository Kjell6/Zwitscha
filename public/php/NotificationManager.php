<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/NutzerVerwaltung.php';

class NotificationManager {
    private mysqli $db;
    private NutzerVerwaltung $nutzerVerwaltung;

    public function __construct() {
        $this->db = db::getInstance();
        $this->nutzerVerwaltung = new NutzerVerwaltung();
    }

    /**
     * Erstellt eine Benachrichtigung in der Datenbank.
     *
     * @param int $recipientId Empfänger der Benachrichtigung.
     * @param int|null $senderId Auslöser der Benachrichtigung.
     * @param string $type Typ der Benachrichtigung.
     * @param int|null $referenceId ID des zugehörigen Objekts (z.B. Post, Kommentar).
     * @return bool True bei Erfolg.
     */
    private function createNotification(int $recipientId, ?int $senderId, string $type, ?int $referenceId): bool {
        $sql = "INSERT INTO notifications (recipient_id, sender_id, type, reference_id) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iisi", $recipientId, $senderId, $type, $referenceId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Löst Benachrichtigungen für Follower aus, wenn ein neuer Post erstellt wird.
     *
     * @param int $postId Die ID des neuen Posts.
     * @param int $authorId Die ID des Autors.
     */
    public function handleNewPost(int $postId, int $authorId): void {
        $followers = $this->nutzerVerwaltung->getFollowers($authorId);
        
        foreach ($followers as $follower) {
            $followerId = $follower['id'];
            // Eigene Aktionen nicht benachrichtigen
            if ($followerId === $authorId) continue;

            $settings = $this->nutzerVerwaltung->getNotificationSettings($followerId);
            
            if ($settings['new_post_from_followed_user'] ?? false) {
                $this->createNotification($followerId, $authorId, 'new_post_from_followed_user', $postId);
            }
        }
    }

    /**
     * Löst Benachrichtigungen für neue Kommentare aus.
     *
     * @param int $commentId Die ID des neuen Kommentars.
     * @param int $postId Die ID des Posts, zu dem der Kommentar gehört.
     * @param int $commenterId Die ID des Nutzers, der kommentiert hat.
     * @param int $postAuthorId Die ID des Autors des Posts.
     */
    public function handleNewComment(int $commentId, int $postId, int $commenterId, int $postAuthorId): void {
        // Benachrichtige den Post-Autor (wenn er nicht selbst kommentiert)
        if ($postAuthorId !== $commenterId) {
            $settings = $this->nutzerVerwaltung->getNotificationSettings($postAuthorId);
            if ($settings['new_comment_on_own_post'] ?? false) {
                $this->createNotification($postAuthorId, $commenterId, 'new_comment_on_own_post', $commentId);
            }
        }
    }

    /**
     * Löst Benachrichtigungen für Antworten auf Kommentare aus.
     *
     * @param int $replyId Die ID der neuen Antwort.
     * @param int $commenterId Die ID des Nutzers, der geantwortet hat.
     * @param int $parentCommentAuthorId Die ID des Autors des ursprünglichen Kommentars.
     */
    public function handleNewReply(int $replyId, int $commenterId, int $parentCommentAuthorId): void {
        // Benachrichtige den Autor des Eltern-Kommentars (wenn er nicht selbst antwortet)
        if ($parentCommentAuthorId !== $commenterId) {
            $settings = $this->nutzerVerwaltung->getNotificationSettings($parentCommentAuthorId);
            if ($settings['new_reply_to_own_comment'] ?? false) {
                $this->createNotification($parentCommentAuthorId, $commenterId, 'new_reply_to_own_comment', $replyId);
            }
        }
    }

    /**
     * Verarbeitet Erwähnungen in einem Text und löst Benachrichtigungen aus.
     *
     * @param string $text Der Text, der Erwähnungen enthalten kann.
     * @param int $senderId Die ID des Nutzers, der den Text verfasst hat.
     * @param int $referenceId Die ID des Posts oder Kommentars, in dem die Erwähnung vorkommt.
     * @param string $notificationType Der Typ der Benachrichtigung ('mention_in_post' oder 'mention_in_comment').
     */
    public function handleMentions(string $text, int $senderId, int $referenceId, string $notificationType): void {
        $mentionedUsernames = extractMentions($text);

        foreach ($mentionedUsernames as $username) {
            $mentionedUser = $this->nutzerVerwaltung->getUserByUsername($username);
            
            if ($mentionedUser) {
                $mentionedUserId = $mentionedUser['id'];

                // Sich selbst nicht benachrichtigen
                if ($mentionedUserId === $senderId) continue;

                $settings = $this->nutzerVerwaltung->getNotificationSettings($mentionedUserId);
                if ($settings['mention_in_post'] ?? false) { // Annahme: eine Einstellung für alle Erwähnungen
                    $this->createNotification($mentionedUserId, $senderId, $notificationType, $referenceId);
                }
            }
        }
    }
} 