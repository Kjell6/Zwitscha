<?php
// ---- POST Request Handling ----
$feedbackMessage = '';
$feedbackType = ''; // success, error, info

// Aktueller Benutzer (später aus Session oder Authentifizierung holen)
$currentUser = 'Max Mustermann';

// Post ID aus URL Parameter
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Simuliere verschiedene Zustände für Testing
$loadingState = $_GET['state'] ?? 'data'; // data, empty, error

// POST Request Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Neuen Kommentar erstellen
    if (isset($_POST['action']) && $_POST['action'] === 'create_comment') {
        $commentText = trim($_POST['comment_text'] ?? '');
        $postIdFromForm = (int)($_POST['post_id'] ?? 0);

        if (empty($commentText)) {
            $feedbackMessage = 'Kommentar-Text darf nicht leer sein.';
            $feedbackType = 'error';
        } elseif (strlen($commentText) > 300) {
            $feedbackMessage = 'Kommentar darf maximal 500 Zeichen lang sein.';
            $feedbackType = 'error';
        } else {
            // Hier später Kommentar in Datenbank speichern
            // Beispiel: print_r($_POST); // Ausgabe der POST-Daten zu Debugging-Zwecken

        }
    }

    // Reaktion togglen
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_reaction') {
        $emoji = $_POST['emoji'] ?? '';
        $postIdFromForm = (int)($_POST['post_id'] ?? 0);

        // Hier später Reaktion in Datenbank toggle
        // Beispiel: print_r($_POST); // Ausgabe der POST-Daten zu Debugging-Zwecken
    }

    // Post löschen
    if (isset($_POST['action']) && $_POST['action'] === 'delete_post') {
        $postIdFromForm = (int)($_POST['post_id'] ?? 0);
        // Hier später Post aus Datenbank löschen
        // Beispiel: print_r($_POST); // Ausgabe der POST-Daten zu Debugging-Zwecken

    }

    // Kommentar löschen
    if (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
        $commentIdFromForm = (int)($_POST['comment_id'] ?? 0);
        // Hier später Kommentar aus Datenbank löschen
        // Beispiel: print_r($_POST); // Ausgabe der POST-Daten zu Debugging-Zwecken
    }
}

