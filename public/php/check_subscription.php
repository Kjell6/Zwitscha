<?php
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

// Fehlerbehandlung
function sendJsonResponse($exists) {
    echo json_encode(['exists' => $exists]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false);
    }

    if (!isLoggedIn()) {
        sendJsonResponse(false);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['endpoint'])) {
        sendJsonResponse(false);
    }

    $endpoint = $data['endpoint'];
    $currentUserId = getCurrentUserId();

    $db = db::getInstance();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM push_subscriptions WHERE user_id = ? AND endpoint = ?");
    $stmt->bind_param("is", $currentUserId, $endpoint);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $exists = ($row['count'] > 0);
    sendJsonResponse($exists);

} catch (Exception $e) {
    error_log("Fehler in check_subscription.php: " . $e->getMessage());
    sendJsonResponse(false);
} 