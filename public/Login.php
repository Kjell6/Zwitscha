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
    <title>Anmelden - Zwitscha</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/Login.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <!-- Login Form -->
        <div class="auth-form-container">
            <div class="logo-section">
                <img src="assets/favicon.png" alt="Zwitscha Logo" class="logo-image" />
            </div>

            <form id="login-form" class="auth-form" method="POST" action="">
                <div class="auth-header">
                    <h1>Willkommen zurück</h1>
                    <p>Melde dich mit deinem Account an</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="benutzername">Benutzername</label>
                    <div class="input-wrapper">
                        <input type="text" name="benutzername" id="benutzername" required 
                               placeholder="Gib deinen Benutzernamen ein"
                               value="<?php echo isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : ''; ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="passwort">Passwort</label>
                    <div class="input-wrapper">
                        <input type="password" name="passwort" id="passwort" required 
                               placeholder="Gib dein Passwort ein" />
                    </div>
                </div>

                <button type="submit" class="auth-button">
                    Anmelden
                </button>

                <div class="auth-footer">
                    <p>Noch keinen Account?</p>
                    <a href="Register.php" class="auth-link">Jetzt registrieren</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>