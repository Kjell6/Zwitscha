<?php
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    require_once __DIR__ . '/php/session_helper.php';

// === Initialisierung ===
    requireLogin();

    $nutzerVerwaltung = new NutzerVerwaltung();
    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);

    if (!$currentUser) {
        die('Benutzer nicht gefunden.');
    }

    $message = '';
    $error = '';

// === POST-Request-Handling für Einstellungen ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete-account'])) {
            // Account löschen
            $success = $nutzerVerwaltung->deleteUser($currentUserId);
            if ($success) {
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
            } elseif (strlen($newName) < 3) {
                $error = "Der Name muss mindestens 3 Zeichen lang sein.";
            } elseif (strlen($newName) > 20) {
                $error = "Der Name darf maximal 20 Zeichen lang sein.";
            } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $newName)) {
                $error = "Der Name darf nur Buchstaben, Zahlen, Punkte, Unterstriche und Bindestriche enthalten.";
            } else {
                $success = $nutzerVerwaltung->updateUserName($currentUserId, $newName);
                if ($success) {
                    $message = "Name wurde erfolgreich geändert.";
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
            } elseif (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]+$/', $newPassword)) {
                $error = "Das neue Passwort enthält unerlaubte Zeichen.";
            } elseif (strlen($newPassword) < 6) {
                $error = "Das neue Passwort muss mindestens 6 Zeichen lang sein.";
            } elseif (strlen($newPassword) > 100) {
                $error = "Das neue Passwort darf maximal 100 Zeichen lang sein.";
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
                } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) { // 2 MB Limit 
                    $error = "Das Bild ist zu groß. Maximal 2 MB sind erlaubt.";
                } else {
                    $imageData = file_get_contents($_FILES['avatar']['tmp_name']);
                    if ($imageData === false) {
                        $error = "Fehler beim Lesen der Bilddatei.";
                    } else {
                        $success = $nutzerVerwaltung->updateProfileImage($currentUserId, $imageData);
                        if ($success) {
                            $message = "Profilbild wurde erfolgreich aktualisiert.";
                            $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
                        } else {
                            $error = "Fehler beim Speichern des Profilbilds in der Datenbank.";
                        }
                    }
                }
            } else {
                $error = "Bitte wählen Sie eine Datei aus.";
            }
        } elseif (isset($_POST['update-notification-settings'])) {
            // Benachrichtigungseinstellungen aktualisieren
            $notificationTypes = [
                'new_post_from_followed_user',
                'new_comment_on_own_post',
                'new_reply_to_own_comment',
                'mention_in_post'
            ];
            
            $all_successful = true;
            foreach ($notificationTypes as $type) {
                $isEnabled = isset($_POST[$type]);
                $success = $nutzerVerwaltung->updateNotificationSetting($currentUserId, $type, $isEnabled);
                if (!$success) {
                    $all_successful = false;
                }
            }

            if ($all_successful) {
                $message = "Benachrichtigungseinstellungen erfolgreich gespeichert.";
            } else {
                $error = "Fehler beim Speichern der Benachrichtigungseinstellungen.";
            }
        }
    }

    $pageTitle = 'Zwitscha – Einstellungen';
    $notificationSettings = $nutzerVerwaltung->getNotificationSettings($currentUserId);
    // VAPID Public Key für das Frontend bereitstellen
    $vapidPublicKey = $_SERVER['VAPID_PUBLIC_KEY'] ?? '';
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <?php include 'global-header.php'; ?>
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/einstellungen.css" />
    <script src="js/image-compression.js"></script>
    <?php include 'pwa-header.php'; ?>
</head>

<body>

<!-- === HEADER === -->
<?php include 'headerDesktop.php'; ?>

