-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Erstellungszeit: 16. Jul 2025 um 10:01
-- Server-Version: 10.11.11-MariaDB-ubu2204
-- PHP-Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `webappdb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `folge`
--

CREATE TABLE `folge` (
  `folgender_id` int(11) NOT NULL,
  `gefolgter_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `folge`
--

INSERT INTO `folge` (`folgender_id`, `gefolgter_id`) VALUES
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(2, 3),
(3, 2),
(2, 5),
(5, 2),
(2, 7),
(7, 2),
(3, 4),
(4, 3),
(3, 6),
(6, 3),
(3, 8),
(8, 3),
(4, 5),
(5, 4),
(4, 9),
(9, 4),
(4, 10),
(10, 4),
(5, 6),
(6, 5),
(5, 11),
(11, 5),
(5, 12),
(12, 5),
(6, 7),
(7, 6),
(6, 8),
(8, 6),
(6, 9),
(9, 6),
(7, 8),
(8, 7),
(7, 10),
(10, 7),
(7, 11),
(11, 7),
(8, 9),
(9, 8),
(8, 12),
(12, 8),
(8, 2),
(2, 8),
(9, 10),
(10, 9),
(9, 11),
(11, 9),
(9, 3),
(3, 9),
(10, 11),
(11, 10),
(10, 12),
(12, 10),
(10, 2),
(2, 10),
(11, 12),
(12, 11),
(11, 3),
(3, 11),
(11, 4),
(4, 11),
(12, 2),
(2, 12),
(12, 3),
(3, 12),
(12, 4),
(4, 12);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kommentar`
--

CREATE TABLE `kommentar` (
  `id` int(11) NOT NULL,
  `nutzer_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `text` text NOT NULL,
  `datumZeit` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kommentar`
--

INSERT INTO `kommentar` (`id`, `nutzer_id`, `post_id`, `parent_comment_id`, `text`, `datumZeit`) VALUES
(1, 2, 1, NULL, 'Glückwunsch zum Start! 🎈', '2025-07-01 09:05:00'),
(2, 3, 1, 1, 'Ich stimme Alice zu! 👍', '2025-07-01 09:06:00'),
(3, 4, 1, 1, 'Freue mich auch! 😊', '2025-07-01 09:07:00'),
(4, 5, 1, NULL, 'Tolle Plattform! 💯', '2025-07-01 09:10:00'),
(5, 1, 2, NULL, 'Danke für das Update, Alice! 😊', '2025-07-01 10:05:00'),
(7, 6, 2, NULL, 'Willkommen Alice! 🤗', '2025-07-01 10:07:00'),
(8, 2, 4, NULL, 'Stimmt! Ohne Kaffee geht nichts ☕', '2025-07-01 12:05:00'),
(9, 5, 4, 8, 'Tee ist aber auch gut! 🍵', '2025-07-01 12:06:00'),
(10, 4, 4, 9, 'Kaffee bleibt unschlagbar! 😄', '2025-07-01 12:07:00'),
(11, 6, 5, NULL, 'Ja! Lass uns über AI sprechen 🤖', '2025-07-01 13:05:00'),
(12, 8, 5, 11, 'Machine Learning ist faszinierend! 🧠', '2025-07-01 13:06:00'),
(13, 10, 5, NULL, 'Blockchain auch interessant! ⛓️', '2025-07-01 13:07:00'),
(14, 7, 6, NULL, 'Welches Buch denn? 📚', '2025-07-01 14:05:00'),
(15, 5, 6, 14, 'Ja, verrate uns den Titel! 😊', '2025-07-01 14:06:00'),
(16, 9, 6, NULL, 'Buchempfehlungen sind super! 👍', '2025-07-01 14:07:00'),
(17, 2, 7, NULL, 'JavaScript! 💛', '2025-07-01 15:05:00'),
(18, 3, 7, NULL, 'Python für mich! 🐍', '2025-07-01 15:06:00'),
(19, 11, 7, NULL, 'Java ist stabil! ☕', '2025-07-01 15:07:00'),
(20, 4, 7, 17, 'JS ist vielseitig! 🚀', '2025-07-01 15:08:00'),
(21, 7, 11, NULL, 'Inception! 🎬', '2025-07-01 19:05:00'),
(22, 9, 11, NULL, 'The Matrix! 🕴️', '2025-07-01 19:06:00'),
(23, 12, 11, 21, 'Inception ist genial! 🧠', '2025-07-01 19:07:00'),
(24, 6, 14, NULL, 'Geht mir genauso! 😅', '2025-07-02 09:05:00'),
(25, 8, 14, NULL, 'Zwitscha > andere Plattformen! 🏆', '2025-07-02 09:06:00'),
(26, 3, 18, NULL, 'Stimmt! Python rocks! 🐍🔥', '2025-07-02 13:05:00'),
(27, 11, 18, 26, 'Einfach und mächtig! 💪', '2025-07-02 13:06:00'),
(28, 3, 23, NULL, 'Was ein schöner Baum!', '2025-07-16 11:53:19'),
(29, 4, 24, NULL, 'Da stimme ich dir zu', '2025-07-16 11:57:42'),
(30, 5, 24, 29, 'Ich auch', '2025-07-16 11:58:48'),
(31, 12, 24, NULL, 'Nö', '2025-07-16 12:00:02');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `login_tokens`
--

CREATE TABLE `login_tokens` (
  `id` int(11) NOT NULL,
  `nutzer_id` int(11) NOT NULL,
  `selektor` char(12) NOT NULL,
  `tokenHash` char(64) NOT NULL,
  `gueltigBis` datetime NOT NULL,
  `erstelltAm` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `login_tokens`
--

INSERT INTO `login_tokens` (`id`, `nutzer_id`, `selektor`, `tokenHash`, `gueltigBis`, `erstelltAm`) VALUES
(4, 1, '3fb1d5d4c7e8', '6769f5b06441b8b7a48508a6b798ea05c2cb9d7d2802ed6abfca4b3fa076b928', '2025-08-15 12:00:14', '2025-07-16 12:00:14');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `nutzer`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Reaktion`
--

