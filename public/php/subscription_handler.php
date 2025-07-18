<?php
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

// Eine globale Fehlerbehandlung, um sicherzustellen, dass wir immer JSON zurückgeben.
function sendJsonError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    if (!isLoggedIn()) {
        sendJsonError('Benutzer ist nicht angemeldet.', 401);
    }

    $currentUserId = getCurrentUserId();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || empty($data['subscription']) || empty($data['subscription']['keys'])) {
        sendJsonError('Ungültige oder unvollständige Daten erhalten.', 400);
    }

    $subscription = $data['subscription'];
    $endpoint = $subscription['endpoint'];
    $p256dh = $subscription['keys']['p256dh'];
    $auth = $subscription['keys']['auth'];

    if (empty($endpoint) || empty($p256dh) || empty($auth)) {
        sendJsonError('Abonnement-Daten sind unvollständig.', 400);
    }

    $db = db::getInstance();
    $stmt = $db->prepare(
        "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) 
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE p256dh = ?, auth = ?"
    );

    // Binden der Parameter an die Anweisung
    $stmt->bind_param("isssss", $currentUserId, $endpoint, $p256dh, $auth, $p256dh, $auth);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Abonnement erfolgreich gespeichert.']);

} catch (Exception $e) {
    // Fängt alle anderen Fehler ab, einschließlich mysqli-Fehler
    sendJsonError('Ein unerwarteter Fehler ist aufgetreten: ' . $e->getMessage());
} 