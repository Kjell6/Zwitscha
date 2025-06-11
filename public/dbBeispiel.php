<!DOCTYPE HTML>
<html lang="de">

<head>
    <title>Beispiel einer Datenbankverbindung</title>
    <meta charset="UTF-8" />
</head>

<body>
    <h1>Beispiel einer Datenbankverbindung mittels PHP</h1>
    <?php
    $db_server = getenv("MYSQL_SERVER");
    $db_user = getenv("MYSQL_USER");
    $db_password = getenv("MYSQL_PASSWORD");
    $db_database = getenv("MYSQL_DATABASE");

    if (in_array(false, array($db_server, $db_user, $db_password, $db_database), $strict = true)) {
        echo "<p><strong>Fehler:</strong> Keine Datenbank-Credentials gefunden!</p>\n";
    } else {
        $mysqli = new mysqli($db_server, $db_user, $db_password, $db_database);
        if ($mysqli->connect_error) {
            echo "<p><strong>Fehler bei der Verbindung:</strong> " . mysqli_connect_error() . "</p>\n";
        } else {

            $result = $mysqli->query("SELECT VERSION()");
            if ($result === false) {
                echo "<p><strong>Fehler bei Versionsabfrage:</strong> " . $mysqli->error . "</p>\n";
            } else {
                $row = $result->fetch_array();
                $version = $row[0];
                echo "<p>Verbindung erfolgreich. Version: " . $version . "</p>\n";
            }

            $result = $mysqli->query("SHOW TABLES");
            if ($result === false) {
                echo "<p><strong>Fehler bei der Anzeigen der Tabellen:</strong> " . $mysqli->error . "</p>\n";
            } else {
                $count = $result->num_rows;
                echo "<p>Die Datenbank " . $db_database . " enthält " . $count . " Tabelle(n).</p>\n";
                if ($count) {
                    echo "<ul>\n";
                    while ($table = mysqli_fetch_row($result)) {
                        echo "<li>" . $table[0] . "</li>\n";
                    }
                    echo "</ul>\n";
                }
            }
        }
    }
    ?>
</body>

</html>