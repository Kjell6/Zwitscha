<?php
require_once __DIR__ . '/php/session_helper.php';

require_once __DIR__ . '/vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

requireLogin();
$currentUserId = getCurrentUserId();

if ($currentUserId !== 3) {
    header('HTTP/1.1 403 Forbidden');
    echo '<h1>Zugriff verweigert</h1><p>Du hast keine Berechtigung, dieses Dashboard zu sehen.</p>';
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// === Live-Log AJAX ===
if (isset($_GET['action']) && $_GET['action'] === 'logs') {
    // Passe ggf. den Service-Namen an!
    $service = 'voice-assistant.service';
    $lines = 100;
    $cmd = "journalctl -u " . escapeshellarg($service) . " -n $lines --no-pager 2>&1";
    $output = shell_exec($cmd);
    // Gegen XSS schützen
    header('Content-Type: text/plain; charset=utf-8');
    echo htmlspecialchars($output ?? 'Keine Logs gefunden.');
    exit();
}


// Systeminfos
$os = php_uname();
$hostname = gethostname();
$phpVersion = phpversion();
$uptime = shell_exec("uptime -p");
$datetime = date("Y-m-d H:i:s");

// CPU
$load = sys_getloadavg();
$cpuCores = (int) shell_exec("nproc 2>/dev/null") ?: count(explode("\n", trim(shell_exec("cat /proc/cpuinfo | grep processor"))));

// RAM
$meminfo = file_get_contents("/proc/meminfo");
preg_match("/MemTotal:\s+(\d+)/", $meminfo, $total);
preg_match("/MemAvailable:\s+(\d+)/", $meminfo, $available);
$totalMem = round($total[1] / 1024, 2); // MB
$freeMem = round($available[1] / 1024, 2); // MB
$usedMem = $totalMem - $freeMem;

// CPU-Temperatur
$cpuTemp = null;
if (file_exists('/sys/class/thermal/thermal_zone0/temp')) {
    $cpuTemp = round(intval(file_get_contents('/sys/class/thermal/thermal_zone0/temp')) / 1000, 1);
} else {
    $cpuTemp = shell_exec("vcgencmd measure_temp 2>/dev/null");
    if ($cpuTemp) {
        $cpuTemp = floatval(str_replace(['temp=', "'C\n"], '', $cpuTemp));
    } else {
        $cpuTemp = "Nicht verfügbar";
    }
}

// Festplattenplatz
$diskTotal = round(disk_total_space("/") / (1024 ** 3), 2); // GB
$diskFree = round(disk_free_space("/") / (1024 ** 3), 2); // GB
$diskUsed = $diskTotal - $diskFree;
$diskUsedPercent = $diskTotal > 0 ? round($diskUsed / $diskTotal * 100) : 0;

// Letzter Reboot
$lastRebootRaw = shell_exec("who -b | awk '{print $3, $4}'");
$lastReboot = $lastRebootRaw !== null ? trim($lastRebootRaw) : '';

// Systemauslastung (Prozentwerte)
$cpuLoadPercent = $cpuCores > 0 ? round($load[0] / $cpuCores * 100) : 0;
$ramUsedPercent = $totalMem > 0 ? round($usedMem / $totalMem * 100) : 0;

$pageTitle = 'Dashboard';

// === Push-Subscriptions + Nutzernamen + Plattform laden ===
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/NutzerVerwaltung.php';

$pushSubscriptions = [];
$nutzerVerwaltung = new NutzerVerwaltung();
$db = db::getInstance();

// Alle Subscriptions holen
$res = $db->query("SELECT * FROM push_subscriptions");
while ($row = $res->fetch_assoc()) {
    $user = $nutzerVerwaltung->getUserById($row['user_id']);
    $row['nutzerName'] = $user ? $user['nutzerName'] : 'Unbekannt';
    // Plattform bestimmen
    $endpoint = $row['endpoint'];
    if (strpos($endpoint, 'android.googleapis.com') !== false || strpos($endpoint, 'fcm.googleapis.com') !== false) {
        $row['plattform'] = 'Google';
    } elseif (strpos($endpoint, 'updates.push.services.mozilla.com') !== false || strpos($endpoint, 'updates-autopush.stage.mozaws.net') !== false || strpos($endpoint, 'updates-autopush.dev.mozaws.net') !== false) {
        $row['plattform'] = 'Mozilla';
    } elseif (preg_match('/\.notify\.windows\.com($|\/)/', $endpoint)) {
        $row['plattform'] = 'Windows';
    } elseif (preg_match('/\.push\.apple\.com($|\/)/', $endpoint)) {
        $row['plattform'] = 'Apple';
    } else {
        $row['plattform'] = 'Unbekannt';
    }
    $pushSubscriptions[] = $row;
}

// === Notification-Versand ===
$notifMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_push_notification'])) {
    $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
    $title = trim($_POST['notif_title'] ?? '');
    $body = trim($_POST['notif_body'] ?? '');
    $icon = 'https://web.zwitscha.social/assets/ZwitschaIcon.png';
    // Ziel-URL: immer Profil des Nutzers
    $profileUrl = '/Profil.php?id=3';

    $sub = null;
    foreach ($pushSubscriptions as $ps) {
        if ($ps['id'] == $subscriptionId) {
            $sub = $ps;
            break;
        }
    }
    if ($sub && $title && $body) {
        $vapidPublicKey = $_SERVER['VAPID_PUBLIC_KEY'] ?? getenv('VAPID_PUBLIC_KEY') ?: '';
        $vapidPrivateKey = $_SERVER['VAPID_PRIVATE_KEY'] ?? getenv('VAPID_PRIVATE_KEY') ?: '';
        $vapidSubject = $_SERVER['VAPID_SUBJECT'] ?? getenv('VAPID_SUBJECT') ?: 'mailto:default@example.com';
        if ($vapidPublicKey && $vapidPrivateKey) {
            $auth = [
                'VAPID' => [
                    'subject' => $vapidSubject,
                    'publicKey' => $vapidPublicKey,
                    'privateKey' => $vapidPrivateKey,
                ],
            ];
            $webPush = new WebPush($auth);
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => $icon,
                'url' => $profileUrl
            ]);
            $webPush->queueNotification($subscription, $payload);
            $result = $webPush->flush();
            $success = false;
            foreach ($result as $report) {
                if ($report->isSuccess()) {
                    $success = true;
                } else {
                    $notifMsg = 'Fehler: ' . htmlspecialchars($report->getReason());
                }
            }
            if ($success) {
                $notifMsg = 'Notification erfolgreich gesendet!';
            } elseif (!$notifMsg) {
                $notifMsg = 'Unbekannter Fehler beim Senden.';
            }
        } else {
            $notifMsg = 'VAPID-Schlüssel fehlen.';
        }
    } else {
        $notifMsg = 'Bitte alle Felder ausfüllen und ein Abo auswählen.';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?php include 'global-header.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #111;
            color: #fff;
            font-family: 'Fira Mono', 'Consolas', 'Menlo', 'Monaco', monospace;
            width: 100%;
            padding-top: 28px;
        }
        .container {
            max-width: 900px;
            margin: 2em auto;
            padding: 0 1em;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 2em;
            justify-content: center;
            width: 100%;
        }
        .card {
            background: #181818;
            border-radius: 12px;
            box-shadow: 0 2px 12px #0005;
            padding: 2em 2em 1.5em 2em;
            min-width: 270px;
            max-width: 420px;
            flex: 1 1 1;
            margin-bottom: 2em;
            border: 1px solid #333;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #fff;
            font-size: 2.2em;
            margin-bottom: 1.2em;
            letter-spacing: 1px;
            text-align: center;
        }
        h2 {
            color: #fff;
            font-size: 1.2em;
            margin-bottom: 0.7em;
            letter-spacing: 1px;
            text-align: center;
        }
        dt {
            color: #fff;
            font-weight: bold;
            margin-top: 1em;
        }
        dd {
            margin-left: 0;
            margin-bottom: .5em;
        }
        .bar {
            background: #222;
            border-radius: 6px;
            overflow: hidden;
            height: 1.2em;
            margin-bottom: .7em;
            box-shadow: 0 1px 4px #0003 inset;
        }
        .bar-inner {
            height: 100%;
            background: #fff;
            opacity: 0.2;
            transition: width 0.5s;
        }
        .terminal-box {
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 1em 1.5em;
            margin-bottom: 1em;
            font-size: 1em;
            box-shadow: 0 1px 6px #0002;
            color: #fff;
        }
        .network-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1em;
        }
        .network-table th, .network-table td {
            border-bottom: 1px solid #333;
            padding: 0.4em 0.7em;
            text-align: left;
        }
        .network-table th {
            color: #fff;
            font-weight: bold;
            background: #181818;
        }
        .network-table td {
            color: #fff;
        }
        .temp-history {
            display: flex;
            gap: 0.5em;
            align-items: flex-end;
            height: 60px;
            margin-top: 1em;
        }
        .temp-bar {
            width: 18px;
            background: #fff;
            opacity: 0.2;
            border-radius: 4px 4px 0 0;
            display: inline-block;
            margin-right: 2px;
            position: relative;
        }
        .temp-bar span {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9em;
            color: #fff;
            white-space: nowrap;
        }

        .zwitscha-back-btn {
            display: inline-flex;
            align-items: center;
            background: #222;
            border-radius: 24px;
            padding: 10px;
            box-shadow: 0 2px 8px #0003;
            transition: background 0.2s;
        }
        .zwitscha-back-btn:hover {
            background: #333;
            color: #1baf1e;
        }
        .zwitscha-back-btn img {
            height: 32px;
            width: auto;
        }

        .push-panel { background: #222; border-radius: 12px; box-shadow: 0 2px 12px #0005; padding: 2em; margin-bottom: 2em; color: #fff; max-width: 420px; width: 100%; }
        .push-panel label { display: block; margin-top: 1em; }
        .push-panel select, .push-panel input, .push-panel textarea { width: 100%; padding: 0.5em; margin-top: 0.3em; border-radius: 6px; border: 1px solid #333; background: #181818; color: #fff; }
        .push-panel button { margin-top: 1.5em; padding: 0.7em 2em; border-radius: 8px; border: none; background: #22DA26; color: #111; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .push-panel button:hover { background: #1baf1e; }
        .push-panel .msg { margin-top: 1em; font-weight: bold; }


        @media (max-width: 900px) {
            .cards { flex-direction: column; align-items: stretch; }
            .card { min-width: 0; }
        }
    </style>
</head>
<body>

    <!-- === MAIN CONTAINER === -->
    <a href="index.php" class="zwitscha-back-btn" title="Zurück zur Startseite">
        <img src="assets/favicon.png" alt="Zwitscha Logo">
    </a>
    <div class="container">
        <!-- === Push Notification Panel === -->
        <div class="push-panel card">
            <h2>Push-Notification testen</h2>
            <form method="post">
                <label for="subscription_id">Abo auswählen:</label>
                <select name="subscription_id" id="subscription_id" required>
                    <option value="">-- Bitte wählen --</option>
                    <?php foreach ($pushSubscriptions as $ps): ?>
                        <option value="<?= $ps['id'] ?>">
                            <?= htmlspecialchars($ps['nutzerName']) ?> (<?= htmlspecialchars($ps['plattform']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="notif_title">Titel:</label>
                <input type="text" name="notif_title" id="notif_title" maxlength="80" required>
                <label for="notif_body">Text:</label>
                <textarea name="notif_body" id="notif_body" rows="3" maxlength="200" required></textarea>
                <button type="submit" name="send_push_notification">Notification senden</button>
                
                <?php /*
                Debug-Nachrichten

                if ($notifMsg): ?>
                    <div class="msg"><?= htmlspecialchars($notifMsg) ?></div>
                <?php endif;
                */ ?>
            </form>
        </div>

        <h1>Raspberry Pi Systeminfo</h1>
        <div class="cards">
            <!-- System Card -->
            <div class="card">
                <h2>System</h2>
                <div class="terminal-box">
                    <b>Betriebssystem:</b> <?= htmlspecialchars($os) ?><br>
                    <b>Hostname:</b> <?= htmlspecialchars($hostname) ?><br>
                    <b>Systemzeit:</b> <?= htmlspecialchars($datetime) ?><br>
                    <b>Uptime:</b> <?= htmlspecialchars(trim($uptime)) ?><br>
                    <b>PHP-Version:</b> <?= htmlspecialchars($phpVersion) ?><br>
                    <b>Letzter Reboot:</b> <?= htmlspecialchars($lastReboot) ?><br>
                </div>
            </div>
            <!-- Auslastung Card -->
            <div class="card">
                <h2>Systemauslastung</h2>
                <div class="terminal-box">
                    <b>CPU-Last:</b> <?= $cpuLoadPercent ?>% (<?= implode(' / ', $load) ?>)<br>
                    <div class="bar"><div class="bar-inner" style="width: <?= $cpuLoadPercent ?>%"></div></div>
                    <b>RAM benutzt:</b> <?= $usedMem ?> MB / <?= $totalMem ?> MB (<?= $ramUsedPercent ?>%)<br>
                    <div class="bar"><div class="bar-inner" style="width: <?= $ramUsedPercent ?>%"></div></div>
                    <b>Festplatte:</b> <?= $diskUsed ?> GB / <?= $diskTotal ?> GB (<?= $diskUsedPercent ?>%)<br>
                    <div class="bar"><div class="bar-inner" style="width: <?= $diskUsedPercent ?>%"></div></div>
                </div>
            </div>
            <!-- Temperatur Card -->
            <div class="card">
                <h2>CPU-Temperatur</h2>
                <div class="terminal-box">
                    <b>Aktuell:</b> <?= htmlspecialchars($cpuTemp) ?> °C
                </div>
            </div>

        <!-- === Live-Logs Card === -->
        <div class="log-card">
            <div class="log-title">Live-Logs: <code>voice-assistant.service</code></div>
            <div class="log-refresh" id="log-refresh-info">Letztes Update: <span id="log-last-update">-</span></div>
            <div class="log-error" id="log-error" style="display:none"></div>
            <div class="logbox" id="logbox">Lade Logs...</div>
        </div>

        </div>
    </div>

    <script>
        // === Live-Logbox Updater ===
        function updateLogs() {
            fetch(window.location.pathname + '?action=logs')
                .then(response => {
                    if (!response.ok) throw new Error('Fehler beim Laden der Logs');
                    return response.text();
                })
                .then(text => {
                    document.getElementById('logbox').textContent = text;
                    document.getElementById('log-error').style.display = 'none';
                    document.getElementById('log-last-update').textContent = (new Date()).toLocaleTimeString();
                })
                .catch(err => {
                    document.getElementById('log-error').textContent = err.message;
                    document.getElementById('log-error').style.display = '';
                });
        }
        updateLogs();
        setInterval(updateLogs, 2000);
    </script>

</body>
</html>