<?php
require_once __DIR__ . '/php/PostVerwaltung.php';

// Verwaltung instanziieren
$postRepository = new PostVerwaltung();


// ---- POST Request Handling für neue Posts ----
$feedbackMessage = '';
$feedbackType = ''; // success, error, info

// ---- POST Request Handling für ERSTELLEN ----
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'create_post'
) {
    $postText   = trim($_POST['post_text'] ?? '');
    // Später durch echte User-ID aus Session ersetzen, z.B. $_SESSION['user_id']
    $currentUserId = 1; // Dummy-ID des eingeloggten Nutzers (beispielNutzer)

    if (empty($postText)) {
        $feedbackMessage = 'Post-Text darf nicht leer sein.';
        $feedbackType    = 'error';

    } elseif (strlen($postText) > 300) {
        $feedbackMessage = 'Post-Text darf maximal 300 Zeichen lang sein.';
        $feedbackType    = 'error';

    } else {
        $imagePath = null;
        // Prüfen, ob ein Bild hochgeladen wurde und fehlerfrei ist
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Sicherer Dateiname, um Überschreibungen und Sicherheitsrisiken zu vermeiden
            $fileName = uniqid('', true) . '_' . basename($_FILES['post_image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $targetPath)) {
                $imagePath = 'assets/uploads/' . $fileName;
            } else {
                $feedbackMessage = 'Bild konnte nicht gespeichert werden.';
                $feedbackType = 'error';
            }
        } elseif (isset($_FILES['post_image']) && $_FILES['post_image']['error'] !== UPLOAD_ERR_NO_FILE) {
             // Fehlerbehandlung für andere Upload-Fehler
            $feedbackMessage = 'Fehler beim Hochladen des Bildes.';
            $feedbackType    = 'error';
        }

        // Post nur erstellen, wenn kein Fehler beim Bild-Upload aufgetreten ist
        if ($feedbackType !== 'error') {
            $success = $postRepository->createPost($currentUserId, $postText, $imagePath);

            if ($success) {
                $feedbackMessage = 'Post erfolgreich angelegt.';
                $feedbackType    = 'success';
                // Leere die Post-Variable, um doppeltes Senden zu verhindern (Post/Redirect/Get Pattern)
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $feedbackMessage = 'Fehler beim Speichern des Posts in der Datenbank.';
                $feedbackType    = 'error';
            }
        }
    }
}


// ---- Dynamische Inhalte: Posts laden ----
// Später: Aktuellen Benutzer aus der Session oder Authentifizierung holen
$currentUser = 'Max Mustermann';
$showFollowedOnly = isset($_GET['filter']) && $_GET['filter'] === 'followed';

$loadingState = $_GET['state'] ?? 'data'; // data, empty, error (für Testing)

// DUMMY-BENUTZERDATEN (später aus Session) für das Laden der Posts
$currentUserIdForPosts = 1;

// Posts aus der Datenbank laden
if ($showFollowedOnly) {
    $posts = $postRepository->getFollowedPosts($currentUserIdForPosts);
} else {
    $posts = $postRepository->getAllPosts($currentUserIdForPosts);
}

// POST Request für Reaktionen und Löschen
// Später: Hier Datenbankoperationen für Reaktionen und Löschen implementieren
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_reaction') {
        // Hier in Datenbank speicern/entfernen
        // Später: Datenbank-Interaktion zum Togglen der Reaktion implementieren
        print_r($_POST); // Dummy-Ausgabe der POST-Daten für Debugging
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_post') {
        // Hier aus Datenbank löschen
        // Später: Datenbank-Interaktion zum Löschen des Posts implementieren
        print_r($_POST); // Dummy-Ausgabe der POST-Daten für Debugging
    }
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <title>Startseite</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<?php include 'headerDesktop.php'; ?>

