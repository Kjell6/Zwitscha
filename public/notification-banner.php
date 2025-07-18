<?php
/**
 * Benachrichtigungs-Banner Komponente
 * Zeigt einen Banner an, der Benutzer auffordert, zu den Einstellungen zu gehen,
 * um Benachrichtigungen zu aktivieren, falls sie noch keine Benachrichtigungseinstellungen haben.
 * 
 * Voraussetzungen:
 * - $nutzerVerwaltung muss initialisiert sein
 * - $currentUserId muss gesetzt sein
 * - banner.css muss eingebunden sein
 * - Bootstrap Icons müssen verfügbar sein
 */

// Prüfen, ob der Benutzer bereits Benachrichtigungseinstellungen hat
if (isset($nutzerVerwaltung) && isset($currentUserId)) {
    $hasNotificationSettings = $nutzerVerwaltung->hasNotificationSettings($currentUserId);
    
    if (!$hasNotificationSettings) {
        ?>
        <!-- === NOTIFICATION BANNER === -->
        <div id="notification-banner" class="notification-banner" style="display: none;">
            <span>
                <i class="bi bi-bell banner-icon"></i>
                Verpasse keine Updates! Aktiviere Benachrichtigungen für neue Posts, Kommentare und Erwähnungen.
            </span>
            <div class="notification-banner-buttons">
                <a href="einstellungen.php" class="button-enable">Zu den Einstellungen</a>
                <button id="notification-close-banner-button" class="button-close" aria-label="Schließen">&times;</button>
            </div>
        </div>
        <?php
    }
}
?> 