<?php
    require_once __DIR__ . '/php/NutzerVerwaltung.php';

// === Initialisierung ===
    $message = '';
    $error = '';
    $redirect = false;

// === POST-Request-Handling für Registrierung ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $benutzername = isset($_POST['benutzername']) ? trim($_POST['benutzername']) : '';
        $passwort = isset($_POST['passwort']) ? $_POST['passwort'] : '';

        if (empty($benutzername) || empty($passwort)) {
            $error = 'Bitte Benutzername und Passwort eingeben.';
        } elseif (strlen($benutzername) < 3 || strlen($benutzername) > 20) {
            $error = 'Benutzername muss zwischen 3 und 20 Zeichen lang sein.';
        } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $benutzername)) {
            $error = 'Benutzername darf nur Buchstaben, Zahlen, Punkte, Unterstriche und Bindestriche enthalten.';
        } elseif (strlen($passwort) < 6 || strlen($passwort) > 100) {
            $error = 'Passwort muss zwischen 6 und 100 Zeichen lang sein.';
        } elseif (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]+$/', $passwort)) {
            $error = 'Passwort enthält unerlaubte Zeichen.';
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
    <title>Registrierung - Zwitscha</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/Login.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            <form id="register-form" class="auth-form" method="POST" action="">
                <!-- === AUTH HEADER === -->
                <div class="auth-header">
                    <h1>Willkommen bei Zwitscha</h1>
                    <p>Erstelle deinen Account und werde Teil der Community</p>
                </div>

                <!-- === ALERT MESSAGES === -->
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

                <!-- === FORM GROUPS === -->
                <div class="form-group">
                    <label for="benutzername">Benutzername</label>
                    <div class="input-wrapper">
                        <input type="text" name="benutzername" id="benutzername" required 
                               placeholder="Wähle einen Benutzernamen"
                               pattern="[a-zA-Z0-9._-]+"
                               minlength="3" maxlength="20"
                               title="Nur Buchstaben, Zahlen, Punkte, Unterstriche und Bindestriche erlaubt"
                               value="<?php echo isset($_POST['benutzername']) ? htmlspecialchars($_POST['benutzername']) : ''; ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="passwort">Passwort</label>
                    <div class="input-wrapper">
                        <input type="password" name="passwort" id="passwort" required 
                               placeholder="Erstelle ein sicheres Passwort"
                               minlength="6" maxlength="100"
                               pattern="[A-Za-z0-9!@#$%^&*()_+\-=\[\]{};':\\|,.<>/?~`]+"
                               title="Das Passwort darf Buchstaben, Zahlen und gängige Sonderzeichen enthalten." />
                    </div>
                </div>

                <!-- === SUBMIT BUTTON === -->
                <button type="submit" class="auth-button">
                    Registrieren
                </button>

                <!-- === AUTH FOOTER === -->
                <div class="auth-footer">
                    <p>Bereits einen Account?</p>
                    <a href="Login.php" class="auth-link">Jetzt anmelden</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
