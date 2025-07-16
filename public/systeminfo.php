<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Temperaturverlauf (letzte 5 Werte aus Datei, falls vorhanden)
$tempHistoryFile = sys_get_temp_dir() . '/pi_temp_history.txt';
$tempHistory = [];
if (file_exists($tempHistoryFile)) {
    $lines = file($tempHistoryFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tempHistory = array_slice($lines, -5);
}
// Aktuellen Wert anhängen (für Anzeige, nicht zum Speichern!)
$tempHistory[] = $cpuTemp;

// Festplattenplatz
$diskTotal = round(disk_total_space("/") / (1024 ** 3), 2); // GB
$diskFree = round(disk_free_space("/") / (1024 ** 3), 2); // GB
$diskUsed = $diskTotal - $diskFree;
$diskUsedPercent = $diskTotal > 0 ? round($diskUsed / $diskTotal * 100) : 0;

// Netzwerkdetails
function getNetworkDetails() {
    $ifaces = ['eth0', 'wlan0'];
    $details = [];
    foreach ($ifaces as $iface) {
        $ip = trim(shell_exec("ip -4 addr show $iface 2>/dev/null | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'"));
        $mac = trim(shell_exec("cat /sys/class/net/$iface/address 2>/dev/null"));
        $status = trim(shell_exec("cat /sys/class/net/$iface/operstate 2>/dev/null"));
        $ssid = $iface === 'wlan0' ? trim(shell_exec("iwgetid -r 2>/dev/null")) : '';
        $signal = $iface === 'wlan0' ? trim(shell_exec("iwconfig wlan0 2>/dev/null | grep 'Link Quality' | awk '{print $2}' | cut -d= -f2")) : '';
        if ($ip || $mac) {
            $details[$iface] = [
                'ip' => $ip,
                'mac' => $mac,
                'status' => $status,
                'ssid' => $ssid,
                'signal' => $signal
            ];
        }
    }
    return $details;
}
$networkDetails = getNetworkDetails();

// Letzter Reboot
$lastReboot = trim(shell_exec("who -b | awk '{print $3, $4}'"));

// Systemauslastung (Prozentwerte)
$cpuLoadPercent = $cpuCores > 0 ? round($load[0] / $cpuCores * 100) : 0;
$ramUsedPercent = $totalMem > 0 ? round($usedMem / $totalMem * 100) : 0;

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Raspberry Pi Systeminfo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #181a1b;
            color: #fff;
            font-family: 'Fira Mono', 'Consolas', 'Menlo', 'Monaco', monospace;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 2em auto;
            padding: 0 1em;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 2em;
            justify-content: center;
        }
        .card {
            background: #222426;
            border-radius: 12px;
            box-shadow: 0 2px 12px #0005;
            padding: 2em 2em 1.5em 2em;
            min-width: 270px;
            flex: 1 1 320px;
            margin-bottom: 2em;
            border: 1px solid #2a7a2e44;
        }
        h1 {
            color: #2aff2a;
            font-size: 2.2em;
            margin-bottom: 1.2em;
            letter-spacing: 1px;
            text-shadow: 0 0 8px #2aff2a33;
        }
        h2 {
            color: #2aff2a;
            font-size: 1.2em;
            margin-bottom: 0.7em;
            letter-spacing: 1px;
        }
        dt {
            color: #2aff2a;
            font-weight: bold;
            margin-top: 1em;
        }
        dd {
            margin-left: 0;
            margin-bottom: .5em;
        }
        .bar {
            background: #333;
            border-radius: 6px;
            overflow: hidden;
            height: 1.2em;
            margin-bottom: .7em;
            box-shadow: 0 1px 4px #0003 inset;
        }
        .bar-inner {
            height: 100%;
            background: linear-gradient(90deg, #2aff2a, #1e7a1e);
            transition: width 0.5s;
        }
        .bar-inner.cpu { background: linear-gradient(90deg, #2aff2a, #ffeb3b, #ff2a2a); }
        .bar-inner.ram { background: linear-gradient(90deg, #2aff2a, #ffeb3b, #ff2a2a); }
        .bar-inner.disk { background: linear-gradient(90deg, #2aff2a, #ffeb3b, #ff2a2a); }
        .terminal-box {
            background: #181a1b;
            border: 1px solid #2a7a2e44;
            border-radius: 8px;
            padding: 1em 1.5em;
            margin-bottom: 1em;
            font-size: 1em;
            box-shadow: 0 1px 6px #0002;
        }
        .network-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1em;
        }
        .network-table th, .network-table td {
            border-bottom: 1px solid #2a7a2e33;
            padding: 0.4em 0.7em;
            text-align: left;
        }
        .network-table th {
            color: #2aff2a;
            font-weight: bold;
            background: #232526;
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
            background: linear-gradient(180deg, #2aff2a, #ffeb3b, #ff2a2a);
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
        @media (max-width: 900px) {
            .cards { flex-direction: column; align-items: stretch; }
            .card { min-width: 0; }
        }
    </style>
</head>
<body>
<div class="container">
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
        <!-- Netzwerk Card -->
        <div class="card">
            <h2>Netzwerk</h2>
            <table class="network-table">
                <tr><th>Interface</th><th>Status</th><th>IP</th><th>MAC</th><th>SSID</th><th>Signal</th></tr>
                <?php foreach ($networkDetails as $iface => $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($iface) ?></td>
                        <td><?= htmlspecialchars($d['status']) ?></td>
                        <td><?= htmlspecialchars($d['ip']) ?></td>
                        <td><?= htmlspecialchars($d['mac']) ?></td>
                        <td><?= htmlspecialchars($d['ssid']) ?></td>
                        <td><?= htmlspecialchars($d['signal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <!-- Auslastung Card -->
        <div class="card">
            <h2>Systemauslastung</h2>
            <div class="terminal-box">
                <b>CPU-Last:</b> <?= $cpuLoadPercent ?>% (<?= implode(' / ', $load) ?>)<br>
                <div class="bar"><div class="bar-inner cpu" style="width: <?= $cpuLoadPercent ?>%"></div></div>
                <b>RAM benutzt:</b> <?= $usedMem ?> MB / <?= $totalMem ?> MB (<?= $ramUsedPercent ?>%)<br>
                <div class="bar"><div class="bar-inner ram" style="width: <?= $ramUsedPercent ?>%"></div></div>
                <b>Festplatte:</b> <?= $diskUsed ?> GB / <?= $diskTotal ?> GB (<?= $diskUsedPercent ?>%)<br>
                <div class="bar"><div class="bar-inner disk" style="width: <?= $diskUsedPercent ?>%"></div></div>
            </div>
        </div>
        <!-- Temperatur Card -->
        <div class="card">
            <h2>CPU-Temperatur</h2>
            <div class="terminal-box">
                <b>Aktuell:</b> <?= htmlspecialchars($cpuTemp) ?> °C
                <div class="temp-history">
                    <?php 
                    $maxTemp = max(array_map('floatval', $tempHistory));
                    $minTemp = min(array_map('floatval', $tempHistory));
                    foreach ($tempHistory as $t): 
                        $height = $maxTemp > $minTemp ? 40 + 20 * (floatval($t) - $minTemp) / ($maxTemp - $minTemp) : 50;
                    ?>
                        <div class="temp-bar" style="height: <?= $height ?>px;" title="<?= htmlspecialchars($t) ?> °C"><span><?= htmlspecialchars($t) ?></span></div>
                    <?php endforeach; ?>
                </div>
                <small>Letzte Werte (aktuell rechts)</small>
            </div>
        </div>
    </div>
</div>
</body>
</html>