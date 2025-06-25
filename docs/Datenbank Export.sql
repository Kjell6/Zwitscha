-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 10.50.1.18:3308
-- Erstellungszeit: 20. Jun 2025 um 23:39
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
(5, 3),
(3, 5),
(4, 3),
(5, 4),
(4, 5),
(3, 10),
(3, 8);

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
(6, 5, 21, 'Heyho, icke auch :O', '2025-06-16 13:56:20'),
(10, 5, 23, 'Zwa zwi Zwitscher', '2025-06-16 13:59:03'),
(11, 4, 21, 'na sicha', '2025-06-16 13:59:10'),
(12, 5, 25, 'Ich liebe eure krustig, saftigen, leckere, flispigen Pizzen', '2025-06-16 14:07:36'),
(13, 8, 25, 'Wann collab?', '2025-06-16 14:07:55'),
(14, 3, 26, 'Vfbf', '2025-06-16 14:08:23'),
(15, 6, 28, 'Hi ich auch', '2025-06-16 14:17:29'),
(16, 6, 26, 'L', '2025-06-16 14:18:11'),
(17, 3, 36, '🛐', '2025-06-20 12:44:33'),
(19, 10, 41, 'Jesus braucht diese Kissen!', '2025-06-20 22:31:54'),
(20, 3, 33, 'Oh, guten Abend zurück 👋', '2025-06-20 22:46:52'),
(21, 3, 23, 'Du sagst es', '2025-06-20 22:58:10'),
(22, 3, 40, 'Wie lang hast du geschlafen?', '2025-06-20 23:22:24');

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
(1, 8, 'Ausrufezeichen'),
(1, 8, 'Daumen Hoch'),
(1, 8, 'Herz'),
(1, 8, 'Lachen'),
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
(3, 36, 'Daumen Hoch'),
(3, 33, 'Herz'),
(3, 24, 'Ausrufezeichen'),
(3, 40, 'Daumen Hoch'),
(3, 40, 'Herz');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT für Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT für Tabelle `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
