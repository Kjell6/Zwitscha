<?php
require_once __DIR__ . '/php/NutzerVerwaltung.php';

$message = '';
$error = '';
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = isset($_POST['benutzername']) ? trim($_POST['benutzername']) : '';
    $passwort = isset($_POST['passwort']) ? $_POST['passwort'] : '';

    if (empty($benutzername) || empty($passwort)) {
        $error = 'Bitte Benutzername und Passwort eingeben.';
    } else {
        // NutzerVerwaltung instanziieren und Registrierung versuchen
        $nutzerVerwaltung = new NutzerVerwaltung();
        $result = $nutzerVerwaltung->registerUser($benutzername, $passwort);
        
        if ($result['success']) {
            $message = $result['message'] . ' Du wirst in 3 Sekunden weitergeleitet.';
            $redirect = true;
            header("Refresh: 3; url=Login.php");
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrieren Zwitscha</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />

    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/Login.css" />

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />

    <style>
        /* Fix für Containerbreite */
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 1rem;
        }
    </style>
</head>
<body>
<div class="login-section logo-section">
    <a href="index.php" class="logo">
        <picture>
            <source srcset="assets/zwitscha_dark.png" media="(prefers-color-scheme: dark)" />
            <img src="assets/zwitscha.png" alt="Zwitscha Logo" class="logo-image" />
        </picture>
    </a>
</div>

<main class="container">
    <section class="Register">
        <form id="register-form" class="card" method="POST" action="">
            <label for="benutzername">Benutzername</label>
            <input type="text" name="benutzername" id="benutzername" required 
                   value="<?php echo isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : ''; ?>" />

            <label for="passwort">Passwort</label>
            <input type="password" name="passwort" id="passwort" required />

            <button type="submit">Registrieren</button>

            <?php if ($message): ?>
                <p style="color: green; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
            <?php elseif ($error): ?>
                <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <p>Falls du bereits einen Account hast und dich anmelden möchtest, klicke
                <a href="Login.php">hier</a>.
            </p>
        </form>
    </section>
</main>
</body>
</html>
