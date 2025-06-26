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
    } elseif (strlen($benutzername) < 3 || strlen($benutzername) > 20) {
        $error = 'Benutzername muss zwischen 3 und 20 Zeichen lang sein.';
    } elseif (strlen($passwort) < 6 || strlen($passwort) > 100) {
        $error = 'Passwort muss zwischen 6 und 100 Zeichen lang sein.';
    } else {
        // NutzerVerwaltung instanziieren und Registrierung versuchen
        $nutzerVerwaltung = new NutzerVerwaltung();
        $result = $nutzerVerwaltung->registerUser($benutzername, $passwort);
        
        if ($result['success']) {
            $successMessage = urlencode('Registrierung erfolgreich! Du kannst dich jetzt anmelden.');
            header("Location: Login.php?message=" . $successMessage);
            exit(); 
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
    <title>Registrieren - Zwitscha</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/Login.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <!-- Register Form -->
        <div class="auth-form-container">
            <div class="logo-section">
                    <img src="assets/favicon.png" alt="Zwitscha Logo" class="logo-image" />
            </div>
            <form id="register-form" class="auth-form" method="POST" action="">
                <div class="auth-header">
                    <h1>Willkommen bei Zwitscha</h1>
                    <p>Erstelle deinen Account und werde Teil der Community</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="benutzername">Benutzername</label>
                    <div class="input-wrapper">
                        <input type="text" name="benutzername" id="benutzername" required 
                               placeholder="Wähle einen Benutzernamen"
                               minlength="3" maxlength="20"
                               value="<?php echo isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : ''; ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="passwort">Passwort</label>
                    <div class="input-wrapper">
                        <input type="password" name="passwort" id="passwort" required 
                               placeholder="Erstelle ein sicheres Passwort"
                               minlength="6" maxlength="100" />
                    </div>
                </div>

                <button type="submit" class="auth-button">
                    Registrieren
                </button>

                <div class="auth-footer">
                    <p>Bereits einen Account?</p>
                    <a href="Login.php" class="auth-link">Jetzt anmelden</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
