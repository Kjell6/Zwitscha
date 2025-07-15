<?php
    require_once __DIR__ . '/php/PostVerwaltung.php';
    require_once __DIR__ . '/php/NutzerVerwaltung.php';
    require_once __DIR__ . '/php/session_helper.php';

// === Initialisierung ===
    $postRepository = new PostVerwaltung();
    $nutzerVerwaltung = new NutzerVerwaltung();

// === POST-Request-Handling für neue Posts ===
    $feedbackMessage = '';
    $feedbackType = ''; // success, error, info

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
            $currentUserId = getCurrentUserId();

        // Prüfen, ob der Post-Text leer ist
        if (empty($postText)) {
            $feedbackMessage = 'Post-Text darf nicht leer sein.';
            $feedbackType    = 'error';

        // Prüfen, ob der Post-Text zu lang ist
        } elseif (strlen($postText) > 300) {
            $feedbackMessage = 'Post-Text darf maximal 300 Zeichen lang sein.';
            $feedbackType    = 'error';

        } else {
            $imageData = null;
            // Prüfen, ob ein Bild hochgeladen wurde und fehlerfrei ist
            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                // Datei-Validierung
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (!in_array($_FILES['post_image']['type'], $allowedTypes)) {
                    $feedbackMessage = 'Nur JPEG, PNG, GIF und WebP Dateien sind erlaubt.';
                    $feedbackType = 'error';
                } elseif ($_FILES['post_image']['size'] > 2 * 1024 * 1024) { // 2 MB Limit (entspricht Server-Limit)
                    $feedbackMessage = 'Das Bild ist zu groß. Maximal 2 MB sind erlaubt.';
                    $feedbackType = 'error';
                } else {
                    $imageData = file_get_contents($_FILES['post_image']['tmp_name']);
                    if ($imageData === false) {
                        $feedbackMessage = 'Fehler beim Lesen der Bilddatei.';
                        $feedbackType = 'error';
                    }
                }
            } elseif (isset($_FILES['post_image']) && $_FILES['post_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $feedbackMessage = 'Fehler beim Hochladen der Datei.';
                $feedbackType = 'error';
            }

                // Post nur erstellen, wenn kein Fehler beim Bild-Upload aufgetreten ist
                if ($feedbackType !== 'error') {
                    $newPostId = $postRepository->createPost($currentUserId, $postText, $imageData);

                if ($newPostId) {
                    $feedbackMessage = 'Post erfolgreich angelegt.';
                    $feedbackType    = 'success';
                    // Leere die Post-Variable, um doppeltes Senden zu verhindern (Post/Redirect/Get Pattern)
                    header("Location: " . $_SERVER['PHP_SELF'] . '#post-' . $newPostId);
                    exit();
                } else {
                    $feedbackMessage = 'Fehler beim Speichern des Posts in der Datenbank.';
                    $feedbackType    = 'error';
                }
            }
        }
        }
    }

