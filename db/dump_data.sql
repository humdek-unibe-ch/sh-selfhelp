-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 19, 2018 at 10:56 AM
-- Server version: 5.7.22-0ubuntu18.04.1
-- PHP Version: 7.2.7-0ubuntu0.18.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sleepcoach`
--

--
-- Dumping data for table `acl`
--

INSERT INTO `acl` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
(0000000001, 0000000005, 1, 0, 0, 0),
(0000000001, 0000000010, 1, 0, 0, 0),
(0000000002, 0000000001, 1, 0, 0, 0),
(0000000002, 0000000002, 1, 0, 0, 0),
(0000000002, 0000000004, 1, 0, 0, 0),
(0000000002, 0000000008, 1, 0, 0, 0),
(0000000002, 0000000009, 1, 0, 0, 0),
(0000000002, 0000000010, 1, 0, 0, 0),
(0000000002, 0000000011, 1, 0, 0, 0);

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`) VALUES
(0000000001, 'title'),
(0000000002, 'label'),
(0000000003, 'url'),
(0000000004, 'user_label'),
(0000000005, 'pw_label'),
(0000000006, 'login_label'),
(0000000007, 'reset_pw_label'),
(0000000008, 'login_title'),
(0000000009, 'intro_title'),
(0000000010, 'intro_text'),
(0000000011, 'alert'),
(0000000012, 'content'),
(0000000013, 'text');

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `locale`, `language`) VALUES
(0000000001, 'de-CH', 'Deutsch (Schweiz)');

--
-- Dumping data for table `pages`
--

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
(0000000010, 'missing', '', NULL, NULL, NULL),
(0000000011, 'login', '/login', 0000000009, NULL, NULL);

--
-- Dumping data for table `pages_sections`
--

INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES
(0000000001, 0000000009, NULL),
(0000000005, 0000000001, NULL),
(0000000010, 0000000012, NULL);

--
-- Dumping data for table `pages_translation`
--

INSERT INTO `pages_translation` (`id`, `id_pages`, `id_languages`, `title`) VALUES
(0000000001, 0000000001, 0000000001, 'Schlaf Coach'),
(0000000002, 0000000005, 0000000001, 'Login'),
(0000000003, 0000000002, 0000000001, 'AGB'),
(0000000004, 0000000004, 0000000001, 'Disclaimer'),
(0000000005, 0000000003, 0000000001, 'Impressum'),
(0000000006, 0000000006, 0000000001, 'Sitzungen'),
(0000000007, 0000000007, 0000000001, 'Protokolle'),
(0000000008, 0000000008, 0000000001, 'Kontakt'),
(0000000009, 0000000009, 0000000001, 'Profil'),
(0000000010, 0000000010, 0000000001, 'Seite nicht gefunden'),
(0000000011, 0000000011, 0000000001, 'Abmelden');

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `id_styles`, `name`, `owner`) VALUES
(0000000001, 0000000001, 'login', NULL),
(0000000002, 0000000003, 'no-access-guest', NULL),
(0000000003, 0000000003, 'no-access', NULL),
(0000000004, 0000000009, 'no-access-title', NULL),
(0000000005, 0000000008, 'no-access-text', NULL),
(0000000006, 0000000008, 'no-access-guest-text', NULL),
(0000000007, 0000000004, 'go-back', NULL),
(0000000008, 0000000004, 'to-login', NULL),
(0000000009, 0000000003, 'home', NULL),
(0000000010, 0000000009, 'home-title', NULL),
(0000000011, 0000000008, 'home-text', NULL),
(0000000012, 0000000003, 'missing', NULL),
(0000000013, 0000000009, 'missing-title', NULL),
(0000000014, 0000000008, 'missing-text', NULL),
(0000000015, 0000000004, 'go-home', NULL);

