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

// Festplattenplatz
$diskTotal = round(disk_total_space("/") / (1024 ** 3), 2); // GB
$diskFree = round(disk_free_space("/") / (1024 ** 3), 2); // GB
$diskUsed = $diskTotal - $diskFree;

// IP-Adressen
function getIPAddresses() {
    $ips = [];
    foreach (['eth0', 'wlan0'] as $iface) {
        $ip = trim(shell_exec("ip -4 addr show $iface 2>/dev/null | grep -oP '(?<=inet\\s)\\d+(\\.\\d+){3}'"));
        if ($ip) $ips[$iface] = $ip;
    }
    // Fallback: alle IPs
    if (empty($ips)) {
        $all = shell_exec("hostname -I");
        $ips['Alle'] = trim($all);
    }
    return $ips;
}
$ipAddresses = getIPAddresses();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Raspberry Pi Systeminfo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: sans-serif; margin: 2em; background: #f8fafb; }
        .info-box { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; padding: 2em 2.5em; max-width: 600px; margin: auto; }
        h1 { color: #2a7a2e; }
        dt { font-weight: bold; margin-top: 1em; }
        dd { margin-left: 0; margin-bottom: .5em; }
        .ips { margin: 0; padding: 0; }
        .ips li { list-style: none; }
        .disk { margin-bottom: 1em; }
    </style>
</head>
<body>
<div class="info-box">
    <h1>Raspberry Pi Systeminfo</h1>
    <dl>
        <dt>Betriebssystem:</dt>
        <dd><?= htmlspecialchars($os) ?></dd>
        <dt>Hostname:</dt>
        <dd><?= htmlspecialchars($hostname) ?></dd>
        <dt>Systemzeit:</dt>
        <dd><?= htmlspecialchars($datetime) ?></dd>
        <dt>Uptime:</dt>
        <dd><?= htmlspecialchars(trim($uptime)) ?></dd>
        <dt>PHP-Version:</dt>
        <dd><?= htmlspecialchars($phpVersion) ?></dd>
        <dt>CPU-Kerne:</dt>
        <dd><?= $cpuCores ?></dd>
        <dt>CPU-Temperatur:</dt>
        <dd><?= htmlspecialchars($cpuTemp) ?> °C</dd>
        <dt>CPU-Last (1/5/15 Min):</dt>
        <dd><?= implode(' / ', $load) ?></dd>
        <dt>RAM gesamt:</dt>
        <dd><?= $totalMem ?> MB</dd>
        <dt>RAM benutzt:</dt>
        <dd><?= $usedMem ?> MB</dd>
        <dt>RAM frei:</dt>
        <dd><?= $freeMem ?> MB</dd>
        <dt>Festplattenplatz:</dt>
        <dd class="disk">
            Gesamt: <?= $diskTotal ?> GB<br>
            Belegt: <?= $diskUsed ?> GB<br>
            Frei: <?= $diskFree ?> GB
        </dd>
        <dt>IP-Adresse(n):</dt>
        <dd>
            <ul class="ips">
                <?php foreach ($ipAddresses as $iface => $ip): ?>
                    <li><?= htmlspecialchars($iface) ?>: <?= htmlspecialchars($ip) ?></li>
                <?php endforeach; ?>
            </ul>
        </dd>
    </dl>
</div>
</body>
</html>