// === Daten für Feed-Anzeige laden ===

    requireLogin();

    $currentUserId = getCurrentUserId();
    $currentUser = $nutzerVerwaltung->getUserById($currentUserId);
    $limit = 15;

    $showFollowedOnly = isset($_GET['filter']) && $_GET['filter'] === 'followed';

    // Posts aus der Datenbank laden
    if ($showFollowedOnly) {
        $posts = $postRepository->getFollowedPosts($currentUserId, $limit, 0);
    } else {
        $posts = $postRepository->getAllPosts($currentUserId, $limit, 0);
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
    <script src="js/image-compression.js"></script>
</head>
<body>
<!-- === HEADER === -->
<?php include 'headerDesktop.php'; ?>

<!-- === MAIN CONTENT === -->
<div class="main-content">
    <!-- === FEEDBACK MESSAGES === -->
    <?php if (!empty($feedbackMessage)): ?>
        <div class="feedback-message feedback-<?php echo $feedbackType; ?>">
            <?php echo htmlspecialchars($feedbackMessage); ?>
        </div>
    <?php endif; ?>

    <!-- === CREATE POST FORM === -->
    <form method="POST" enctype="multipart/form-data" class="create-post-form">
        <input type="hidden" name="action" value="create_post">

        <!-- Form Header -->
        <div class="form-header">
            <img class="user-avatar" src="getImage.php?type=user&id=<?php echo $currentUserId; ?>" loading="lazy" alt="Dein Profilbild">
            <textarea name="post_text" id="post-input" placeholder="Was gibt's Neues?" maxlength="300" required></textarea>
        </div>

        <!-- === IMAGE PREVIEW === -->
        <div class="image-preview" id="image-preview" style="display: none;">
            <img id="preview-img" src="#" alt="Bildvorschau">
            <button id="remove-image" type="button" aria-label="Bild entfernen"><i class="bi bi-x-lg"></i></button>
        </div>

        <!-- === FORM FOOTER === -->
        <div class="form-footer">
            <div class="form-actions">
                <label for="image-input" class="action-button" aria-label="Bild hinzufügen">
                    <i class="bi bi-image"></i>
                </label>
                <input type="file" name="post_image" id="image-input" accept="image/*" style="display: none;">
            </div>
            <div class="form-submit-area">
                <p class="character-count">0/300</p>
                <button id="post-button" type="submit">Posten</button>
            </div>
        </div>
    </form>

    <!-- === POST TOGGLE === -->
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

    <!-- === FEED CONTAINER === -->
    <section class="feed" id="posts-container">
        <?php
        if (empty($posts)) {
            if ($showFollowedOnly) {
                ?>
                <!-- Empty State - Followed Posts -->
                <div class="empty-state">
                    <i class="bi bi-people" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Keine Posts von gefolgten Nutzern</h3>
                    <p>Du folgst noch niemandem oder deine gefolgten Nutzer haben noch keine Posts veröffentlicht.</p>
                    <a href="index.php" class="btn btn-primary">Alle Posts anzeigen</a>
                </div>
                <?php
            } else {
                ?>
                <!-- Empty State - All Posts -->
                <div class="empty-state">
                    <i class="bi bi-chat-square-text" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Noch keine Posts vorhanden</h3>
                    <p>Verfasse den ersten Post, um die Community zu starten!</p>
                </div>
                <?php
            }
        } else {
            // Dynamic Post Content
            foreach ($posts as $post) {
                include 'post.php';
            }
        }
        ?>
    </section>

    <!-- === LOAD MORE SECTION === -->
    <?php if (count($posts) === $limit): ?>
    <div id="mehr-laden-container" style="display: flex; justify-content: center; margin: 20px 0;">
        <button id="mehr-laden-button" class="btn">Mehr laden</button>
    </div>
    <?php endif; ?>
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

    imageInput.addEventListener('change', async (event) => {
        const file = event.target.files[0];
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
                imageInput.value = ''; // Input zurücksetzen
                return;
            }

            try {
                // Automatische Bildkomprimierung vor Upload
                imagePreview.style.display = 'block';
                await window.imageCompressor.handleFileInput(imageInput, previewImg, (compressedFile) => {
                    const originalSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    const compressedSizeMB = (compressedFile.size / (1024 * 1024)).toFixed(2);
                    console.log(`Komprimierung: ${originalSizeMB}MB → ${compressedSizeMB}MB`);
                });
            } catch (error) {
                alert('Fehler bei der Bildverarbeitung: ' + error.message);
                imageInput.value = '';
                imagePreview.style.display = 'none';
            }
        }
    });

    removeImageButton.addEventListener('click', () => {
        imageInput.value = '';
        previewImg.src = '#';
        imagePreview.style.display = 'none';
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const container = document.getElementById("posts-container");
        const buttonContainer = document.getElementById('mehr-laden-container');

        if (!buttonContainer) return;

        const button = document.getElementById("mehr-laden-button");
        let offset = <?php echo $limit; ?>;
        const limit = <?php echo $limit; ?>;
        const context = "<?php echo $showFollowedOnly ? 'followed' : 'all'; ?>";

        button.addEventListener("click", () => {
            button.disabled = true;
            button.textContent = 'Lädt...';

            fetch(`php/get-posts.php?context=${context}&offset=${offset}&limit=${limit}`)
                .then(res => {
                    if (!res.ok) throw new Error('Fehler beim Laden der Posts');
                    return res.text();
                })
                .then(html => {
                    if (!html.trim()) {
                        if (buttonContainer) buttonContainer.style.display = 'none';
                    } else {
                        container.insertAdjacentHTML('beforeend', html);
                        offset += limit;
                    }
                })
                .catch(err => {
                    console.error(err);
                    button.textContent = 'Fehler!';
                })
                .finally(() => {
                    if (buttonContainer && buttonContainer.style.display !== 'none') {
                        button.disabled = false;
                        button.textContent = 'Mehr laden';
                    }
                });
        });
    });
</script>
</body>
</html>