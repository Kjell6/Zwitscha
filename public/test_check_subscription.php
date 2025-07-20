<?php
// Manueller Test für check_subscription.php
require_once __DIR__ . '/php/init.php';

header('Content-Type: text/plain');

echo "=== MANUELLER TEST: check_subscription.php ===\n\n";

if (!isLoggedIn()) {
    echo "FEHLER: Nicht angemeldet. Bitte zuerst anmelden.\n";
    exit;
}

$currentUserId = getCurrentUserId();
echo "Aktueller User: $currentUserId\n\n";

// Fake Endpoint zum Testen
$testEndpoint = "https://fcm.googleapis.com/fcm/send/test-endpoint-12345";

echo "Teste mit Fake-Endpoint: $testEndpoint\n";

// Simuliere POST-Request
$_SERVER['REQUEST_METHOD'] = 'POST';
$testData = json_encode(['endpoint' => $testEndpoint]);

// Schreibe Test-Daten in temporäre Datei
file_put_contents('php://memory', $testData);

echo "Rufe check_subscription.php auf...\n";

// Capture output
ob_start();
try {
    // Simuliere den Aufruf
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/php/check_subscription.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: ' . $_SERVER['HTTP_COOKIE'] // Session-Cookie weiterleiten
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    
} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
}

echo "\n=== Ende Test ===\n"; 