-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 10.50.1.18:3308
-- Erstellungszeit: 20. Jun 2025 um 13:35
-- Server-Version: 10.9.2-MariaDB-1:10.9.2+maria~ubu2204
-- PHP-Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `gwprg-25-team-19`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `folge`
--

CREATE TABLE `folge` (
  `folgender_id` int(11) NOT NULL,
  `gefolgter_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `folge`
--

INSERT INTO `folge` (`folgender_id`, `gefolgter_id`) VALUES
(1, 2),
(5, 3),
(3, 5),
(4, 3),
(5, 4),
(4, 5),
(11, 10);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kommentar`
--

CREATE TABLE `kommentar` (
  `id` int(11) NOT NULL,
  `nutzer_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `datumZeit` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `kommentar`
--

INSERT INTO `kommentar` (`id`, `nutzer_id`, `post_id`, `text`, `datumZeit`) VALUES
(3, 1, 2, 'Da hast du wohl recht. Es ist wirklich wunderschön in der Natur spazieren zu gehen.', '2025-06-12 11:48:14'),
(5, 1, 19, 'xdgysg', '2025-06-13 10:29:47'),
(6, 5, 21, 'Heyho, icke auch :O', '2025-06-16 13:56:20'),
(10, 5, 23, 'Zwa zwi Zwitscher', '2025-06-16 13:59:03'),
(11, 4, 21, 'na sicha', '2025-06-16 13:59:10'),
(12, 5, 25, 'Ich liebe eure krustig, saftigen, leckere, flispigen Pizzen', '2025-06-16 14:07:36'),
(13, 8, 25, 'Wann collab?', '2025-06-16 14:07:55'),
(14, 3, 26, 'Vfbf', '2025-06-16 14:08:23'),
(15, 6, 28, 'Hi ich auch', '2025-06-16 14:17:29'),
(16, 6, 26, 'L', '2025-06-16 14:18:11'),
(17, 3, 36, '🛐', '2025-06-20 12:44:33'),
(18, 3, 37, 'Heiliger Bimbam', '2025-06-20 12:48:52');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nutzer`
--

CREATE TABLE `nutzer` (
  `id` int(11) NOT NULL,
  `nutzerName` varchar(50) NOT NULL,
  `passwort` varchar(100) NOT NULL,
  `istAdministrator` tinyint(1) NOT NULL,
  `erstellungsDatum` datetime NOT NULL DEFAULT current_timestamp(),
  `profilbild` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `nutzer`
--

INSERT INTO `nutzer` (`id`, `nutzerName`, `passwort`, `istAdministrator`, `erstellungsDatum`, `profilbild`) VALUES
(1, 'beispielNutzer', 'passwort123', 1, '2025-05-06 13:00:16', NULL),
(2, 'nutzer2', '1', 0, '2025-06-12 10:01:28', NULL),
(3, 'Kjell', '060304', 1, '2025-06-16 13:55:31', NULL),
(4, 'ferdi', '1234', 0, '2025-06-16 13:55:42', NULL),
(5, 'Julian', 'Fortnite', 1, '2025-06-16 13:55:49', NULL),
(6, 'StandartSkill', 'Skybase', 0, '2025-06-16 14:02:59', NULL),
(7, 'DolceCrusto', 'DolceCrusto', 0, '2025-06-16 14:04:58', NULL),
(8, 'Sondag', 'sondag', 0, '2025-06-16 14:05:19', NULL),
(9, 'Max', 'Max', 0, '2025-06-20 12:35:11', NULL),
(10, 'Jesus', 'Jesus', 0, '2025-06-20 12:42:50', NULL),
(11, 'Jens2', 'Jens', 0, '2025-06-20 13:01:00', NULL),
(12, 'Hallo', 'Hallo', 0, '2025-06-20 13:33:09', 0x6173736574732f706c616365686f6c6465722d70726f66696c62696c642e6a7067);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `nutzer_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `datumZeit` datetime NOT NULL DEFAULT current_timestamp(),
  `bildDaten` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `post`
--

INSERT INTO `post` (`id`, `nutzer_id`, `text`, `datumZeit`, `bildDaten`) VALUES
(2, 2, 'Gerade einen tollen Spaziergang gemacht. Die Natur ist wunderschön! 🌳', '2025-05-10 12:30:00', NULL),
(8, 1, 'Leute, das ist der erste richtige Post, der auf Zwitscha gepostet wird!!!', '2025-06-11 20:16:20', NULL),
(19, 1, 'hj', '2025-06-12 14:45:08', NULL),
(21, 3, 'Hey Leute, benutzt noch wer anders Zwitscha?', '2025-06-16 13:56:04', NULL),
(23, 5, 'Zwi zwa zwitscher', '2025-06-16 13:58:21', NULL),
(24, 4, 'wacht auf!!1elf', '2025-06-16 14:03:02', NULL),
(25, 7, 'Hallo liebe Pizzafreunde!', '2025-06-16 14:06:23', NULL),
(26, 8, 'Bei uns gibt es frische und leckere Lachs Avocado Bagels!!!', '2025-06-16 14:07:44', NULL),
(28, 6, 'Hey Loite bin neu hier', '2025-06-16 14:17:00', NULL),
(30, 6, ':(', '2025-06-16 14:17:17', NULL),
(33, 5, 'Guten Abend liebe Zwitscha Community', '2025-06-19 19:30:57', NULL),
(35, 3, 'Jo Heute ist Freitag, ist das nicht cool?', '2025-06-20 12:30:14', NULL),
(36, 10, 'Welt seid mir gegrüßt!', '2025-06-20 12:43:34', NULL),
(37, 10, 'Just woke up like this', '2025-06-20 12:47:37', NULL),
(38, 8, 'Bei uns gibt es leckere Zimtschnecken', '2025-06-20 12:53:39', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Reaktion`
--

CREATE TABLE `Reaktion` (
  `nutzer_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `reaktionsTyp` enum('Daumen Hoch','Daumen Runter','Herz','Lachen','Fragezeichen','Ausrufezeichen') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `Reaktion`
--

INSERT INTO `Reaktion` (`nutzer_id`, `post_id`, `reaktionsTyp`) VALUES
(1, 2, 'Ausrufezeichen'),
(1, 8, 'Ausrufezeichen'),
(1, 8, 'Daumen Hoch'),
(1, 2, 'Lachen'),
(1, 8, 'Herz'),
(1, 8, 'Lachen'),
(1, 2, 'Daumen Runter'),
(4, 21, 'Lachen'),
(3, 21, 'Daumen Hoch'),
(4, 21, 'Herz'),
(3, 23, 'Ausrufezeichen'),
(4, 21, 'Daumen Hoch'),
(5, 21, 'Daumen Hoch'),
(3, 23, 'Daumen Runter'),
(4, 23, 'Fragezeichen'),
(4, 26, 'Herz'),
(8, 21, 'Daumen Hoch'),
(8, 28, 'Herz'),
(8, 30, 'Herz'),
(3, 30, 'Daumen Runter'),
(3, 28, 'Herz'),
(3, 36, 'Herz'),
(11, 38, 'Daumen Hoch');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `folge`
--
ALTER TABLE `folge`
  ADD KEY `folgender_id` (`folgender_id`),
  ADD KEY `gefolgter_id` (`gefolgter_id`);

--
-- Indizes für die Tabelle `kommentar`
--
ALTER TABLE `kommentar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `foreignKey Nutzer` (`nutzer_id`),
  ADD KEY `foreignKey Post` (`post_id`);

--
-- Indizes für die Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nutzerName` (`nutzerName`);

--
-- Indizes für die Tabelle `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_ibfk_1` (`nutzer_id`);

--
-- Indizes für die Tabelle `Reaktion`
--
ALTER TABLE `Reaktion`
  ADD KEY `Reaktion_ibfk_1` (`nutzer_id`),
  ADD KEY `Reaktion_ibfk_2` (`post_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `kommentar`
--
ALTER TABLE `kommentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT für Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT für Tabelle `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `folge`
--
ALTER TABLE `folge`
  ADD CONSTRAINT `folge_ibfk_1` FOREIGN KEY (`folgender_id`) REFERENCES `nutzer` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folge_ibfk_2` FOREIGN KEY (`gefolgter_id`) REFERENCES `nutzer` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `kommentar`
--
ALTER TABLE `kommentar`
  ADD CONSTRAINT `foreignKey Nutzer` FOREIGN KEY (`nutzer_id`) REFERENCES `nutzer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `foreignKey Post` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`nutzer_id`) REFERENCES `nutzer` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `Reaktion`
--
ALTER TABLE `Reaktion`
  ADD CONSTRAINT `Reaktion_ibfk_1` FOREIGN KEY (`nutzer_id`) REFERENCES `nutzer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Reaktion_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
