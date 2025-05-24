<?php
$error = false;
$currentUser = 'Benutzer1'; // Beispiel für den aktuell eingeloggten Nutzer

$postDetail = [
    'autor' => 'Benutzer1',
    'profilBild' => 'assets/placeholder-profilbild.jpg',
    'datumZeit' => '2025-05-01T12:00:00Z',
    'text' => 'Das ist der detaillierte Text des Posts.',
    'bildPfad' => 'assets/zwitscha_green.jpg',
    'reactions' => [
        '👍' => 3,
        '👎' => 0,
        '❤️' => 5,
        '🤣' => 2,
        '❓' => 0,
        '‼️' => 1
    ],
    'comments' => 3
];

$kommentare = [
    [
        'autor' => 'Kommentator1',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-05-01T13:00:00Z',
        'time_label' => 'vor 5 Minuten',
        'text' => 'Das ist ein Kommentar.'
    ],
    [
        'autor' => 'Kommentator2',
        'profilBild' => 'assets/placeholder-profilbild.jpg',
        'datumZeit' => '2025-05-01T13:05:00Z',
        'time_label' => 'vor 2 Minuten',
        'text' => 'Noch ein Kommentar.'
    ]
];


$datum = new DateTime($postDetail['datumZeit']);
$postDetail['time_label'] = $datum->format('H:i, d.m.y'); // ergibt z. B.: 13:43, 30.04.25

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Detail</title>
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

    <article class="detail-post">
        <section class="post-user-infos-detail">
            <a href="Profil.php" class="no-post-details">
                <img src="<?= htmlspecialchars($postDetail['profilBild']) ?>" alt="Profilbild">
            </a>
            <div class="post-user-details-detail">
                <span class="post-author-name"><?= htmlspecialchars($postDetail['autor']) ?></span>
                <time datetime="<?= htmlspecialchars($postDetail['datumZeit']) ?>" class="post-timestamp">
                    <?= htmlspecialchars($postDetail['time_label']) ?>
                </time>
            </div>
            <?php if ($currentUser === $postDetail['autor']): ?>
                <button class="post-options-button no-post-details" type="button" aria-label="Post-Optionen">
                    <i class="bi bi-trash-fill"></i>
                </button>
            <?php endif; ?>
        </section>

        <div class="post-content-detail">
            <p><?= nl2br(htmlspecialchars($postDetail['text'])) ?></p>

            <?php if (!empty($postDetail['bildPfad'])): ?>
                <div class="post-image-container">
                    <img src="<?= htmlspecialchars($postDetail['bildPfad']) ?>"
                         alt="Post-Bild"
                         class="post-image"
                         onclick="openLightbox('<?= htmlspecialchars($postDetail['bildPfad']) ?>')"
                         style="cursor: pointer;">
                </div>
            <?php endif; ?>
        </div>

        <section class="post-actions detail-actions">
            <div class="post-reactions">
                <?php foreach ($postDetail['reactions'] as $emoji => $count): ?>
                    <button class="reaction-button" type="button" data-emoji="<?= htmlspecialchars($emoji) ?>">
                        <?= $emoji ?> <span class="reaction-counter"><?= (int)$count ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="comment-input-group">
                <textarea type="text" id="comment-input" placeholder="Schreibe einen Kommentar..."></textarea>
                <button id="comment-button" type="button">Kommentieren</button>
            </div>
        </section>
    </article>


    <section class="comments-section">
        <h2><?= count($kommentare) ?> Kommentar<?= count($kommentare) !== 1 ? 'e' : '' ?></h2>
        <?php if ($error): ?>
            <p class="error">Fehler beim Laden der Posts. Bitte später erneut versuchen.</p>

        <?php elseif (count($kommentare) === 0): ?>
            <p class="empty">Noch keine Posts verfügbar.</p>

        <?php else: ?>
            <ul id="comments">
                <?php foreach ($kommentare as $kommentar): ?>
                    <li>
                        <?php
                        // $kommentare-Array in post.php verfügbar machen:
                        include 'kommentar.php';
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
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