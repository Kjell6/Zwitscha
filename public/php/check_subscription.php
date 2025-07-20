<?php
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

// Debug-Logging aktivieren
error_log("DEBUG check_subscription.php: Anfrage erhalten");

// Fehlerbehandlung
function sendJsonResponse($exists, $debug_info = '') {
    $response = ['exists' => $exists];
    if ($debug_info) {
        $response['debug'] = $debug_info;
    }
    error_log("DEBUG check_subscription.php: Antwort - exists: " . ($exists ? 'true' : 'false') . ", debug: " . $debug_info);
    echo json_encode($response);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("DEBUG check_subscription.php: Falsche HTTP-Methode - " . $_SERVER['REQUEST_METHOD']);
        sendJsonResponse(false, 'Falsche HTTP-Methode');
    }

    if (!isLoggedIn()) {
        error_log("DEBUG check_subscription.php: Benutzer nicht angemeldet");
        sendJsonResponse(false, 'Nicht angemeldet');
    }

    $rawInput = file_get_contents('php://input');
    error_log("DEBUG check_subscription.php: Rohe Eingabe - " . $rawInput);
    
    $data = json_decode($rawInput, true);
    
    if (!$data || empty($data['endpoint'])) {
        error_log("DEBUG check_subscription.php: Ungültige Daten - " . print_r($data, true));
        sendJsonResponse(false, 'Ungültige Daten');
    }

    $endpoint = $data['endpoint'];
    $currentUserId = getCurrentUserId();
    
    error_log("DEBUG check_subscription.php: Prüfe User $currentUserId mit Endpoint: " . substr($endpoint, 0, 50) . '...');

    $db = db::getInstance();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM push_subscriptions WHERE user_id = ? AND endpoint = ?");
    $stmt->bind_param("is", $currentUserId, $endpoint);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $exists = ($row['count'] > 0);
    error_log("DEBUG check_subscription.php: Gefunden: " . $row['count'] . " Subscriptions");
    sendJsonResponse($exists, "User: $currentUserId, Count: " . $row['count']);

} catch (Exception $e) {
    error_log("DEBUG check_subscription.php: Exception - " . $e->getMessage());
    sendJsonResponse(false, 'Exception: ' . $e->getMessage());
} 