<?php

// Zeitangaben in benutzerfreundlichem Format anzeigen
if (!function_exists('time_ago')) {
    function time_ago(string $datetime, string $full = 'vor %s'): string {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

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

        // Plural-Endungen
        foreach ($string as $k => &$v) {
            if ($values[$k]) {
                $plural = match ($k) {
                    'y' => 'e',
                    'm' => 'e',
                    'w' => 'n',
                    'd' => 'en',
                    'h' => 'n',
                    'i' => 'n',
                    's' => 'n',
                    default => '',
                };

                $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? $plural : '');
            } else {
                unset($string[$k]);
            }
        }

        // Nur größte Zeiteinheit anzeigen
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

/**
 * Extrahiert alle @-Erwähnungen aus einem Text.
 *
 * @param string $text Der zu durchsuchende Text.
 * @return array Eine Liste von eindeutigen Benutzernamen (ohne @).
 */
function extractMentions(string $text): array {
    preg_match_all('/@([a-zA-Z0-9._-]+)/', $text, $matches);
    if (!empty($matches[1])) {
        // Duplikate entfernen und zurückgeben
        return array_unique($matches[1]);
    }
    return [];
}

if (!function_exists('linkify_content')) {
    /**
     * Wandelt @-Erwähnungen und #-Hashtags in einem Text in Links um.
     *
     * @param string $text Der zu durchsuchende Text.
     * @param NutzerVerwaltung $nutzerVerwaltung Eine Instanz der NutzerVerwaltung.
     * @return string Der Text mit umgewandelten Links als sicherem HTML.
     */
    function linkify_content(string $text, NutzerVerwaltung $nutzerVerwaltung): string {
        // Text in @-Erwähnungen und #-Hashtags aufteilen
        $parts = preg_split('/([@]\w+|[#][\wÄÖÜäöüß]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $resultHtml = '';

        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            // @-Erwähnung verarbeiten
            if ($part[0] === '@' && preg_match('/^@(\w+)$/', $part, $matches)) {
                $username = $matches[1];
                $user = $nutzerVerwaltung->getUserByUsername($username);
                if ($user) {
                    $url = 'Profil.php?userid=' . urlencode($user['id']);
                    $linkHtml = htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
                    $resultHtml .= '<a href="' . $url . '" class="link no-post-details">' . $linkHtml . '</a>';
                } else {
                    $resultHtml .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
                }
            }
            // #-Hashtag verarbeiten
            elseif ($part[0] === '#' && preg_match('/^#([\wÄÖÜäöüß]+)$/u', $part, $matches)) {
                $hashtag = $matches[1];
                $url = 'hashtag.php?tag=' . urlencode($hashtag);
                $linkHtml = htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
                $resultHtml .= '<a href="' . $url . '" class="link no-post-details">' . $linkHtml . '</a>';
            }
            // Normaler Text
            else {
                $resultHtml .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
            }
        }

        return nl2br($resultHtml, false);
    }
} 