<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// VAPID-Schlüssel (ersetzen Sie diese in einer Produktivumgebung durch Umgebungsvariablen)
$vapidPublicKey = 'BJxX1uVuBeafFnQWLh49WksunbYOI-xM5iONmecrNta9V9MzOBsuBJgj6eJroTUZebP7zzlnwko-34Ck4upjafc';
$vapidPrivateKey = 'TMX9g5c3uA7mp7QOaiYpSSfwwmfqoiE4sSTHaQhnSM0';

$auth = [
    'VAPID' => [
        'subject' => 'mailto:deine-email@dein-server.com', // Ersetzen Sie dies durch Ihre E-Mail
        'publicKey' => $vapidPublicKey,
        'privateKey' => $vapidPrivateKey,
    ],
];

// WebPush-Instanz erstellen
$webPush = new WebPush($auth);

// Alle Abonnements aus der Datenbank holen
$db = db::getInstance();
$result = $db->query("SELECT * FROM push_subscriptions");
$subscriptions = $result->fetch_all(MYSQLI_ASSOC);

// Die Benachrichtigungsinhalte
$notificationPayload = json_encode([
    'title' => 'Test-Benachrichtigung von Zwitscha!',
    'body' => 'Wenn du das siehst, funktionieren die Push-Nachrichten. 🎉',
    'icon' => 'https://web.zwitscha.social/assets/ZwitschaIcon.png', // Absoluter Pfad zum Icon
    'url' => 'https://web.zwitscha.social/' // URL, die beim Klick geöffnet wird
]);

// Benachrichtigung an alle Abonnenten senden
foreach ($subscriptions as $sub) {
    $subscription = Subscription::create([
        'endpoint' => $sub['endpoint'],
        'publicKey' => $sub['p256dh'],
        'authToken' => $sub['auth'],
    ]);
    $webPush->queueNotification(
        $subscription,
        $notificationPayload
    );
}

// Alle Benachrichtigungen in der Warteschlange senden
echo "Sende Benachrichtigungen an " . count($subscriptions) . " Abonnenten...\n";
foreach ($webPush->flush() as $report) {
    $endpoint = $report->getRequest()->getUri()->__toString();

    if ($report->isSuccess()) {
        echo "[v] Nachricht erfolgreich gesendet an {$endpoint}.\n";
    } else {
        echo "[x] Nachricht fehlgeschlagen für {$endpoint}: {$report->getReason()}\n";
    }
}
echo "Fertig.\n"; 