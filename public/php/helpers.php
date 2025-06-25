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