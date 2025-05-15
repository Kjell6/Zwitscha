<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Zwitscha - Suche</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/search.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&display=swap" rel="stylesheet">
</head>
<body>

    <div class="main-content">
        <h2>Benutzer suchen</h2>

        <div class="header-section search-section">
            <input type="text" placeholder="Suche..." class="search-bar" id="header-search-input" autocomplete="off">
            <div class="header-search-results-dropdown"></div>
        </div>
    </div>

    <script>
        const script = document.createElement('script');
        script.src = 'js/search.js';
        script.onload = () => {
            if (typeof initHeaderSearch === 'function') {
                initHeaderSearch();
            }
        };
        document.body.appendChild(script);
    </script>

    <?php include 'footerMobile.php'; ?>


</body>
</html>