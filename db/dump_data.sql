-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 17, 2018 at 01:39 PM
-- Server version: 5.7.22-0ubuntu18.04.1
-- PHP Version: 7.2.5-0ubuntu0.18.04.1

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
(0000000002, 0000000010, 1, 0, 0, 0);

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
(0000000010, 'missing', '', NULL, NULL, NULL);

--
-- Dumping data for table `pages_sections`
--

INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `id_styles`, `position`) VALUES
(0000000001, 0000000003, 0000000001, 1),
(0000000002, 0000000004, 0000000003, 0),
(0000000004, 0000000004, 0000000001, 5),
(0000000004, 0000000005, 0000000003, 1),
(0000000010, 0000000006, 0000000001, NULL);

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
(0000000010, 0000000010, 0000000001, 'Seite nicht gefunden');

--
-- Dumping data for table `sections_translation`
--

INSERT INTO `sections_translation` (`id`, `id_languages`, `title`, `content`, `link`) VALUES
(0000000001, 0000000001, 'Kein Zugriff', 'Um diese Seite zu erreichen müssen Sie eingeloggt sein.', 'Zum Login#login'),
(0000000002, 0000000001, 'Kein Zugriff', 'Sie haben keine Zugriffsrechte für diese Seite.', 'Zurück#:back'),
(0000000003, 0000000001, 'Wilkommen zum Schlaf Coach', 'Eine kurze Enführung zum Schlaf Coach.', NULL),
(0000000004, 0000000001, 'Test Section 1', 'Test Section 1 Content', 'Zurück#:back'),
(0000000005, 0000000001, 'Test Section 2', 'Test Section 2 Content', NULL),
(0000000006, 0000000001, 'Seite nicht gefunden', 'Diese Seite konnete leider nicht gefunden werden.', 'Zurück#:back');

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `style`) VALUES
(0000000001, 'jumbotron'),
(0000000003, 'well');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`) VALUES
(0000000001, 'guest', '$2y$10$YAGdZtBk.eLsC48iaNG2huAcjppEQ7EusPaJRdRYa86PPfv4FFEBG'),
(0000000002, 'me@mydomain.com', '$2y$10$wAbSIxVz.l75x/DkgGExZ.fqVfzoyPHJKK77OwTxZNLlKFq1hWVaq');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
