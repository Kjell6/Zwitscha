<?php
require_once __DIR__ . '/php/NutzerVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';

// Überprüfen ob Nutzer angemeldet ist
requireLogin();

$nutzerVerwaltung = new NutzerVerwaltung();

// Aktuelle User-ID aus Session holen
$currentUserId = getCurrentUserId();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

if (!$currentUser) {
    die('Benutzer nicht gefunden.');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete-account'])) {
        // Account löschen
        $success = $nutzerVerwaltung->deleteUser($currentUserId);
        if ($success) {
            // Nach dem Löschen zur Login-Seite umleiten
            header('Location: Login.php?message=account_deleted');
            exit;
        } else {
            $error = "Fehler beim Löschen des Accounts.";
        }
        
    } elseif (isset($_POST['change-name'])) {
        // Name ändern
        $newName = trim($_POST['new-name'] ?? '');
        if (empty($newName)) {
            $error = "Der Name darf nicht leer sein.";
        } elseif (strlen($newName) < 2) {
            $error = "Der Name muss mindestens 2 Zeichen lang sein.";
        } elseif (strlen($newName) > 50) {
            $error = "Der Name darf maximal 50 Zeichen lang sein.";
        } else {
            $success = $nutzerVerwaltung->updateUserName($currentUserId, $newName);
            if ($success) {
                $message = "Name wurde erfolgreich geändert.";
                // Aktuelle Nutzerdaten neu laden
                $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
            } else {
                $error = "Fehler beim Ändern des Namens.";
            }
        }
        
    } elseif (isset($_POST['change-password'])) {
        // Passwort ändern
        $currentPassword = $_POST['current-password'] ?? '';
        $newPassword = $_POST['new-password'] ?? '';
        $confirmPassword = $_POST['confirm-password'] ?? '';

        if (!$nutzerVerwaltung->verifyCurrentPassword($currentUserId, $currentPassword)) {
            $error = "Das aktuelle Passwort ist falsch.";
        } elseif (!ctype_alnum($newPassword)) {
            $error = "Das neue Passwort darf nur Buchstaben und Zahlen enthalten.";
        } elseif (strlen($newPassword) < 6) {
            $error = "Das neue Passwort muss mindestens 6 Zeichen lang sein.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Die neuen Passwörter stimmen nicht überein.";
        } else {
            $success = $nutzerVerwaltung->updatePassword($currentUserId, $newPassword);
            if ($success) {
                $message = "Passwort wurde erfolgreich geändert.";
            } else {
                $error = "Fehler beim Ändern des Passworts.";
            }
        }
        
    } elseif (isset($_POST['change-avatar'])) {
        // Profilbild ändern
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // Datei-Validierung
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
                $error = "Nur JPEG, PNG, GIF und WebP Dateien sind erlaubt.";
            } elseif ($_FILES['avatar']['size'] > 50 * 1024 * 1024) { // 50 MB Limit
                $error = "Das Bild ist zu groß. Maximal 50 MB sind erlaubt.";
            } else {
                // Upload-Verzeichnis erstellen falls es nicht existiert
                $uploadDir = __DIR__ . '/assets/uploads/profile/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Sicherer Dateiname generieren
                $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $fileName = 'profile_' . $currentUserId . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                $relativePath = 'assets/uploads/profile/' . $fileName;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                    // Altes Profilbild löschen (falls es kein Placeholder ist)
                    $oldImage = $currentUser['profilBild'];
                    if ($oldImage && strpos($oldImage, 'placeholder') === false && file_exists(__DIR__ . '/' . $oldImage)) {
                        unlink(__DIR__ . '/' . $oldImage);
                    }
                    
                    $success = $nutzerVerwaltung->updateProfileImage($currentUserId, $relativePath);
                    if ($success) {
                        $message = "Profilbild wurde erfolgreich aktualisiert.";
                        // Aktuelle Nutzerdaten neu laden
                        $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
                    } else {
                        $error = "Fehler beim Speichern des Profilbilds in der Datenbank.";
                        // Hochgeladene Datei wieder löschen
                        if (file_exists($targetPath)) {
                            unlink($targetPath);
                        }
                    }
                } else {
                    $error = "Fehler beim Hochladen der Datei.";
                }
            }
        } else {
            $error = "Bitte wählen Sie eine Datei aus.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zwitscha – Einstellungen</title>
    <link rel="icon" href="assets/favicon.png" type="image/png" />

    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/einstellungen.css" />
    <script src="js/image-compression.js"></script>
</head>

<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <section class="settings">
        <h2>Kontoeinstellungen</h2>

        <?php if (!empty($message)): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Profilbild ändern -->
        <form method="POST" enctype="multipart/form-data" class="card">
            <fieldset>
                <legend>Profilbild ändern</legend>
                <div class="avatar-form-layout">
                    
                    <div class="avatar-buttons">
                        <label for="avatar" class="button button-secondary">
                            <i class="bi bi-image"></i> Datei auswählen
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;"/>
                        <button type="submit" name="change-avatar" class="button">Bild aktualisieren</button>
                    </div>

                    <div class="avatar-preview">
                        <img id="avatar-preview-img" src="<?php echo htmlspecialchars($currentUser['profilBild'] ?: 'assets/placeholder-profilbild-2.png'); ?>" alt="Vorschau" />
                    </div>

                </div>
            </fieldset>
        </form>

        <!-- Name ändern -->
        <form method="POST" class="card">
            <fieldset>
                <legend>Name ändern</legend>
                <label for="new-name">Neuer Name:</label>
                <input type="text" id="new-name" name="new-name" 
                       value="<?php echo htmlspecialchars($currentUser['nutzerName']); ?>" 
                       required />
                <button type="submit" name="change-name" class="button">Speichern</button>
            </fieldset>
        </form>

        <!-- Passwort ändern -->
        <form method="POST" class="card">
            <fieldset>
                <legend>Passwort ändern</legend>

                <label for="current-password">Aktuelles Passwort:</label>
                <input type="password" id="current-password" name="current-password" required />

                <label for="new-password">Neues Passwort:</label>
                <input
                        type="password"
                        id="new-password"
                        name="new-password"
                        required
                        pattern="[A-Za-z0-9]+"
                        minlength="6"
                        title="Nur Buchstaben und Zahlen erlaubt. Mindestens 6 Zeichen."
                />

                <label for="confirm-password">Neues Passwort bestätigen:</label>
                <input type="password" id="confirm-password" name="confirm-password" required />

                <button type="submit" name="change-password" class="button">Passwort aktualisieren</button>
            </fieldset>
        </form>

        <!-- Account löschen -->
        <form method="POST" class="card" onsubmit="return confirm('Möchtest du deinen Account wirklich löschen?');">
            <fieldset>
                <legend>Account löschen</legend>
                <p>Diese Aktion kann nicht rückgängig gemacht werden.</p>
                <button type="submit" name="delete-account" class="button danger">Account löschen</button>
            </fieldset>
        </form>
    </section>
</main>

<footer>
    <p>&copy; 2025 Zwitscha</p>
</footer>

<?php include 'footerMobile.php'; ?>

<script>
    // Live-Vorschau für Profilbild-Upload
    document.getElementById('avatar').addEventListener('change', async function(event) {
        const file = event.target.files[0];
        const previewImg = document.getElementById('avatar-preview-img');
        
        if (file) {
            // Prüfe, ob es ein gültiges Bildformat ist - Safari iOS kompatibel
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            const fileType = file.type.toLowerCase();
            const isValidType = allowedTypes.some(type => 
                fileType === type || 
                (type === 'image/jpeg' && fileType === 'image/jpg') ||
                (type === 'image/jpg' && fileType === 'image/jpeg')
            );
            
            if (!isValidType && file.size > 0) {
                alert('Nur JPEG, PNG, GIF und WebP Dateien sind erlaubt.');
                event.target.value = ''; // Input zurücksetzen
                return;
            }
            
            try {
                // Automatische Bildkomprimierung vor Upload
                await window.imageCompressor.handleFileInput(event.target, previewImg, (compressedFile) => {
                    const originalSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    const compressedSizeMB = (compressedFile.size / (1024 * 1024)).toFixed(2);
                    console.log(`Profilbild komprimiert: ${originalSizeMB}MB → ${compressedSizeMB}MB`);
                });
            } catch (error) {
                alert('Fehler bei der Bildverarbeitung: ' + error.message);
                event.target.value = '';
                previewImg.src = "<?php echo htmlspecialchars($currentUser['profilBild'] ?: 'assets/placeholder-profilbild-2.png'); ?>";
            }
        } else {
            // Wenn keine Datei ausgewählt, zurück zum ursprünglichen Bild
            previewImg.src = "<?php echo htmlspecialchars($currentUser['profilBild'] ?: 'assets/placeholder-profilbild-2.png'); ?>";
        }
    });
</script>

</body>
</html>

