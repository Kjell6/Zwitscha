<?php
// Dummy-Nutzerliste
$dummyUsers = [
    'max' => '1234',
    'lisa' => 'passwort',
    'admin' => 'admin',
    'user' => 'user'
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = isset($_POST['benutzername']) ? trim($_POST['benutzername']) : '';
    $passwort = isset($_POST['passwort']) ? $_POST['passwort'] : '';

    // Login prüfen
    if (array_key_exists($benutzername, $dummyUsers) && $dummyUsers[$benutzername] === $passwort) {
        header("Location: index.php");
        exit;
    } else {
        $error = "[Dummy] Benutzername oder Passwort ist falsch.";
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
            <input type="text" name="benutzername" id="benutzername" required />

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
