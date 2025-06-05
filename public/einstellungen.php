<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete-account'])) {
        echo "<p style='color: red; font-weight: bold;'>[Dummy] Account würde jetzt gelöscht werden.</p>";
    } elseif (isset($_POST['change-name'])) {
        $newName = htmlspecialchars(isset($_POST['new-name']) ? $_POST['new-name'] : '');
        echo "<p style='color: green; font-weight: bold;'>[Dummy] Name würde jetzt zu $newName geändert werden.</p>";
    } elseif (isset($_POST['change-password'])) {
        echo "<p style='color: blue; font-weight: bold;'>[Dummy] Passwort würde jetzt geändert werden.</p>";
    } elseif (isset($_POST['change-avatar'])) {
        echo "<p style='color: orange; font-weight: bold;'>[Dummy] Profilbild würde jetzt aktualisiert werden.</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zwitscha – Einstellungen</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />

    <!-- Globales Styling -->
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/header.css" />

    <!-- Seitenspezifisches Styling -->
    <link rel="stylesheet" href="css/einstellungen.css" />
</head>

<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <section class="settings">
        <h2>Kontoeinstellungen</h2>

        <!-- Profilbild ändern -->
        <form method="POST" enctype="multipart/form-data" class="card">
            <fieldset>
                <legend>Profilbild ändern</legend>
                <div class="form-row compact-avatar-row">

                    <div class="image-upload">
                        <label for="avatar" class="image-upload-label">
                            <i class="bi bi-image"></i> Neues Profilbild
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;"/>
                    </div>
                    <div class="avatar-preview">
                        <img src="assets/placeholder-profilbild-2.png" alt="Profilbild-Vorschau" />
                    </div>
                </div>
                <button type="submit" name="change-avatar" class="button">Bild aktualisieren</button>
            </fieldset>
        </form>

        <!-- Name ändern -->
        <form method="POST" class="card">
            <fieldset>
                <legend>Name ändern</legend>
                <label for="new-name">Neuer Name:</label>
                <input type="text" id="new-name" name="new-name" required />
                <button type="submit" name="change-name" class="button">Speichern</button>
            </fieldset>
        </form>

        <!-- Passwort ändern -->
        <form method="POST" class="card">
            <fieldset>
                <legend>Passwort ändern</legend>

                <label for="current-password">Aktuelles Passwort:</label>
                <input type="password" id="current-password" name="current-password" required />

                <label for="new-password">Neues Passwort:</label>
                <input type="password" id="new-password" name="new-password" required />

                <label for="confirm-password">Neues Passwort bestätigen:</label>
                <input type="password" id="confirm-password" name="confirm-password" required />

                <button type="submit" name="change-password" class="button">Passwort aktualisieren</button>
            </fieldset>
        </form>

        <!-- Account löschen -->
        <form method="POST" class="card" onsubmit="return confirm('Möchtest du deinen Account wirklich löschen?');">
            <fieldset>
                <legend>Account löschen</legend>
                <p>Diese Aktion kann nicht rückgängig gemacht werden.</p>
                <button type="submit" name="delete-account" class="button danger">Account löschen</button>
            </fieldset>
        </form>
    </section>
</main>

<footer>
    <p>&copy; 2025 Zwitscha</p>
</footer>

<?php include 'footerMobile.php'; ?>

</body>
</html>