<!-- === MAIN CONTAINER === -->
<main class="container">
    <!-- === SETTINGS CONTAINER === -->
    <section class="settings">
        <h2>Kontoeinstellungen</h2>

        <!-- === FEEDBACK MESSAGES === -->
        <?php if (!empty($message)): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- === NOTIFICATION SETTINGS FORM === -->
        <form id="notification-form" class="card" method="POST">
            <fieldset>
                <legend>Benachrichtigungen</legend>
                <p>Hier kannst du deine Push-Benachrichtigungen verwalten.</p>
                
                <div id="notification-controls">
                    <button type="button" id="enable-notifications-button" class="button">Benachrichtigungen im Browser erlauben</button>
                    <p id="notification-status" style="margin-top: 1rem; font-weight: bold;"></p>
                    
                    <div id="notification-toggles" style="display: none; margin-top: 1rem;">
                        <h4>Benachrichtigungsarten</h4>

                        <div class="notification-setting">
                            <label for="notif-new-post">Jemand, dem du folgst, postet etwas</label>
                            <input type="checkbox" id="notif-new-post" name="new_post_from_followed_user" <?php echo ($notificationSettings['new_post_from_followed_user'] ?? false) ? 'checked' : ''; ?>>
                        </div>

                        <div class="notification-setting">
                            <label for="notif-new-comment">Jemand kommentiert deinen Post</label>
                            <input type="checkbox" id="notif-new-comment" name="new_comment_on_own_post" <?php echo ($notificationSettings['new_comment_on_own_post'] ?? false) ? 'checked' : ''; ?>>
                        </div>

                        <div class="notification-setting">
                            <label for="notif-new-reply">Jemand antwortet auf deinen Kommentar</label>
                            <input type="checkbox" id="notif-new-reply" name="new_reply_to_own_comment" <?php echo ($notificationSettings['new_reply_to_own_comment'] ?? false) ? 'checked' : ''; ?>>
                        </div>

                        <div class="notification-setting">
                            <label for="notif-mention">Jemand erwähnt dich in einem Post oder Kommentar</label>
                            <input type="checkbox" id="notif-mention" name="mention_in_post" <?php echo ($notificationSettings['mention_in_post'] ?? false) ? 'checked' : ''; ?>>
                        </div>

                        <button type="submit" name="update-notification-settings" class="button">Einstellungen speichern</button>
                    </div>
                </div>
            </fieldset>
        </form>

        <!-- === AVATAR FORM === -->
        <form id="avatar-form" method="POST" enctype="multipart/form-data" class="card">
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
                        <img id="avatar-preview-img" src="getImage.php?type=user&id=<?php echo $currentUserId; ?>&t=<?php echo time(); ?>" alt="Vorschau" />
                    </div>

                </div>
            </fieldset>
        </form>

        <!-- === NAME CHANGE FORM === -->
        <form method="POST" class="card">
            <fieldset>
                <legend>Name ändern</legend>
                <label for="new-name">Neuer Name:</label>
                <input type="text" id="new-name" name="new-name" 
                       value="<?php echo htmlspecialchars($currentUser['nutzerName']); ?>" 
                       pattern="[a-zA-Z0-9._-]+"
                       minlength="3" maxlength="20"
                       title="Nur Buchstaben, Zahlen, Punkte, Unterstriche und Bindestriche erlaubt"
                       required />
                <button type="submit" name="change-name" class="button">Speichern</button>
            </fieldset>
        </form>

        <!-- === PASSWORD CHANGE FORM === -->
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
                        pattern="[A-Za-z0-9!@#$%^&*()_+\-=\[\]{};':\\\|,.<>\/?~`]+"
                        minlength="6"
                        maxlength="100"
                        title="Das Passwort muss mindestens 6 Zeichen lang sein und darf Buchstaben, Zahlen und gängige Sonderzeichen enthalten."
                />

                <label for="confirm-password">Neues Passwort bestätigen:</label>
                <input type="password" id="confirm-password" name="confirm-password" required />

                <button type="submit" name="change-password" class="button">Passwort aktualisieren</button>
            </fieldset>
        </form>

        <!-- === ACCOUNT DELETION FORM === -->
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

<script src="js/image-preview.js"></script>
<script type="module">
    import { initializeNotificationManager } from './js/notifications.js';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Avatar-Bildvorschau initialisieren
        initializeAvatarImagePreview(
            'avatar',
            'avatar-preview-img'
        );
        
        // Benachrichtigungs-Manager initialisieren
        const vapidPublicKey = '<?php echo $vapidPublicKey; ?>';
        initializeNotificationManager('enable-notifications-button', 'notification-status', vapidPublicKey, '#notification-toggles');
    });
</script>

</body>
</html>

