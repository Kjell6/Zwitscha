<?php
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete-account'])) {
        $message = "[Dummy] Account würde jetzt gelöscht werden.";
    } elseif (isset($_POST['change-name'])) {
        $newName = htmlspecialchars($_POST['new-name'] ?? '');
        $message = "[Dummy] Name würde jetzt zu $newName geändert werden.";
    } elseif (isset($_POST['change-password'])) {
        $currentPassword = $_POST['current-password'] ?? '';
        $newPassword = $_POST['new-password'] ?? '';
        $confirmPassword = $_POST['confirm-password'] ?? '';

        // Dummy-Passwortüberprüfung
        $dummyCurrentPassword = '123456'; // Beispiel: angenommenes korrektes Passwort

        if ($currentPassword !== $dummyCurrentPassword) {
            $error = "[Dummy] Aktuelles Passwort ist falsch.";
        } elseif (!ctype_alnum($newPassword)) {
            $error = "Das neue Passwort darf nur Buchstaben und Zahlen enthalten.";
        } elseif (strlen($newPassword) < 6) {
            $error = "Das neue Passwort muss mindestens 6 Zeichen lang sein.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Die neuen Passwörter stimmen nicht überein.";
        } else {
            $message = "[Dummy] Passwort wurde erfolgreich geändert.";
        }
    } elseif (isset($_POST['change-avatar'])) {
        $message = "[Dummy] Profilbild würde jetzt aktualisiert werden.";
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

    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/einstellungen.css" />
</head>

<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <section class="settings">
        <h2>Kontoeinstellungen</h2>

        <?php if (!empty($message)): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

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
                <input
                        type="password"
                        id="new-password"
                        name="new-password"
                        required
                        pattern="[A-Za-z0-9]+"
                        minlength="6"
                        title="Nur Buchstaben und Zahlen erlaubt. Mindestens 6 Zeichen."
                />

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
