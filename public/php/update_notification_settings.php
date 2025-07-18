<?php
header('Content-Type: application/json');

require_once __DIR__ . '/NutzerVerwaltung.php';
require_once __DIR__ . '/session_helper.php';

// === Initialisierung & Sicherheitschecks ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Nur POST-Anfragen sind erlaubt.']);
    exit;
}

requireLogin();
$currentUserId = getCurrentUserId();
$data = json_decode(file_get_contents('php://input'), true);

if ($data === null || !isset($data['type']) || !isset($data['enabled'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage: Fehlende Daten.']);
    exit;
}

// === Daten validieren ===
$notificationType = $data['type'];
$isEnabled = (bool)$data['enabled'];

$allowedTypes = [
    'new_post_from_followed_user',
    'new_comment_on_own_post',
    'new_reply_to_own_comment',
    'mention_in_post'
];

if (!in_array($notificationType, $allowedTypes)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Ungültiger Benachrichtigungstyp.']);
    exit;
}

// === Einstellung aktualisieren ===
$nutzerVerwaltung = new NutzerVerwaltung();
$success = $nutzerVerwaltung->updateNotificationSetting($currentUserId, $notificationType, $isEnabled);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Einstellung konnte nicht gespeichert werden.']);
} 