<div class="main-content">
    <!-- Feedback Messages -->
    <?php if (!empty($feedbackMessage)): ?>
        <div class="feedback-message feedback-<?php echo $feedbackType; ?>">
            <?php echo htmlspecialchars($feedbackMessage); ?>
        </div>
    <?php endif; ?>

    <!-- Post Erstellung Form -->
    <form method="POST" enctype="multipart/form-data" class="post-input-group">
        <input type="hidden" name="action" value="create_post">
        <div class="user-profile">
            <img src="assets/placeholder-profilbild.jpg" alt="Profilbild">
        </div>
        <div class="post-input-group-inputs">
            <textarea name="post_text" id="post-input" placeholder="Verfasse einen Post..." maxlength="300" required></textarea>
            <div class="image-upload">
                <label for="image-input" class="image-upload-label">
                    <i class="bi bi-image"></i> Bild hinzufügen
                </label>
                <input type="file" name="post_image" id="image-input" accept="image/*" style="display: none;">
            </div>
            <div class="image-preview" id="image-preview">
                <img id="preview-img" src="#" alt="Bildvorschau">
                <button id="remove-image" type="button"> <i class="bi bi-trash-fill"> </i></button>
            </div>
            <div class="post-input-bottom">
                <p class="character-count">0/300</p>
                <button id="post-button" type="submit">Veröffentlichen</button>
            </div>
        </div>
    </form>

    <!-- Filter Toggle -->
    <div class="switch-wrapper">
        <div class="post-toggle">
            <input type="radio" id="all-posts" name="post-filter" <?php echo !$showFollowedOnly ? 'checked' : ''; ?>
                   onchange="window.location.href='index.php'">
            <label for="all-posts">Alle Posts</label>
            <input type="radio" id="followed-posts" name="post-filter" <?php echo $showFollowedOnly ? 'checked' : ''; ?>
                   onchange="window.location.href='index.php?filter=followed'">
            <label for="followed-posts">Gefolgt</label>
            <span class="switch-indicator"></span>
        </div>
    </div>

    <!-- Dynamischer Feed -->
    <section class="feed">
        <?php
        // Logik zur Anzeige der dynamischen Zustände
        switch ($loadingState) {
            case 'empty':
                ?>
                <div class="empty-state">
                    <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Noch keine Posts vorhanden</h3>
                    <p>Verfasse den ersten Post oder folge anderen Nutzern, um deren Posts zu sehen.</p>
                </div>
                <?php
                break;

            case 'error':
                ?>
                <div class="error-state">
                    <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Fehler beim Laden der Posts</h3>
                    <p>Die Posts konnten nicht geladen werden. Bitte versuchen Sie es später erneut.</p>
                    <button onclick="window.location.reload()" class="btn btn-primary">Neu laden</button>
                </div>
                <?php
                break;

            case 'data':
            default:
                if (empty($posts)) {
                    if ($showFollowedOnly) {
                        ?>
                        <div class="empty-state">
                            <i class="bi bi-people" style="font-size: 48px; margin-bottom: 20px;"></i>
                            <h3>Keine Posts von gefolgten Nutzern</h3>
                            <p>Du folgst noch niemandem oder deine gefolgten Nutzer haben noch keine Posts veröffentlicht.</p>
                            <a href="index.php" class="btn btn-primary">Alle Posts anzeigen</a>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="empty-state">
                            <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                            <h3>Noch keine Posts vorhanden</h3>
                            <p>Verfasse den ersten Post, um die Community zu starten!</p>
                        </div>
                        <?php
                    }
                } else {
                    foreach ($posts as $post) {
                        include 'post.php';
                    }
                }
                break;
        }
        ?>
    </section>
</div>

<?php include 'footerMobile.php'; ?>
<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<?php include 'lightbox.php'; ?>

<script>
    // Zeichenzähler für Post-Textarea
    const postInput = document.getElementById('post-input');
    const charCount = document.querySelector('.character-count');

    function updateCharCount() {
        const count = postInput.value.length;
        charCount.textContent = count + '/300';
        charCount.style.color = count > 280 ? '#dc3545' : '#6c757d';
    }

    postInput.addEventListener('input', () => {
        // Automatische Höhenanpassung
        postInput.style.height = 'auto';
        postInput.style.height = postInput.scrollHeight + 'px';

        // Zeichenzähler aktualisieren
        updateCharCount();
    });

    // Bildvorschau
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeImageButton = document.getElementById('remove-image');

    imageInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    removeImageButton.addEventListener('click', () => {
        imageInput.value = '';
        previewImg.src = '#';
        imagePreview.style.display = 'none';
    });

    // Reaktions-Forms per AJAX (optional für bessere UX)
    document.querySelectorAll('.reaction-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            // Hier könnte AJAX implementiert werden für nahtlose Reaktionen
            // e.preventDefault();
        });
    });
</script>
</body>
</html>