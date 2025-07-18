<?php
    session_start();
    require_once __DIR__ . '/php/NutzerVerwaltung.php';

// === Initialisierung ===
    $error = '';
    $successMessage = '';

    // Prüfen, ob eine Erfolgsnachricht von der Registrierung übergeben wurde
    if (isset($_GET['message'])) {
        $successMessage = htmlspecialchars($_GET['message']);
    }

// === POST-Request-Handling für Login ===
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

                if (isset($_POST['remember_me'])) {
                    $nutzerVerwaltung->createRememberToken($user['id']);
                }
                
                // Redirect zur ursprünglich gewünschten Seite oder zur Startseite
                $redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                header("Location: " . $redirectUrl);
                exit;
            } else {
                $error = 'Benutzername oder Passwort ist falsch.';
            }
        }
    }
    $pageTitle = 'Anmelden - Zwitscha';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include 'global-header.php'; ?>
    <link rel="stylesheet" href="css/Login.css" />
</head>
<!-- === AUTH BODY === -->
<body class="auth-body">
    <!-- === AUTH CONTAINER === -->
    <div class="auth-container">
        <!-- === AUTH FORM CONTAINER === -->
        <div class="auth-form-container">
            <!-- === LOGO SECTION === -->
            <div class="logo-section">
                <img src="assets/favicon.png" alt="Zwitscha Logo" class="logo-image" />
            </div>

            <!-- === AUTH FORM === -->
            <form id="login-form" class="auth-form" method="POST" action="">
                <!-- === AUTH HEADER === -->
                <div class="auth-header">
                    <h1>Willkommen zurück</h1>
                    <p>Melde dich mit deinem Account an</p>
                </div>

                <!-- === ALERT MESSAGES === -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?= $successMessage ?></span>
                    </div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- === FORM GROUPS === -->
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

                <!-- === REMEMBER ME === -->
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember_me" name="remember_me" value="1">
                    <label for="remember_me">Angemeldet bleiben</label>
                </div>

                <!-- === SUBMIT BUTTON === -->
                <button type="submit" class="auth-button">
                    Anmelden
                </button>

                <!-- === AUTH FOOTER === -->
                <div class="auth-footer">
                    <p>Noch keinen Account?</p>
                    <a href="Register.php" class="auth-link">Jetzt registrieren</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>