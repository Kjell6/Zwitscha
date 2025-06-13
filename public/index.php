<?php
require_once __DIR__ . '/php/PostVerwaltung.php';
require_once __DIR__ . '/php/NutzerVerwaltung.php';
require_once __DIR__ . '/php/session_helper.php';

// Verwaltung instanziieren
$postRepository = new PostVerwaltung();
$nutzerVerwaltung = new NutzerVerwaltung();


// ---- POST Request Handling für neue Posts ----
$feedbackMessage = '';
$feedbackType = ''; // success, error, info

// ---- POST Request Handling für ERSTELLEN ----
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'create_post'
) {
    // Prüfen ob angemeldet für Post-Erstellung
    if (!isLoggedIn()) {
        $feedbackMessage = 'Du musst angemeldet sein, um Posts zu erstellen.';
        $feedbackType = 'error';
    } else {
        $postText   = trim($_POST['post_text'] ?? '');
        // User-ID aus Session holen
        $currentUserId = getCurrentUserId();

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
            // === Bild-Validierung ===
            $file = $_FILES['post_image'];
            
            // Erlaubte Dateiformate
            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];
            
            // MIME-Type prüfen
            $fileMimeType = $file['type'];
            if (!array_key_exists($fileMimeType, $allowedMimeTypes)) {
                $feedbackMessage = 'Ungültiges Bildformat. Nur JPG, PNG, GIF und WebP sind erlaubt.';
                $feedbackType = 'error';
            } else {
                // Zusätzliche Validierung mit getimagesize() für mehr Sicherheit
                $imageInfo = getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $feedbackMessage = 'Die hochgeladene Datei ist kein gültiges Bild.';
                    $feedbackType = 'error';
                } else {
                    // Prüfe, ob der MIME-Type der Datei mit dem tatsächlichen Bildtyp übereinstimmt
                    $detectedMimeType = $imageInfo['mime'];
                    if ($fileMimeType !== $detectedMimeType) {
                        $feedbackMessage = 'Das Bildformat stimmt nicht mit der Dateierweiterung überein.';
                        $feedbackType = 'error';
                    } else {
                        // Dateigröße prüfen (max 10 MB)
                        $maxFileSize = 10 * 1024 * 1024; // 10 MB in Bytes
                        if ($file['size'] > $maxFileSize) {
                            $feedbackMessage = 'Das Bild ist zu groß. Maximal 10 MB sind erlaubt.';
                            $feedbackType = 'error';
                        } else {
                            // Upload-Verzeichnis erstellen
                            $uploadDir = __DIR__ . '/assets/uploads/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            // Sicherer Dateiname mit korrekter Erweiterung
                            $fileExtension = $allowedMimeTypes[$detectedMimeType];
                            $fileName = uniqid('img_', true) . '.' . $fileExtension;
                            $targetPath = $uploadDir . $fileName;

                            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                                $imagePath = 'assets/uploads/' . $fileName;
                            } else {
                                $feedbackMessage = 'Bild konnte nicht gespeichert werden.';
                                $feedbackType = 'error';
                            }
                        }
                    }
                }
            }
        } elseif (isset($_FILES['post_image']) && $_FILES['post_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Erweiterte Fehlerbehandlung für Upload-Fehler
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'Das Bild ist größer als die Server-Einstellung erlaubt.',
                UPLOAD_ERR_FORM_SIZE  => 'Das Bild ist größer als im Formular angegeben.',
                UPLOAD_ERR_PARTIAL    => 'Das Bild wurde nur teilweise hochgeladen.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server-Fehler: Temporärer Ordner fehlt.',
                UPLOAD_ERR_CANT_WRITE => 'Server-Fehler: Konnte Datei nicht schreiben.',
                UPLOAD_ERR_EXTENSION  => 'Upload wurde durch eine PHP-Erweiterung gestoppt.',
            ];
            $errorCode = $_FILES['post_image']['error'];
            $feedbackMessage = $uploadErrors[$errorCode] ?? 'Unbekannter Upload-Fehler.';
            $feedbackType = 'error';
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
}


// ---- Dynamische Inhalte: Posts laden ----
// Prüfen ob angemeldet
if (!isLoggedIn()) {
    header("Location: Login.php");
    exit();
}

$currentUserId = getCurrentUserId();
$currentUser = $nutzerVerwaltung->getUserById($currentUserId);

$showFollowedOnly = isset($_GET['filter']) && $_GET['filter'] === 'followed';

$loadingState = $_GET['state'] ?? 'data'; // data, empty, error (für Testing)

// Posts aus der Datenbank laden
if ($showFollowedOnly) {
    $posts = $postRepository->getFollowedPosts($currentUserId);
} else {
    $posts = $postRepository->getAllPosts($currentUserId);
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
            <img src="<?php echo htmlspecialchars($currentUser['profilBild'] ?? 'assets/placeholder-profilbild.jpg'); ?>" alt="Profilbild">
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
            // === Frontend-Validierung ===
            
            // Erlaubte Dateiformate
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            // MIME-Type prüfen
            if (!allowedTypes.includes(file.type)) {
                alert('Ungültiges Bildformat. Nur JPG, PNG, GIF und WebP sind erlaubt.');
                imageInput.value = ''; // Input zurücksetzen
                return;
            }
            
            // Dateigröße prüfen (max 10 MB)
            const maxFileSize = 10 * 1024 * 1024; // 10 MB
            if (file.size > maxFileSize) {
                alert('Das Bild ist zu groß. Maximal 10 MB sind erlaubt.');
                imageInput.value = ''; // Input zurücksetzen
                return;
            }
            
            // Dateiname-Validierung (verhindert gefährliche Zeichen)
            const fileName = file.name;
            const dangerousChars = /[<>:"/\\|?*]/;
            if (dangerousChars.test(fileName)) {
                alert('Der Dateiname enthält ungültige Zeichen.');
                imageInput.value = ''; // Input zurücksetzen
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                // Erstelle ein temporäres Image-Element, um die Dimensionen zu ermitteln
                const tempImg = new Image();
                tempImg.onload = function() {
                    // Bildabmessungen validieren (optional: max 8000x8000 px)
                    const maxDimension = 8000;
                    if (this.width > maxDimension || this.height > maxDimension) {
                        alert(`Das Bild ist zu groß. Maximale Auflösung: ${maxDimension}x${maxDimension} Pixel.`);
                        imageInput.value = '';
                        return;
                    }
                    
                    // Prüfe ob das Bild höher als 650px ist
                    if (this.height > 650) {
                        // Erstelle ein Canvas, um das Bild zu skalieren
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        
                        // Berechne neue Dimensionen (max 650px Höhe)
                        const maxHeight = 650;
                        const ratio = maxHeight / this.height;
                        const newWidth = this.width * ratio;
                        
                        canvas.width = newWidth;
                        canvas.height = maxHeight;
                        
                        // Zeichne das skalierte Bild auf das Canvas
                        ctx.drawImage(this, 0, 0, newWidth, maxHeight);
                        
                        // Setze das skalierte Bild als Preview
                        previewImg.src = canvas.toDataURL('image/jpeg', 0.9);
                    } else {
                        // Bild ist bereits klein genug, verwende es direkt
                        previewImg.src = e.target.result;
                    }
                    imagePreview.style.display = 'block';
                };
                
                tempImg.onerror = function() {
                    alert('Die ausgewählte Datei ist kein gültiges Bild.');
                    imageInput.value = '';
                };
                
                tempImg.src = e.target.result;
            };
            
            reader.onerror = function() {
                alert('Fehler beim Lesen der Datei.');
                imageInput.value = '';
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