CREATE TABLE `Reaktion` (
  `nutzer_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `reaktionsTyp` enum('Daumen Hoch','Daumen Runter','Herz','Lachen','Fragezeichen','Ausrufezeichen') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Reaktion`
--

INSERT INTO `Reaktion` (`nutzer_id`, `post_id`, `reaktionsTyp`) VALUES
(2, 1, 'Herz'),
(3, 1, 'Daumen Hoch'),
(4, 1, 'Herz'),
(5, 1, 'Daumen Hoch'),
(6, 1, 'Herz'),
(7, 1, 'Daumen Hoch'),
(8, 1, 'Herz'),
(9, 1, 'Daumen Hoch'),
(10, 1, 'Herz'),
(11, 1, 'Daumen Hoch'),
(12, 1, 'Herz'),
(1, 2, 'Herz'),
(3, 2, 'Daumen Hoch'),
(6, 2, 'Herz'),
(7, 2, 'Daumen Hoch'),
(2, 3, 'Herz'),
(4, 3, 'Daumen Hoch'),
(5, 3, 'Herz'),
(8, 3, 'Daumen Hoch'),
(2, 4, 'Lachen'),
(5, 4, 'Herz'),
(6, 4, 'Lachen'),
(9, 4, 'Herz'),
(6, 5, 'Daumen Hoch'),
(8, 5, 'Herz'),
(10, 5, 'Daumen Hoch'),
(11, 5, 'Herz'),
(7, 6, 'Herz'),
(9, 6, 'Daumen Hoch'),
(11, 6, 'Herz'),
(12, 6, 'Daumen Hoch'),
(2, 7, 'Herz'),
(3, 7, 'Daumen Hoch'),
(4, 7, 'Herz'),
(11, 7, 'Daumen Hoch'),
(1, 8, 'Herz'),
(3, 8, 'Herz'),
(5, 8, 'Herz'),
(9, 8, 'Herz'),
(4, 9, 'Daumen Hoch'),
(6, 9, 'Herz'),
(10, 9, 'Daumen Hoch'),
(12, 9, 'Herz'),
(2, 10, 'Lachen'),
(5, 10, 'Herz'),
(7, 10, 'Lachen'),
(11, 10, 'Herz'),
(7, 11, 'Daumen Hoch'),
(9, 11, 'Herz'),
(12, 11, 'Daumen Hoch'),
(2, 11, 'Herz'),
(1, 12, 'Daumen Hoch'),
(3, 12, 'Herz'),
(8, 12, 'Daumen Hoch'),
(10, 12, 'Herz'),
(4, 13, 'Daumen Hoch'),
(6, 13, 'Fragezeichen'),
(9, 13, 'Daumen Hoch'),
(11, 13, 'Herz'),
(6, 14, 'Lachen'),
(8, 14, 'Herz'),
(10, 14, 'Lachen'),
(12, 14, 'Daumen Hoch'),
(2, 15, 'Lachen'),
(4, 15, 'Herz'),
(7, 15, 'Lachen'),
(9, 15, 'Herz'),
(1, 16, 'Daumen Hoch'),
(5, 16, 'Herz'),
(6, 16, 'Daumen Hoch'),
(11, 16, 'Herz'),
(3, 17, 'Herz'),
(8, 17, 'Daumen Hoch'),
(10, 17, 'Herz'),
(12, 17, 'Daumen Hoch'),
(3, 18, 'Herz'),
(7, 18, 'Daumen Hoch'),
(11, 18, 'Herz'),
(12, 18, 'Daumen Hoch'),
(2, 19, 'Herz'),
(5, 19, 'Herz'),
(9, 19, 'Herz'),
(11, 19, 'Herz'),
(1, 20, 'Daumen Hoch'),
(4, 20, 'Herz'),
(6, 20, 'Daumen Hoch'),
(10, 20, 'Herz'),
(1, 22, 'Ausrufezeichen'),
(6, 24, 'Ausrufezeichen'),
(4, 24, 'Ausrufezeichen'),
(4, 24, 'Daumen Hoch'),
(5, 24, 'Ausrufezeichen'),
(5, 24, 'Daumen Hoch'),
(12, 24, 'Ausrufezeichen');

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
  ADD KEY `foreignKey Post` (`post_id`),
  ADD KEY `fk_parent_comment` (`parent_comment_id`);

--
-- Indizes für die Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selektor` (`selektor`),
  ADD KEY `nutzer_id_idx` (`nutzer_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT für Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT für Tabelle `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
  ADD CONSTRAINT `fk_parent_comment` FOREIGN KEY (`parent_comment_id`) REFERENCES `kommentar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foreignKey Nutzer` FOREIGN KEY (`nutzer_id`) REFERENCES `nutzer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `foreignKey Post` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD CONSTRAINT `login_tokens_ibfk_1` FOREIGN KEY (`nutzer_id`) REFERENCES `nutzer` (`id`) ON DELETE CASCADE;

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
