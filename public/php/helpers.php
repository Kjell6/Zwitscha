<?php

if (!function_exists('time_ago')) {
    function time_ago(string $datetime, string $full = 'vor %s'): string {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        // Berechne Wochen separat ohne dynamische Eigenschaften
        $weeks = floor($diff->d / 7);
        $days = $diff->d - ($weeks * 7);

        $values = [
            'y' => $diff->y,
            'm' => $diff->m,
            'w' => $weeks,
            'd' => $days,
            'h' => $diff->h,
            'i' => $diff->i,
            's' => $diff->s,
        ];

        $string = [
            'y' => 'Jahr',
            'm' => 'Monat',
            'w' => 'Woche',
            'd' => 'Tag',
            'h' => 'Stunde',
            'i' => 'Minute',
            's' => 'Sekunde',
        ];

        foreach ($string as $k => &$v) {
            if ($values[$k]) {
                $plural = match ($k) {
                    'y' => 'e',       // Jahre
                    'm' => 'e',       // Monate
                    'w' => 'n',       // Wochen
                    'd' => 'en',      // Tage
                    'h' => 'n',       // Stunden
                    'i' => 'n',       // Minuten
                    's' => 'n',       // Sekunden
                    default => '',
                };

                $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? $plural : '');
            } else {
                unset($string[$k]);
            }
        }

        // Nur die größte Zeiteinheit anzeigen
        if (!empty($string)) {
            $string = array_slice($string, 0, 1);
        }

        $time_ago = $string ? implode(', ', $string) : 'gerade jetzt';
        return sprintf($full, $time_ago);
    }
}

if (!function_exists('getReactionEmojiMap')) {
    /**
     * Liefert die Zuordnung von Reaktionstyp (DB-Wert) zu Emoji-Zeichen.
     *
     * @return array<string,string>
     */
    function getReactionEmojiMap(): array {
        return [
            'Daumen Hoch'   => '👍',
            'Daumen Runter' => '👎',
            'Herz'          => '❤️',
            'Lachen'        => '🤣',
            'Fragezeichen'  => '❓',
            'Ausrufezeichen'=> '‼️',
        ];
    }
}

if (!function_exists('linkify_mentions')) {
    function linkify_mentions(string $text, NutzerVerwaltung $nutzerVerwaltung): string {
        // Teile den Text anhand von @-Erwähnungen auf.
        $parts = preg_split('/(@\w+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $resultHtml = '';

        foreach ($parts as $part) {
            // Prüft, ob der Teil eine @-Erwähnung ist
            if (isset($part[0]) && $part[0] === '@' && preg_match('/^@(\w+)$/', $part, $matches)) {
                $username = $matches[1];
                $user = $nutzerVerwaltung->getUserByUsername($username);

                if ($user) {
                    // Wenn der Nutzer existiert, erstelle einen Link
                    $url = 'Profil.php?userid=' . urlencode($user['id']);
                    $mentionHtml = htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
                    $resultHtml .= '<a href="' . $url . '" class="mention-link no-post-details">' . $mentionHtml . '</a>';
                } else {
                    // Wenn der Nutzer nicht existiert, escape den Text
                    $resultHtml .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
                }
            } else {
                // Für alle anderen Textteile, nur escapen
                $resultHtml .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
            }
        }

        return $resultHtml;
    }
} 