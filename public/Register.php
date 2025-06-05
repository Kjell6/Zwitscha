<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrieren</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Logo -->
    <div class="login-section logo-section">
        <a href="index.php" class="logo">
            <picture>
                <!-- Dark Mode -->
                <source
                        srcset="assets/zwitscha_dark.png"
                        media="(prefers-color-scheme: dark)"
                >
                <!-- Light Mode (Fallback) -->
                <img
                        src="assets/zwitscha.png"
                        alt="Zwitscha Logo"
                        class="logo-image"
                >
            </picture>
        </a>
    </div>

    <!-- Registrieren Karte -->
    <main class="container">
        <section class="Register">
            <form id="login-form" class="card">
                <label>Benutzername</label>
                <input type="text" name="benutzername">
                <label>Passwort</label>
                <input type="password" name="passwort">
                <!-- Anmelde Button -->
                <a href="index.php"> <!-- An dieser Stelle später vielleicht lieber Verlinkung über JS machen, damit man anmeldedaten prüfen kann und dementsprechent auf die nächste Seite kommt oder nicht -->
                    <button type="button">Registrieren</button>
                </a>
                <p>Falls du bereits einen Account hast und dich anmelden möchest, klicke <a href="Login.php">hier</a>.</p>

            </form>
        </section>
    </main>
</body>
</html>