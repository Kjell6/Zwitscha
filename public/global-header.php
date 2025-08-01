<?php
// Stellt sicher, dass die Variable $pageTitle immer existiert.
if (!isset($pageTitle)) {
    $pageTitle = 'Zwitscha';
}

// Anti-Caching-Headers setzen
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
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
<meta name="theme-color" content="#f5f5f5"/>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('ServiceWorker erfolgreich registriert, Scope:', registration.scope);
                })
                .catch(error => {
                    console.error('ServiceWorker Registrierung fehlgeschlagen:', error);
                });
        });
    }
</script> 