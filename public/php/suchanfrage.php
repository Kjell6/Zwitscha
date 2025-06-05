<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $suchbegriff = trim($_POST['query']);

    //Hier Datenbankzugriff

    echo "<div>Suchergebnis für: <strong>" . htmlspecialchars($suchbegriff) . "</strong></div>";
}
?>

