<?php
require_once __DIR__ . '/php/init.php';

header('Content-Type: text/plain');

echo "=== DEBUG: Raspberry Pi Benachrichtigungen ===\n\n";

// 1. VAPID-Schlüssel prüfen
echo "1. VAPID-Schlüssel:\n";
$vapidPublicKey = $_SERVER['VAPID_PUBLIC_KEY'] ?? getenv('VAPID_PUBLIC_KEY') ?: '';
$vapidPrivateKey = $_SERVER['VAPID_PRIVATE_KEY'] ?? getenv('VAPID_PRIVATE_KEY') ?: '';

echo "   Public Key: " . ($vapidPublicKey ? 'GESETZT (Länge: ' . strlen($vapidPublicKey) . ')' : 'NICHT GESETZT') . "\n";
echo "   Private Key: " . ($vapidPrivateKey ? 'GESETZT (Länge: ' . strlen($vapidPrivateKey) . ')' : 'NICHT GESETZT') . "\n\n";

// 2. Datenbank-Verbindung prüfen
echo "2. Datenbank-Verbindung:\n";
try {
    $db = db::getInstance();
    echo "   Status: ERFOLGREICH\n";
    
    // Anzahl Subscriptions prüfen
    $result = $db->query("SELECT COUNT(*) as count FROM push_subscriptions");
    $row = $result->fetch_assoc();
    echo "   Anzahl Subscriptions: " . $row['count'] . "\n";
    
    // Subscriptions pro User anzeigen
    $result = $db->query("SELECT user_id, COUNT(*) as count FROM push_subscriptions GROUP BY user_id");
    echo "   Subscriptions pro User:\n";
    while ($row = $result->fetch_assoc()) {
        echo "     User " . $row['user_id'] . ": " . $row['count'] . " Subscription(s)\n";
    }
    
} catch (Exception $e) {
    echo "   Status: FEHLER - " . $e->getMessage() . "\n";
}

echo "\n3. Server-Umgebung:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'UNBEKANNT') . "\n";
echo "   HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'UNBEKANNT') . "\n";
echo "   Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'UNBEKANNT') . "\n";

// 4. Aktuelle Session prüfen (falls angemeldet)
echo "\n4. Session-Status:\n";
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    echo "   Status: ANGEMELDET\n";
    echo "   User ID: " . $userId . "\n";
    
    // Subscriptions für diesen User prüfen
    $stmt = $db->prepare("SELECT endpoint, created_at FROM push_subscriptions WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "   Subscriptions für diesen User:\n";
    if ($result->num_rows === 0) {
        echo "     KEINE Subscriptions gefunden\n";
    } else {
        while ($row = $result->fetch_assoc()) {
            $endpoint_short = substr($row['endpoint'], 0, 50) . '...';
            echo "     - " . $endpoint_short . " (erstellt: " . $row['created_at'] . ")\n";
        }
    }
    $stmt->close();
} else {
    echo "   Status: NICHT ANGEMELDET\n";
}

echo "\n=== Ende Debug ===\n"; 