--
-- Dumping data for table `sections_fields_translation`
--

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `content`) VALUES
(0000000001, 0000000004, 0000000001, 'Benutzername'),
(0000000001, 0000000005, 0000000001, 'Passwort'),
(0000000001, 0000000006, 0000000001, 'Anmelden'),
(0000000001, 0000000007, 0000000001, 'Passwort vergessen?'),
(0000000001, 0000000008, 0000000001, 'Bitte einloggen'),
(0000000001, 0000000009, 0000000001, 'Anmeldung zum Schlaf Coach'),
(0000000001, 0000000010, 0000000001, 'Kurzer Enführungstext der etwas über das Projekt erzählt.'),
(0000000001, 0000000011, 0000000001, 'Der Benutzername oder das Passwort ist nicht korrekt.'),
(0000000004, 0000000013, 0000000001, 'Kein Zugriff'),
(0000000005, 0000000013, 0000000001, 'Sie haben keine Zugriffsrechte für diese Seite.'),
(0000000006, 0000000013, 0000000001, 'Um diese Seite zu erreichen müssen Sie eingeloggt sein.'),
(0000000007, 0000000002, 0000000001, 'Zurück'),
(0000000007, 0000000003, NULL, ':back'),
(0000000008, 0000000002, 0000000001, 'Zum Login'),
(0000000008, 0000000003, NULL, 'login'),
(0000000010, 0000000013, 0000000001, 'Willkommen zum Schlaf Coach'),
(0000000011, 0000000013, 0000000001, 'Eine kurze Enführung zum Schlaf Coach.'),
(0000000013, 0000000013, 0000000001, 'Seite nicht gefunden'),
(0000000014, 0000000013, 0000000001, 'Diese Seite konnete leider nicht gefunden werden.'),
(0000000015, 0000000002, 0000000001, 'Zur Startseite'),
(0000000015, 0000000003, NULL, 'home');

--
-- Dumping data for table `sections_hierarchy`
--

INSERT INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
(0000000002, 0000000004, 1),
(0000000002, 0000000006, 2),
(0000000002, 0000000008, 3),
(0000000003, 0000000004, 1),
(0000000003, 0000000005, 2),
(0000000003, 0000000007, 3),
(0000000009, 0000000010, 1),
(0000000009, 0000000011, 2),
(0000000012, 0000000013, 1),
(0000000012, 0000000014, 2),
(0000000012, 0000000015, 3);

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `name`) VALUES
(0000000001, 'login'),
(0000000003, 'jumbotron'),
(0000000004, 'button'),
(0000000005, 'link'),
(0000000006, 'alert'),
(0000000007, 'card'),
(0000000008, 'plaintext'),
(0000000009, 'title1'),
(0000000010, 'title2'),
(0000000011, 'title3'),
(0000000012, 'title4'),
(0000000013, 'title5');

--
-- Dumping data for table `styles_content`
--

INSERT INTO `styles_content` (`id_styles`, `content`) VALUES
(0000000003, 0000000004),
(0000000003, 0000000005),
(0000000003, 0000000008),
(0000000003, 0000000009),
(0000000003, 0000000010),
(0000000003, 0000000011),
(0000000003, 0000000012),
(0000000003, 0000000013),
(0000000006, 0000000008),
(0000000006, 0000000009),
(0000000006, 0000000010),
(0000000006, 0000000011),
(0000000006, 0000000012),
(0000000006, 0000000013),
(0000000007, 0000000004),
(0000000007, 0000000005),
(0000000007, 0000000008),
(0000000007, 0000000009),
(0000000007, 0000000010),
(0000000007, 0000000011),
(0000000007, 0000000012),
(0000000007, 0000000013);

--
-- Dumping data for table `styles_fields`
--

INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES
(0000000001, 0000000004),
(0000000001, 0000000005),
(0000000001, 0000000006),
(0000000001, 0000000007),
(0000000001, 0000000008),
(0000000001, 0000000009),
(0000000001, 0000000010),
(0000000001, 0000000011),
(0000000003, 0000000012),
(0000000004, 0000000002),
(0000000004, 0000000003),
(0000000005, 0000000002),
(0000000005, 0000000003),
(0000000006, 0000000012),
(0000000007, 0000000001),
(0000000007, 0000000012),
(0000000008, 0000000013),
(0000000009, 0000000013),
(0000000010, 0000000013),
(0000000011, 0000000013),
(0000000012, 0000000013),
(0000000013, 0000000013);

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`) VALUES
(0000000001, 'guest', '$2y$10$YAGdZtBk.eLsC48iaNG2huAcjppEQ7EusPaJRdRYa86PPfv4FFEBG'),
(0000000002, 'me@mydomain.com', '$2y$10$wAbSIxVz.l75x/DkgGExZ.fqVfzoyPHJKK77OwTxZNLlKFq1hWVaq');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
