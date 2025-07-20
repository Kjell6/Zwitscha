<?php
// Umfassendes Debug-Skript für Umgebungsunterschiede
header('Content-Type: text/plain; charset=utf-8');

echo "=== UMGEBUNGS-DEBUG VERGLEICH ===\n\n";

echo "=== GRUNDLEGENDE SYSTEMINFO ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Betriebssystem: " . PHP_OS . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'NICHT GESETZT') . "\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'NICHT GESETZT') . "\n";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'NICHT GESETZT') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'NICHT GESETZT') . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? 'JA (' . $_SERVER['HTTPS'] . ')' : 'NEIN') . "\n\n";

echo "=== VAPID-SCHLÜSSEL STATUS ===\n";
echo "\$_SERVER Methode:\n";
echo "  VAPID_PUBLIC_KEY: " . (isset($_SERVER['VAPID_PUBLIC_KEY']) ? 'GESETZT (Länge: ' . strlen($_SERVER['VAPID_PUBLIC_KEY']) . ')' : 'NICHT GESETZT') . "\n";
echo "  VAPID_PRIVATE_KEY: " . (isset($_SERVER['VAPID_PRIVATE_KEY']) ? 'GESETZT (Länge: ' . strlen($_SERVER['VAPID_PRIVATE_KEY']) . ')' : 'NICHT GESETZT') . "\n";
echo "  VAPID_SUBJECT: " . ($_SERVER['VAPID_SUBJECT'] ?? 'NICHT GESETZT') . "\n";

echo "\ngetenv() Methode:\n";
echo "  VAPID_PUBLIC_KEY: " . (getenv('VAPID_PUBLIC_KEY') ? 'GESETZT (Länge: ' . strlen(getenv('VAPID_PUBLIC_KEY')) . ')' : 'NICHT GESETZT') . "\n";
echo "  VAPID_PRIVATE_KEY: " . (getenv('VAPID_PRIVATE_KEY') ? 'GESETZT (Länge: ' . strlen(getenv('VAPID_PRIVATE_KEY')) . ')' : 'NICHT GESETZT') . "\n";
echo "  VAPID_SUBJECT: " . (getenv('VAPID_SUBJECT') ?: 'NICHT GESETZT') . "\n";

echo "\n=== DOCKER/CONTAINER INFO ===\n";
echo "Container ID: " . (file_exists('/proc/self/cgroup') ? trim(substr(file_get_contents('/proc/self/cgroup'), -12)) : 'NICHT VERFÜGBAR') . "\n";
echo "Hostname: " . gethostname() . "\n";

echo "\n=== UMGEBUNGSVARIABLEN (alle mit VAPID) ===\n";
$found_vapid = false;
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'VAPID') !== false) {
        echo "ENV[$key]: " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) . "\n";
        $found_vapid = true;
    }
}
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'VAPID') !== false) {
        echo "SERVER[$key]: " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) . "\n";
        $found_vapid = true;
    }
}
if (!$found_vapid) {
    echo "KEINE VAPID-Variablen gefunden!\n";
}

echo "\n=== DATENBANKVERBINDUNG ===\n";
try {
    require_once __DIR__ . '/php/db.php';
    $db = db::getInstance();
    echo "Datenbankverbindung: ERFOLGREICH\n";
    echo "MySQL Version: " . $db->server_info . "\n";
    
    // Prüfe push_subscriptions Tabelle
    $result = $db->query("SHOW TABLES LIKE 'push_subscriptions'");
    echo "push_subscriptions Tabelle: " . ($result->num_rows > 0 ? 'EXISTIERT' : 'EXISTIERT NICHT') . "\n";
    
    if ($result->num_rows > 0) {
        $count_result = $db->query("SELECT COUNT(*) as count FROM push_subscriptions");
        $count = $count_result->fetch_assoc()['count'];
        echo "Anzahl Abonnements: " . $count . "\n";
    }
} catch (Exception $e) {
    echo "Datenbankfehler: " . $e->getMessage() . "\n";
}

echo "\n=== DATEISYSTEM CHECKS ===\n";
echo "Aktuelles Verzeichnis: " . getcwd() . "\n";
echo "Script-Pfad: " . __FILE__ . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NICHT GESETZT') . "\n";

$files_to_check = [
    'manifest.json',
    'sw.js', 
    'js/notifications.js',
    'php/subscription_handler.php',
    'php/check_subscription.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    echo "$file: " . ($exists ? 'EXISTIERT' : 'FEHLT');
    if ($exists) {
        echo " (Größe: " . filesize($file) . " Bytes)";
    }
    echo "\n";
}

echo "\n=== COMPOSER/VENDOR CHECK ===\n";
echo "vendor/autoload.php: " . (file_exists('vendor/autoload.php') ? 'EXISTIERT' : 'FEHLT') . "\n";
if (file_exists('vendor/autoload.php')) {
    echo "WebPush Library: ";
    try {
        require_once 'vendor/autoload.php';
        if (class_exists('Minishlink\\WebPush\\WebPush')) {
            echo "VERFÜGBAR\n";
        } else {
            echo "NICHT VERFÜGBAR\n";
        }
    } catch (Exception $e) {
        echo "FEHLER: " . $e->getMessage() . "\n";
    }
}

echo "\nDebug-Skript abgeschlossen.\n"; 