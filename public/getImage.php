<?php
require_once 'php/config.php';
require_once 'php/NutzerVerwaltung.php';
require_once 'php/PostVerwaltung.php';
require_once 'php/session_helper.php';


$nutzerVerwaltung = new NutzerVerwaltung();
$postVerwaltung = new PostVerwaltung();

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $imageData = null;
    if ($type === 'user') {
        // Annahme: getNutzerById ist eine gültige Methode, die wir verwenden können.
        $user = $nutzerVerwaltung->getUserById($id);
        if ($user && isset($user['profilbild']) && $user['profilbild']) {
            $imageData = $user['profilbild'];
        }
    } elseif ($type === 'post') {
        // Für getPostById benötigen wir die ID des aktuellen Nutzers für die Berechtigungsprüfung
        $currentUserId = getCurrentUserId() ?? 0;
        $post = $postVerwaltung->getPostById($id, $currentUserId);
        if ($post && isset($post['bildDaten']) && $post['bildDaten']) {
            $imageData = $post['bildDaten'];
        }
    }

    if ($imageData) {
        // Versuchen, den Bildtyp zu erkennen, ansonsten Standard auf JPEG.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($imageData);
        
        // Default auf image/jpeg, wenn der Typ nicht erkannt werden kann
        if (strpos($mime_type, 'image/') !== 0) {
            $mime_type = 'image/jpeg';
        }

        header("Content-Type: " . $mime_type);
        echo $imageData;
        exit;
    }
}

// Wenn kein Bild gefunden wurde oder die ID ungültig ist, ein Platzhalterbild anzeigen.
$placeholder = 'assets/placeholder-profilbild.jpg';
// Sicherstellen, dass der richtige Content-Type für den Platzhalter gesendet wird.
$finfo = new finfo(FILEINFO_MIME_TYPE);
$placeholder_mime = $finfo->file($placeholder) ?: 'image/jpeg';

header('Content-Type: ' . $placeholder_mime);
readfile($placeholder);
