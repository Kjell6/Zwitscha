-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 10.50.1.18:3308
-- Erstellungszeit: 04. Jul 2025 um 15:11
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
