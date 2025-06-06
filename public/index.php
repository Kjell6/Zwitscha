<?php
// ---- POST Request Handling für neue Posts ----
$feedbackMessage = '';
$feedbackType = ''; // success, error, info

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'create_post'
) {
    $postText   = trim($_POST['post_text'] ?? '');
    $currentUser = 'Max Mustermann';

    if (empty($postText)) {
        $feedbackMessage = 'Post-Text darf nicht leer sein.';
        $feedbackType    = 'error';

    } elseif (strlen($postText) > 300) {
        $feedbackMessage = 'Post-Text darf maximal 300 Zeichen lang sein.';
        $feedbackType    = 'error';

    } elseif (isset($_FILES['post_image'])
        && $_FILES['post_image']['error'] !== UPLOAD_ERR_OK
    ) {
        print_r($_POST); // Dummy-Ausgabe
        $feedbackMessage = 'Fehler beim Hochladen des Bildes.';
        $feedbackType    = 'error';

    } else {
        // Hier würden später Post und ggf. Bild in die DB geschrieben werden.
        // Aktuell nur Debug-Ausgabe:
        print_r($_POST);
        if (isset($_FILES['post_image'])) {
            print_r($_FILES['post_image']);
        }
        $feedbackMessage = 'Post erfolgreich angelegt (Dummy).';
        $feedbackType    = 'success';
    }
}


// ---- Dynamische Inhalte: Posts laden ----
// Später: Aktuellen Benutzer aus der Session oder Authentifizierung holen
$currentUser = 'Max Mustermann';
$showFollowedOnly = isset($_GET['filter']) && $_GET['filter'] === 'followed';

// Simuliere verschiedene Zustände für dynamische Inhalte
// Für Tests: URL um ?state=empty oder ?state=error ergänzen
// Später: Zustand basierend auf Datenbankabfrage bestimmen (Erfolg/Fehler/keine Daten)

$loadingState = $_GET['state'] ?? 'data'; // data, empty, error (für Testing)

// Dummy-Posts (später aus Datenbank)
$allPosts = [
    [
        'id' => 1,
        'autor' => 'Anna Beispiel',
        'userId' => 1,

        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-26T14:15:00Z',
        'time_label' => 'vor 1 Tag',
        'text' => '👍',
        'bildPfad' => '',
        'reactions' => ['👍'=>2,'👎'=>0,'❤️'=>1,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments' => 0,
        'isFollowed' => false
    ],
    [
        'id' => 2,
        'autor' => 'Max Mustermann',
        'userId' => 2,

        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T10:30:00Z',
        'time_label' => 'vor 2 Stunden',
        'text' => 'Wie findet ihr dieses neue Logo von Zwitscha? Ich finde es super! Es ist modern und frisch. Was denkt ihr?',
        'bildPfad' => 'assets/zwitscha_green.jpg',
        'reactions' => ['👍'=>5,'👎'=>1,'❤️'=>3,'🤣'=>0,'❓'=>0,'‼️'=>2],
        'comments' => 2,
        'isFollowed' => true
    ],
    [
        'id' => 3,
        'autor' => 'Lena Neumann',
        'userId' => 3,

        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T08:00:00Z',
        'time_label' => 'vor 4 Stunden',
        'text' => 'Guten Morgen! 🌞 Heute starte ich mit frischem Kaffee und neuen Ideen in den Tag. Manchmal reicht ein bisschen Ruhe, um wieder kreative Energie zu tanken. Was motiviert euch am Morgen?',
        'bildPfad' => '',
        'reactions' => ['👍'=>8,'👎'=>0,'❤️'=>5,'🤣'=>1,'❓'=>0,'‼️'=>0],
        'comments' => 3,
        'isFollowed' => true
    ],
    [
        'id' => 4,
        'autor' => 'Tom Testfall',
        'userId' => 4,

        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-25T21:45:00Z',
        'time_label' => 'vor 2 Tagen',
        'text' => 'Ich suche nach einem spannenden Buch für das Wochenende. Thriller, Science-Fiction oder gern auch etwas Philosophisches – habt ihr Empfehlungen, die euch nachhaltig beeindruckt haben?',
        'bildPfad' => '',
        'reactions' => ['👍'=>3,'👎'=>0,'❤️'=>2,'🤣'=>0,'❓'=>1,'‼️'=>0],
        'comments' => 5,
        'isFollowed' => false
    ],
    [
        'id' => 5,
        'autor' => 'Sophie Sonnenschein',
        'userId' => 5,

        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T12:10:00Z',
        'time_label' => 'vor 30 Minuten',
        'text' => 'Der Frühling bringt Farbe und Leben zurück! Ich war heute früh unterwegs und habe die ersten blühenden Kirschbäume gesehen. Gibt\'s etwas Schöneres, als draußen zu sitzen und einfach mal durchzuatmen?',
        'bildPfad' => '',
        'reactions' => ['👍'=>12,'👎'=>0,'❤️'=>9,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments' => 1,
        'isFollowed' => true
    ]
];

// Posts nach Filter filtern
if ($showFollowedOnly) {
    $posts = array_filter($allPosts, function($post) {
        return $post['isFollowed'];
    });
} else {
    $posts = $allPosts;
}

// Die Post-Darstellung wird jetzt durch post.php gehandhabt

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
                    // Wenn Daten vorhanden, Posts anzeigen - jeden Post über post.php einbinden
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