<?php
// Stellt sicher, dass die Variable $pageTitle immer existiert.
if (!isset($pageTitle)) {
    $pageTitle = 'Zwitscha';
}

// HTTPS-Erkennung für Cloudflare Tunnel korrigieren
if (isset($_SERVER['HTTP_CF_VISITOR'])) {
    $cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
    if ($cf_visitor && $cf_visitor['scheme'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
}
// Alternativ: Cloudflare setzt auch andere Header
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="assets/favicon.png" type="image/png">
<title><?php echo htmlspecialchars($pageTitle); ?></title>

<!-- Globale Stylesheets -->
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- PWA Manifest und Service Worker -->
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#4CAF50"/>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').then(registration => {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, err => {
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }
</script> 