// ---- Dummy Daten für Post ----
$allPosts = [
    1 => [
        'id' => 1,
        'autor' => 'Anna Beispiel',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-26T14:15:00Z',
        'time_label' => 'vor 1 Tag',
        'text' => '👍 Dieses neue Feature ist wirklich großartig! Es macht die Bedienung so viel einfacher.',
        'bildPfad' => '',
        'reactions' => ['👍'=>2,'👎'=>0,'❤️'=>1,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments' => 3
    ],
    2 => [
        'id' => 2,
        'autor' => 'Max Mustermann',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T10:30:00Z',
        'time_label' => 'vor 2 Stunden',
        'text' => 'Wie findet ihr dieses neue Logo von Zwitscha? Ich finde es super! Es ist modern und frisch. Was denkt ihr?',
        'bildPfad' => 'assets/zwitscha_green.jpg',
        'reactions' => ['👍'=>5,'👎'=>1,'❤️'=>3,'🤣'=>0,'❓'=>0,'‼️'=>2],
        'comments' => 2
    ],
    3 => [
        'id' => 3,
        'autor' => 'Lena Neumann',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T08:00:00Z',
        'time_label' => 'vor 4 Stunden',
        'text' => 'Guten Morgen! 🌞 Heute starte ich mit frischem Kaffee und neuen Ideen in den Tag. Manchmal reicht ein bisschen Ruhe, um wieder kreative Energie zu tanken. Was motiviert euch am Morgan?',
        'bildPfad' => '',
        'reactions' => ['👍'=>8,'👎'=>0,'❤️'=>5,'🤣'=>1,'❓'=>0,'‼️'=>0],
        'comments' => 4
    ]
];

// ---- Dummy Daten für Kommentare ----
$allComments = [
    1 => [ // Post ID 1
        [
            'id' => 101,
            'post_id' => 1,
            'autor' => 'Tom Testfall',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-26T15:30:00Z',
            'time_label' => 'vor 20 Stunden',
            'text' => 'Absolut! Das macht wirklich einen großen Unterschied.'
        ],
        [
            'id' => 102,
            'post_id' => 1,
            'autor' => 'Sophie Sonnenschein',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-26T16:45:00Z',
            'time_label' => 'vor 19 Stunden',
            'text' => 'Kann ich nur zustimmen! Endlich wurde das umgesetzt.'
        ],
        [
            'id' => 103,
            'post_id' => 1,
            'autor' => 'Max Mustermann',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T09:15:00Z',
            'time_label' => 'vor 3 Stunden',
            'text' => 'Freut mich, dass es euch auch gefällt! 😊'
        ]
    ],
    2 => [ // Post ID 2
        [
            'id' => 201,
            'post_id' => 2,
            'autor' => 'Anna Beispiel',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T11:00:00Z',
            'time_label' => 'vor 1 Stunde',
            'text' => 'Das Logo ist wirklich gelungen! Sehr moderne Gestaltung.'
        ],
        [
            'id' => 202,
            'post_id' => 2,
            'autor' => 'Lena Neumann',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T11:45:00Z',
            'time_label' => 'vor 15 Minuten',
            'text' => 'Die Farben sind perfekt gewählt! 💚'
        ]
    ],
    3 => [ // Post ID 3
        [
            'id' => 301,
            'post_id' => 3,
            'autor' => 'Tom Testfall',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T08:30:00Z',
            'time_label' => 'vor 3,5 Stunden',
            'text' => 'Ein guter Kaffee am Morgen ist wirklich das Beste!'
        ],
        [
            'id' => 302,
            'post_id' => 3,
            'autor' => 'Anna Beispiel',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T09:00:00Z',
            'time_label' => 'vor 3 Stunden',
            'text' => 'Bei mir ist es Tee und ein Spaziergang. Das weckt alle Sinne! ☕'
        ],
        [
            'id' => 303,
            'post_id' => 3,
            'autor' => 'Sophie Sonnenschein',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T10:15:00Z',
            'time_label' => 'vor 1,5 Stunden',
            'text' => 'Musik und ein paar Minuten Meditation funktionieren bei mir am besten.'
        ],
        [
            'id' => 304,
            'post_id' => 3,
            'autor' => 'Max Mustermann',
            'profilBild' => 'assets/placeholder-profilbild.jpg',
            'datumZeit' => '2025-04-27T11:30:00Z',
            'time_label' => 'vor 30 Minuten',
            'text' => 'Tolle Ideen! Jeder hat seine eigene Routine - das ist das Schöne daran.'
        ]
    ]
];

// ---- Post und Kommentare laden basierend auf ID ----
// Hier später Datenbankabfrage, um den Post mit der gegebenen ID zu laden
$post = null;
// Hier später Datenbankabfrage, um die Kommentare für diesen Post zu laden
$comments = [];
// Simuliere Laden basierend auf Ladezustand und Dummy-Daten
if ($loadingState === 'data') {
    if (isset($allPosts[$postId])) {
        $post = $allPosts[$postId];
        $comments = $allComments[$postId] ?? [];
    }
}

// Berechtigung zum Löschen prüfen
$canDeletePost = $post && ($post['autor'] === $currentUser);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? 'Post von ' . htmlspecialchars($post['autor']) : 'Post Details'; ?></title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/postDetail.css">
    <link rel="stylesheet" href="css/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

<?php include 'headerDesktop.php'; ?>

<main class="container">
    <div class="page-header-container">
        <a href="index.php" class="back-button">
            <button type="button">Zurück</button>
        </a>
        <h1>Post</h1>
    </div>

    <!-- Dynamischer Content basierend auf Loading State -->
    <?php
    switch ($loadingState) {
        case 'error':
            ?>
            <div class="error-state">
                <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>Fehler beim Laden des Posts</h3>
                <p>Der Post konnte nicht geladen werden. Bitte versuchen Sie es später erneut.</p>
                <button onclick="window.location.reload()" class="btn btn-primary">Neu laden</button>
            </div>
            <?php
            break;

        case 'empty':
        case 'data':
        default:
            if (!$post) {
                ?>
                <div class="empty-state">
                    <i class="bi bi-file-earmark-x" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Post nicht gefunden</h3>
                    <p>Der gewünschte Post existiert nicht oder wurde gelöscht.</p>
                    <a href="index.php" class="btn btn-primary">Zurück zur Startseite</a>
                </div>
                <?php
            } else {
                // Post anzeigen
                ?>
                <article class="detail-post">
                    <section class="post-user-infos-detail">
                        <a href="Profil.php" class="no-post-details">
                            <img src="<?php echo htmlspecialchars($post['profilBild']); ?>" alt="Profilbild">
                        </a>
                        <div class="post-user-details-detail">
                            <span class="post-author-name"><?php echo htmlspecialchars($post['autor']); ?></span>
                            <time datetime="<?php echo $post['datumZeit']; ?>" class="post-timestamp">
                                <?php echo htmlspecialchars($post['time_label']); ?>
                            </time>
                        </div>
                        <?php if ($canDeletePost): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Post wirklich löschen?');">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button class="post-options-button no-post-details" type="submit" aria-label="Post löschen">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </section>

                    <div class="post-content-detail">
                        <p><?php echo nl2br(htmlspecialchars($post['text'])); ?></p>

                        <?php if (!empty($post['bildPfad'])): ?>
                            <div class="post-image-container">
                                <img src="<?php echo htmlspecialchars($post['bildPfad']); ?>"
                                     alt="Post-Bild"
                                     class="post-image"
                                     onclick="openLightbox('<?php echo htmlspecialchars($post['bildPfad']); ?>')"
                                     style="cursor: pointer;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <section class="post-actions detail-actions">
                        <div class="post-reactions">
                            <?php
                            $emojis = ['👍', '👎', '❤️', '🤣', '❓', '‼️'];
                            foreach ($emojis as $emoji):
                                $count = $post['reactions'][$emoji] ?? 0;
                                ?>
                                <form method="POST" style="display: inline;" class="reaction-form">
                                    <input type="hidden" name="action" value="toggle_reaction">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="emoji" value="<?php echo $emoji; ?>">
                                    <button class="reaction-button" type="submit" data-emoji="<?php echo $emoji; ?>">
                                        <?php echo $emoji; ?> <span class="reaction-counter"><?php echo $count; ?></span>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>

                        <!-- Kommentar-Eingabeformular -->
                        <form method="POST" class="comment-input-group">
                            <input type="hidden" name="action" value="create_comment">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <textarea name="comment_text" id="comment-input" placeholder="Schreibe einen Kommentar..." maxlength="500" required></textarea>
                            <button id="comment-button" type="submit">Kommentieren</button>
                        </form>
                    </section>
                </article>

                <!-- Kommentare Sektion -->
                <section class="comments-section">
                    <?php if (empty($comments)): ?>
                        <div class="empty-state">
                            <i class="bi bi-chat-dots" style="font-size: 32px; margin-bottom: 15px;"></i>
                            <h3>Noch keine Kommentare</h3>
                            <p>Sei der Erste, der einen Kommentar schreibt!</p>
                        </div>
                    <?php else: ?>
                        <h2><?php echo count($comments); ?> Kommentar<?php echo count($comments) != 1 ? 'e' : ''; ?></h2>
                        <ul id="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <?php
                                $comment_for_template = $comment;
                                include 'kommentar.php';
                                ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <?php
            }
            break;
    }
    ?>

</main>

<?php include 'footerMobile.php'; ?>

<footer>
    <p>© 2025 Zwitscha</p>
</footer>

<script>
    const commentInput = document.getElementById('comment-input');
    commentInput.addEventListener('input', () => {
        commentInput.style.height = 'auto';
        commentInput.style.height = commentInput.scrollHeight + 'px';
    });
</script>

<?php include 'lightbox.php'; ?>


</body>
</html>