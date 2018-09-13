INSERT INTO `users` (`id`, `email`, `password`) VALUES
(0000000001, 'guest', '$2y$10$YAGdZtBk.eLsC48iaNG2huAcjppEQ7EusPaJRdRYa86PPfv4FFEBG');

INSERT INTO `pages` (`id`, `keyword`, `url`, `parent`, `nav_position`, `footer_position`) VALUES
(0000000001, 'home', '/', NULL, NULL, NULL),
(0000000002, 'agb', '/agb', NULL, NULL, 300),
(0000000003, 'impressum', '/impressum', NULL, NULL, 100),
(0000000004, 'disclaimer', '/disclaimer', NULL, NULL, 200),
(0000000005, 'login', '/login', NULL, NULL, NULL),
(0000000006, 'sessions', '/sitzungen', NULL, 100, NULL),
(0000000007, 'protocols', '/protokolle', NULL, 200, NULL),
(0000000008, 'contact', '/kontakt', NULL, 300, NULL),
(0000000009, 'profile', '/profil', NULL, NULL, NULL),
(0000000010, 'missing', '', NULL, NULL, NULL);

INSERT INTO `languages` (`id`, `locale`, `language`) VALUES
(0000000001, 'de-CH', 'Deutsch (Schweiz)');

INSERT INTO `acl` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
(0000000001, 0000000005, 1, 0, 0, 0),
(0000000001, 0000000010, 1, 0, 0, 0);

INSERT INTO `sections_translation` (`id`, `id_languages`, `title`, `content`, `link`) VALUES
(0000000001, 0000000001, 'Kein Zugriff', 'Um diese Seite zu erreichen müssen Sie eingeloggt sein.', 'Zum Login#login'),
(0000000002, 0000000001, 'Kein Zugriff', 'Sie haben keine Zugriffsrechte für diese Seite.', 'Zurück#:back'),
(0000000003, 0000000001, 'Seite nicht gefunden', 'Diese Seite konnete leider nicht gefunden werden.', 'Zurück#:back'),
(0000000004, 0000000001, '', 'Der Benutzername oder das Passwort ist nicht korrekt.', NULL);
