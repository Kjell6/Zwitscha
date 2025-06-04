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
        <form id="change-avatar-form" class="card">
            <fieldset>
                <legend>Profilbild ändern</legend>
                <div class="form-row compact-avatar-row">
                    <div class="form-group">
                        <label for="avatar">Neues Profilbild:</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" />
                    </div>
                    <div class="avatar-preview">
                        <img src="assets/placeholder-profilbild-2.png" alt="Profilbild-Vorschau" />
                    </div>
                </div>
                <button type="submit" class="button">Bild aktualisieren</button>
            </fieldset>
        </form>


        <!-- Name ändern -->
        <form id="change-name-form" class="card">
            <fieldset>
                <legend>Name ändern</legend>
                <label for="new-name">Neuer Name:</label>
                <input type="text" id="new-name" name="new-name" required />
                <button type="submit" class="button">Speichern</button>
            </fieldset>
        </form>

        <!-- Passwort ändern -->
        <form id="change-password-form" class="card">
            <fieldset>
                <legend>Passwort ändern</legend>

                <label for="current-password">Aktuelles Passwort:</label>
                <input type="password" id="current-password" name="current-password" required />

                <label for="new-password">Neues Passwort:</label>
                <input type="password" id="new-password" name="new-password" required />

                <label for="confirm-password">Neues Passwort bestätigen:</label>
                <input type="password" id="confirm-password" name="confirm-password" required />

                <button type="submit" class="button">Passwort aktualisieren</button>
            </fieldset>
        </form>

        <!-- Account löschen -->
        <form id="delete-account-form" class="card danger">
            <fieldset>
                <legend>Account löschen</legend>
                <p>Bist du sicher, dass du deinen Account dauerhaft löschen möchtest?</p>
                <button type="submit" class="button danger-button">Account löschen</button>
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
