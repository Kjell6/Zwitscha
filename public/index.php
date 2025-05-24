<?php
// ---- Dummy-Daten der Posts ----
$error = false;
$currentUser = 'Max Mustermann'; // später aus der Session holen

$posts = [
    [
        'autor'     => 'Anna Beispiel',
        'profilBild'     => 'assets/placeholder-profilbild.jpg',
        'datumZeit'  => '2025-04-26T14:15:00Z',
        'time_label' => 'vor 1 Tag',
        'text'    => '👍',
        'bildPfad'     => '',
        'reactions'  => ['👍'=>2,'👎'=>0,'❤️'=>1,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments'   => 0,
    ],
    [
        'autor'     => 'Max Mustermann',
        'profilBild'     => 'assets/placeholder-profilbild.jpg',
        'datumZeit'  => '2025-04-27T10:30:00Z',
        'time_label' => 'vor 2 Stunden',
        'text'    => 'Wie findet ihr dieses neue Logo von Zwitscha? Ich finde es super! Es ist modern und frisch. Was denkt ihr?',
        'bildPfad'     => 'assets/zwitscha_green.jpg',
        'reactions'  => ['👍'=>5,'👎'=>1,'❤️'=>3,'🤣'=>0,'❓'=>0,'‼️'=>2],
        'comments'   => 2,
    ],
    [
        'autor'     => 'Lena Neumann',
        'profilBild'=> 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T08:00:00Z',
        'time_label'=> 'vor 4 Stunden',
        'text'      => 'Guten Morgen! 🌞 Heute starte ich mit frischem Kaffee und neuen Ideen in den Tag. Manchmal reicht ein bisschen Ruhe, um wieder kreative Energie zu tanken. Was motiviert euch am Morgen?',
        'bildPfad'  => '',
        'reactions' => ['👍'=>8,'👎'=>0,'❤️'=>5,'🤣'=>1,'❓'=>0,'‼️'=>0],
        'comments'  => 3,
    ],
    [
        'autor'     => 'Tom Testfall',
        'profilBild'=> 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-25T21:45:00Z',
        'time_label'=> 'vor 2 Tagen',
        'text'      => 'Ich suche nach einem spannenden Buch für das Wochenende. Thriller, Science-Fiction oder gern auch etwas Philosophisches – habt ihr Empfehlungen, die euch nachhaltig beeindruckt haben?',
        'bildPfad'  => '',
        'reactions' => ['👍'=>3,'👎'=>0,'❤️'=>2,'🤣'=>0,'❓'=>1,'‼️'=>0],
        'comments'  => 5,
    ],
    [
        'autor'     => 'Sophie Sonnenschein',
        'profilBild'=> 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-27T12:10:00Z',
        'time_label'=> 'vor 30 Minuten',
        'text'      => 'Der Frühling bringt Farbe und Leben zurück! Ich war heute früh unterwegs und habe die ersten blühenden Kirschbäume gesehen. Gibt’s etwas Schöneres, als draußen zu sitzen und einfach mal durchzuatmen?',
        'bildPfad'  => '',
        'reactions' => ['👍'=>12,'👎'=>0,'❤️'=>9,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments'  => 1,
    ],
    [
        'autor'     => 'Jan Zweifel',
        'profilBild'=> 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-24T17:20:00Z',
        'time_label'=> 'vor 3 Tagen',
        'text'      => 'Kann mir jemand den neuen Algorithmus erklären? Ich lese mich seit Stunden ein, aber irgendwie macht es einfach keinen Sinn. 🤯 Vielleicht fehlt mir der richtige Denkansatz oder ein gutes Beispiel.',
        'bildPfad'  => '',
        'reactions' => ['👍'=>1,'👎'=>0,'❤️'=>0,'🤣'=>0,'❓'=>4,'‼️'=>1],
        'comments'  => 2,
    ],
    [
        'autor'     => 'Carla Kreativ',
        'profilBild'=> 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-04-23T19:10:00Z',
        'time_label'=> 'vor 4 Tagen',
        'text'      => 'Habe heute ein DIY-Projekt abgeschlossen: ein Regal komplett aus alten Weinkisten gebaut. Nachhaltig, günstig und sieht super aus! Würde es jedem empfehlen, der Lust auf ein schnelles Upcycling-Projekt hat.',
        'bildPfad'  => '',
        'reactions' => ['👍'=>15,'👎'=>0,'❤️'=>10,'🤣'=>0,'❓'=>0,'‼️'=>0],
        'comments'  => 6,
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
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
    <div class="post-input-group">
        <div class="user-profile">
            <img src="assets/placeholder-profilbild.jpg" alt="Profilbild">
        </div>
        <div class="post-input-group-inputs">
            <textarea type="text" id="post-input" placeholder="Verfasse einen Post..."></textarea>
            <div class="image-upload">
                <label for="image-input" class="image-upload-label">
                    <i class="bi bi-image"></i> Bild hinzufügen
                </label>
                <input type="file" id="image-input" accept="image/*" style="display: none;">
            </div>
            <div class="image-preview" id="image-preview">
                <img id="preview-img" src="#" alt="Bildvorschau">
                <button id="remove-image" type="button"> <i class="bi bi-trash-fill"> </i></button>
            </div>
            <div class="post-input-bottom">
                <p class="character-count">0/300</p>
                <button id="post-button" type="button">Veröffentlichen</button>
            </div>
        </div>
    </div>

    <div class="switch-wrapper">
        <div class="post-toggle">
            <input type="radio" id="all-posts" name="post-filter" checked>
            <label for="all-posts">Alle Posts</label>
            <input type="radio" id="followed-posts" name="post-filter">
            <label for="followed-posts">Gefolgt</label>
            <span class="switch-indicator"></span>
        </div>
    </div>


    <section class="feed">
        <?php if ($error): ?>
            <p class="error">Fehler beim Laden der Posts. Bitte später erneut versuchen.</p>

        <?php elseif (count($posts) === 0): ?>
            <p class="empty">Noch keine Posts verfügbar.</p>

        <?php else: ?>
            <ul id="posts">
                <?php foreach ($posts as $post): ?>
                    <li>
                        <?php
                        // $post-Array in post.php verfügbar machen:
                        include 'post.php';
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
<?php include 'footerMobile.php'; ?>
<footer>
    <p>© 2025 Zwitscha</p>
</footer>
<script>
    // Größe des Textfeldes automatisch an Textlänge anpassen
    const commentInput = document.getElementById('post-input');
    commentInput.addEventListener('input', () => {
        commentInput.style.height = 'auto'; // Reset height to auto
        commentInput.style.height = commentInput.scrollHeight + 'px'; // Set height to scroll height
    });

    // Bildvorschau anzeigen
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

    // Bild entfernen
    removeImageButton.addEventListener('click', () => {
        imageInput.value = '';
        previewImg.src = '#';
        imagePreview.style.display = 'none';
    });
</script>
</body>
</html>