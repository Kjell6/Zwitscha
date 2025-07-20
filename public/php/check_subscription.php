<?php
require_once __DIR__ . '/init.php'; // Ihre neue Initialisierungsdatei

header('Content-Type: application/json');

function send_json_error($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['is_subscribed' => false, 'error' => $message]);
    exit;
}

if (!isLoggedIn()) {
    send_json_error('Benutzer nicht angemeldet.', 401);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['endpoint'])) {
    send_json_error('Endpoint nicht angegeben.', 400);
}

try {
    $db = db::getInstance();
    $stmt = $db->prepare("SELECT COUNT(*) FROM push_subscriptions WHERE endpoint = ? AND user_id = ?");
    
    $currentUserId = getCurrentUserId();
    $endpoint = $data['endpoint'];
    
    $stmt->bind_param("si", $endpoint, $currentUserId);
    $stmt->execute();
    
    $count = 0;
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    echo json_encode(['is_subscribed' => $count > 0]);

} catch (Exception $e) {
    send_json_error('Datenbankfehler: ' . $e->getMessage());
} 