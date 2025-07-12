-- Beispiel-Datensätze für Zwitscha-Demo

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------
-- Nutzer, das admin Passwort ist admin123
-- -----------------------------
INSERT INTO nutzer (id, nutzerName, passwort, istAdministrator, erstellungsDatum, profilbild) VALUES
  (1, 'admin', '$2y$10$KvqS8f4UE35JArzs.ZNPOOBb0gs4SaJWi/2mQZ5Cl3drEY36l9Dh6', 1, '2025-07-01 08:00:00', NULL),
  (2, 'alice', '$2y$10$hYlCp99mLo95x4mi0YYXReZm4PX1JMBj.AijW1s2075QnyYjsi6eG', 0, '2025-07-01 08:05:00', NULL),
  (3, 'bob',   '$2y$10$VUs7bEbKRBsqatmU/5hORexu2JKQq5vMdF6Xq/j/zwZsAjB5Y8bLG', 0, '2025-07-01 08:10:00', NULL);

-- -----------------------------
-- Folgebeziehungen
-- -----------------------------
INSERT INTO folge (folgender_id, gefolgter_id) VALUES
  (2, 1), -- Alice folgt Admin
  (3, 1), -- Bob folgt Admin
  (3, 2); -- Bob folgt Alice

-- -----------------------------
-- Posts
-- -----------------------------
INSERT INTO post (id, nutzer_id, text, datumZeit, bildDaten) VALUES
  (1, 1, 'Willkommen bei Zwitscha! 🎉', '2025-07-01 09:00:00', NULL),
  (2, 2, 'Erster Post von Alice',      '2025-07-01 10:00:00', NULL),
  (3, 1, 'Noch ein Post vom Admin',    '2025-07-01 11:00:00', NULL),
  (4, 3, 'Bobs Gedanken zum Tag',      '2025-07-01 12:00:00', NULL);

-- -----------------------------
-- Kommentare (inkl. Antwort-Thread)
-- -----------------------------
INSERT INTO kommentar (id, nutzer_id, post_id, parent_comment_id, text, datumZeit) VALUES
  (1, 2, 1, NULL, 'Glückwunsch zum ersten Post! 🎈', '2025-07-01 09:05:00'),
  (2, 3, 1, 1,    'Ich stimme Alice zu! 👍',         '2025-07-01 09:06:00'),
  (3, 1, 2, NULL, 'Danke für das Update, Alice! 😊', '2025-07-01 10:05:00');

-- -----------------------------
-- Reaktionen
-- -----------------------------
INSERT INTO Reaktion (nutzer_id, post_id, reaktionsTyp) VALUES
  (2, 1, 'Herz'),
  (3, 1, 'Daumen Hoch'),
  (1, 2, 'Lachen'),
  (3, 2, 'Herz'),
  (2, 4, 'Fragezeichen');

SET FOREIGN_KEY_CHECKS = 1; 