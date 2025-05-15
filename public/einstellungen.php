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
  </section>
</main>

<footer>
  <p>&copy; 2025 Zwitscha</p>
</footer>

<?php include 'footerMobile.php'; ?>

</body>
</html>
