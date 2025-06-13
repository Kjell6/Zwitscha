<?php
session_start();
require_once __DIR__ . '/php/NutzerVerwaltung.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = isset($_POST['benutzername']) ? trim($_POST['benutzername']) : '';
    $passwort = isset($_POST['passwort']) ? $_POST['passwort'] : '';

    if (empty($benutzername) || empty($passwort)) {
        $error = 'Benutzername und Passwort sind erforderlich.';
    } else {
        // NutzerVerwaltung instanziieren und Login versuchen
        $nutzerVerwaltung = new NutzerVerwaltung();
        $user = $nutzerVerwaltung->authenticateUser($benutzername, $passwort);
        
        if ($user) {
            // Login erfolgreich - Session setzen
            $_SESSION['angemeldet'] = true;
            $_SESSION['eingeloggt'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nutzerName'];
            $_SESSION['ist_admin'] = $user['istAdministrator'];
            
            // Redirect zur ursprünglich gewünschten Seite oder zur Startseite
            $redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: " . $redirectUrl);
            exit;
        } else {
            $error = 'Benutzername oder Passwort ist falsch.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Zwitscha</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/Login.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />
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
    <section class="Login">
        <form id="login-form" class="card" method="POST" action="">
            <label for="benutzername">Benutzername</label>
            <input type="text" name="benutzername" id="benutzername" required 
                   value="<?php echo isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : ''; ?>" />

            <label for="passwort">Passwort</label>
            <input type="password" name="passwort" id="passwort" required />

            <button type="submit">Anmelden</button>

            <?php if (!empty($error)): ?>
                <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <p>Falls du noch keinen Account hast und dich registrieren möchtest, klicke
                <a href="Register.php">hier</a>.
            </p>
        </form>
    </section>
</main>
</body>
</html>