<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/PostVerwaltung.php'; // Hinzugefügt

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

echo "Starte Benachrichtigungsversand...\n";

// === VAPID-Schlüssel laden ===
$vapidPublicKey = $_SERVER['VAPID_PUBLIC_KEY'] ?? '';
$vapidPrivateKey = $_SERVER['VAPID_PRIVATE_KEY'] ?? '';
$vapidSubject = $_SERVER['VAPID_SUBJECT'] ?? 'mailto:default@example.com';

if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
    die("Fehler: VAPID-Schlüssel sind nicht in den Umgebungsvariablen gesetzt.\n");
}

$auth = [
    'VAPID' => [
        'subject' => $vapidSubject,
        'publicKey' => $vapidPublicKey,
        'privateKey' => $vapidPrivateKey,
    ],
];

$webPush = new WebPush($auth);
$db = db::getInstance();
$postVerwaltung = new PostVerwaltung(); // Hinzugefügt

// === Ungelesene Benachrichtigungen holen ===
// Wir fügen `is_sent` zur `notifications` Tabelle hinzu, um doppeltes Senden zu vermeiden.
// Fürs Erste nutzen wir `is_read` als Platzhalter, ideal wäre eine eigene Spalte `is_sent`.
$result = $db->query("
    SELECT n.id, n.recipient_id, n.sender_id, n.type, n.reference_id, u.nutzerName as sender_name
    FROM notifications n
    JOIN nutzer u ON n.sender_id = u.id
    WHERE n.is_read = 0 -- Annahme: 0 bedeutet ungesendet
    LIMIT 50 -- Um die Last zu begrenzen
");

$notifications = $result->fetch_all(MYSQLI_ASSOC);
if (empty($notifications)) {
    echo "Keine neuen Benachrichtigungen zum Senden gefunden.\n";
    exit;
}

echo "Gefunden: " . count($notifications) . " Benachrichtigungen.\n";

// === Benachrichtigungen erstellen und in die Warteschlange stellen ===
foreach ($notifications as $notification) {
    // Abonnements für den Empfänger holen
    $subscriptionsResult = $db->prepare("SELECT * FROM push_subscriptions WHERE user_id = ?");
    $subscriptionsResult->bind_param("i", $notification['recipient_id']);
    $subscriptionsResult->execute();
    $subscriptions = $subscriptionsResult->get_result()->fetch_all(MYSQLI_ASSOC);
    $subscriptionsResult->close();

    if (empty($subscriptions)) {
        echo "Kein Abonnement für Nutzer " . $notification['recipient_id'] . " gefunden. Überspringe.\n";
        continue;
    }

    // Nachricht zusammenstellen
    list($title, $body, $url) = generateNotificationContent($notification); // postVerwaltung übergeben
    $payload = json_encode(['title' => $title, 'body' => $body, 'icon' => 'https://web.zwitscha.social/assets/ZwitschaIcon.png', 'url' => $url]);

    foreach ($subscriptions as $sub) {
        $subscription = Subscription::create([
            'endpoint' => $sub['endpoint'],
            'publicKey' => $sub['p256dh'],
            'authToken' => $sub['auth'],
        ]);
        $webPush->queueNotification($subscription, $payload);
    }
}

// === Benachrichtigungen versenden ===
echo "Sende Benachrichtigungen...\n";
$reportSuccess = [];
$reportFailure = [];

foreach ($webPush->flush() as $report) {
    if ($report->isSuccess()) {
        $reportSuccess[] = $report->getEndpoint();
    } else {
        $reportFailure[] = "Endpoint: {$report->getEndpoint()}, Grund: {$report->getReason()}";
        // Hier könnte man abgelaufene Abonnements aus der DB löschen
    }
}

if (!empty($reportSuccess)) {
    echo "Erfolgreich gesendet an: \n" . implode("\n", $reportSuccess) . "\n";
}
if (!empty($reportFailure)) {
    echo "Fehler bei: \n" . implode("\n", $reportFailure) . "\n";
}

// === Gesendete Benachrichtigungen markieren ===
$sentNotificationIds = array_column($notifications, 'id');
if (!empty($sentNotificationIds)) {
    $ids_string = implode(',', $sentNotificationIds);
    $db->query("UPDATE notifications SET is_read = 1 WHERE id IN ($ids_string)"); // is_read wird als is_sent missbraucht
    echo count($sentNotificationIds) . " Benachrichtigungen als 'gesendet' markiert.\n";
}

echo "Fertig.\n";


/**
 * Generiert Titel, Text und URL für eine Benachrichtigung.
 */
function generateNotificationContent(array $notification): array {
    global $db; // Zugriff auf die globale DB-Instanz
    $sender = $notification['sender_name'];
    $base_url = 'https://web.zwitscha.social';
    $url = $base_url; // Standard-URL

    switch ($notification['type']) {
        case 'new_post_from_followed_user':
            $title = "Neuer Post von @{$sender}";
            $body = "Schau dir an, was @{$sender} Neues gepostet hat!";
            $url = "{$base_url}/postDetails.php?post_id={$notification['reference_id']}";
            break;
        case 'new_comment_on_own_post':
            $title = "@{$sender} hat deinen Post kommentiert";
            $body = "Dein Post hat einen neuen Kommentar erhalten.";
            $url = "{$base_url}/kommentarEinzeln.php?comment_id={$notification['reference_id']}";
            break;
        case 'new_reply_to_own_comment':
            $title = "@{$sender} hat auf deinen Kommentar geantwortet";
            $body = "Jemand hat auf deinen Kommentar geantwortet.";
            $url = "{$base_url}/kommentarEinzeln.php?comment_id={$notification['reference_id']}";
            break;
        case 'mention_in_post':
            $title = "Du wurdest von @{$sender} erwähnt";
            $body = "@{$sender} hat dich in einem Post erwähnt.";
            $url = "{$base_url}/postDetails.php?post_id={$notification['reference_id']}";
            break;
        case 'mention_in_comment':
            $title = "Du wurdest von @{$sender} erwähnt";
            $body = "@{$sender} hat dich in einem Kommentar erwähnt.";
            // Um zum Kommentar zu verlinken, brauchen wir die post_id
            $stmt = $db->prepare("SELECT post_id FROM kommentar WHERE id = ?");
            $stmt->bind_param('i', $notification['reference_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result) {
                // Link zum einzelnen Kommentar ist am besten, da es den Kontext zeigt.
                $url = "{$base_url}/kommentarEinzeln.php?comment_id={$notification['reference_id']}";
            }
            break;
        default:
            $title = "Neue Benachrichtigung";
            $body = "Du hast eine neue Benachrichtigung auf Zwitscha.";
            break;
    }
    return [$title, $body, $url];
} 