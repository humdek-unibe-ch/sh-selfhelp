INSERT INTO `users` (`id`, `email`, `password`) VALUES
(0000000001, 'guest', '$2y$10$YAGdZtBk.eLsC48iaNG2huAcjppEQ7EusPaJRdRYa86PPfv4FFEBG'),
(NULL, 'me@mydomain.com', '$2y$10$wAbSIxVz.l75x/DkgGExZ.fqVfzoyPHJKK77OwTxZNLlKFq1hWVaq');

INSERT INTO `acl` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
(0000000001, 0000000005, 1, 0, 0, 0);

INSERT INTO `languages` (`id`, `locale`, `language`) VALUES (NULL, 'de-CH', 'Deutsch (Schweiz)');

INSERT INTO `pages` (`id`, `keyword`, `url`, `nav_position`, `footer_position`) VALUES
(NULL, 'home', '/', NULL, NULL),
(NULL, 'agb', '/agb', NULL, 300),
(NULL, 'impressum', '/impressum', NULL, 100),
(NULL, 'disclaimer', '/disclaimer', NULL, 200);
(NULL, 'login', '/login', NULL, NULL),
(NULL, 'sessions', '/sitzungen', 100, NULL),
(NULL, 'protocols', '/protokolle', 200, NULL);

INSERT INTO `pages_translation` (`id`, `id_pages`, `id_languages`, `title`) VALUES
(NULL, 0000000001, 0000000001, 'Schlaf Coach'),
(NULL, 0000000005, 0000000001, 'Log In'),
(NULL, 0000000002, 0000000001, 'AGB'),
(NULL, 0000000004, 0000000001, 'Disclaimer'),
(NULL, 0000000003, 0000000001, 'Impressum'),
(NULL, 0000000006, 0000000001, 'Sitzungen'),
(NULL, 0000000007, 0000000001, 'Protokolle'),
(NULL, 0000000008, 0000000001, 'Kontakt');
