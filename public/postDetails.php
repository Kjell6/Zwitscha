<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Detail</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post.css">
    <link rel="stylesheet" href="css/postDetail.css">
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

    <article class="post detail-post">
        <section class="post-user-infos">
            <a href="Profil.php" class="no-post-details">
                <img src="assets/placeholder-profilbild.jpg" alt="Profilbild">
            </a>
            <div class="post-user-details">
                <span class="post-author-name">[Autorname]</span>
                <time datetime="2025-04-27T10:30:00Z" class="post-timestamp">13:43, 30.04.25</time>
            </div>
            <button class="post-options-button no-post-details" type="button" aria-label="Post-Optionen"> <i class="bi bi-trash-fill"> </i></button>
        </section>

        <div class="post-content">
            <p>[Hier steht der Text des Zwitscha-Posts. Maximal 300 Zeichen.]</p>
        </div>

        <div class="post-images-container">
            <!-- <img src="assets/zwitscha.png" alt="Post-Bild" class="post-image"> -->
        </div>


        <section class="post-actions detail-actions">
            <div class="post-reactions">
                <button class="reaction-button" type="button" data-emoji="👍">👍 <span class="reaction-counter">0</span></button>
                <button class="reaction-button" type="button" data-emoji="👎">👎 <span class="reaction-counter">0</span></button>
                <button class="reaction-button" type="button" data-emoji="❤️">❤️ <span class="reaction-counter">0</span></button>
                <button class="reaction-button" type="button" data-emoji="🤣">🤣 <span class="reaction-counter">0</span></button>
                <button class="reaction-button" type="button" data-emoji="❓">❓ <span class="reaction-counter">0</span></button>
                <button class="reaction-button" type="button" data-emoji="‼️">‼️ <span class="reaction-counter">0</span></button>
            </div>

            <div class="comment-input-group">
                <textarea type="text" id="comment-input" placeholder="Schreibe einen Kommentar..."></textarea>
                <button id="comment-button" type="button">Kommentieren</button>
            </div>
        </section>

    </article>

    <section class="comments-section">
        <h2>3 Kommentare</h2>
        <ul id="comments-list">
            <li>
                <?php include 'kommentar.php'; ?>
            </li>
            <li>
                <?php include 'kommentar.php'; ?>
            </li>
        </ul>
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
</body>
</html>