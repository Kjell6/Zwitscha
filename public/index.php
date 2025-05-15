<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="assets/favicon.png" type="image/png">
    <title>Startseite</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/Startseite.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'headerDesktop.php'; ?>

    <div class="post-input-group">
        <textarea type="text" id="post-input" placeholder="Verfasse einen Post..."></textarea>
        <button id="post-button" type="button">Veröffentlichen</button>
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


    <section>
        <ul id="posts">
            <li>
                <?php include 'post.php'; ?>
            </li>

            <li>
                <?php include 'post.php'; ?>
            </li>

            <li>
                <?php include 'post.php'; ?>
            </li>

            <li>
                <?php include 'post.php'; ?>
            </li>

        </ul>
    </section>

    <?php include 'footerMobile.php'; ?>

    <script>
        //Größe des Textfeldes automatisch an Textlänge anpassen

        const commentInput = document.getElementById('post-input');

        commentInput.addEventListener('input', () => {
            commentInput.style.height = 'auto'; // Reset height to auto
            commentInput.style.height = commentInput.scrollHeight + 'px'; // Set height to scroll height
        });
    </script>

</body>
</html>