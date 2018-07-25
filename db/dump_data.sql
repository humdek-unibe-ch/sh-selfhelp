-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 25, 2018 at 02:56 PM
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
(0000000002, 0000000003, 1, 0, 0, 0),
(0000000002, 0000000004, 1, 0, 0, 0),
(0000000002, 0000000006, 1, 0, 0, 0),
(0000000002, 0000000007, 1, 0, 0, 0),
(0000000002, 0000000008, 1, 0, 0, 0),
(0000000002, 0000000009, 1, 0, 0, 0),
(0000000002, 0000000010, 1, 0, 0, 0),
(0000000002, 0000000011, 1, 0, 0, 0),
(0000000002, 0000000012, 1, 0, 0, 0),
(0000000002, 0000000013, 1, 0, 0, 0);

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`) VALUES
(0000000001, 'title'),
(0000000002, 'label'),
(0000000003, 'url'),
(0000000004, 'user_label'),
(0000000005, 'pw_label'),
(0000000006, 'login_action_label'),
(0000000007, 'reset_pw_action_label'),
(0000000008, 'login_title'),
(0000000009, 'intro_title'),
(0000000010, 'intro_text'),
(0000000011, 'alert_fail'),
(0000000012, 'content'),
(0000000013, 'text'),
(0000000015, 'progress_label'),
(0000000016, 'continue_label'),
(0000000017, 'subtitle'),
(0000000018, 'back'),
(0000000019, 'next'),
(0000000020, 'alt'),
(0000000021, 'caption'),
(0000000022, 'caption_title'),
(0000000023, 'url_keyword'),
(0000000024, 'type'),
(0000000025, 'source'),
(0000000026, 'right_label'),
(0000000027, 'wrong_label'),
(0000000028, 'right_content'),
(0000000029, 'wrong_content'),
(0000000030, 'pw_confirm_label'),
(0000000031, 'pw_change_action_label'),
(0000000032, 'alert_success'),
(0000000033, 'pw_change_title'),
(0000000034, 'delete_title'),
(0000000035, 'delete_label'),
(0000000036, 'delete_content'),
(0000000037, 'delete_confirm_label'),
(0000000038, 'delete_cancel_label'),
(0000000039, 'delete_confirm_content'),
(0000000040, 'alert_pw_fail'),
(0000000041, 'alert_pw_success'),
(0000000042, 'alert_del_fail'),
(0000000043, 'alert_del_success');

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `locale`, `language`) VALUES
(0000000001, 'all', 'All languages'),
(0000000002, 'de-CH', 'Deutsch (Schweiz)');

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
(0000000009, 'profile', '/profil', 0000000012, 1, NULL),
(0000000010, 'missing', NULL, NULL, NULL, NULL),
(0000000011, 'login', '/login', 0000000012, 10, NULL),
(0000000012, 'profile-link', NULL, NULL, NULL, NULL),
(0000000013, 'session', '/sitzungen/[i:id]', 0000000006, NULL, NULL);

--
-- Dumping data for table `pages_fields_translation`
--

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
(0000000001, 0000000002, 0000000002, 'Schlaf Coach'),
(0000000002, 0000000002, 0000000002, 'AGB'),
(0000000003, 0000000002, 0000000002, 'Impressum'),
(0000000004, 0000000002, 0000000002, 'Disclaimer'),
(0000000005, 0000000002, 0000000002, 'Login'),
(0000000005, 0000000004, 0000000002, 'Benutzername'),
(0000000005, 0000000005, 0000000002, 'Passwort'),
(0000000005, 0000000006, 0000000002, 'Anmelden'),
(0000000005, 0000000007, 0000000002, 'Passwort vergessen?'),
(0000000005, 0000000008, 0000000002, 'Bitte einloggen'),
(0000000005, 0000000009, 0000000002, 'Anmeldung zum Schlaf Coach'),
(0000000005, 0000000010, 0000000002, 'Kurzer Enführungstext der etwas über das Projekt erzählt.'),
(0000000005, 0000000011, 0000000002, 'Der Benutzername oder das Passwort ist nicht korrekt.'),
(0000000006, 0000000001, 0000000002, 'Überblick über die Sitzungen'),
(0000000006, 0000000002, 0000000002, 'Sitzungen'),
(0000000006, 0000000013, 0000000002, 'Für eine Sitzung sollten Sie sich eine Woche Zeit nehmen.\r\nWichtig ist, dass Sie die in den Sitzungen eingeführten Übungen so oft wie möglich wiederholen.\r\n\r\nSie können jederzeit auf bereits bearbeitete Sitzungen klicken, um deren Bearbeitung zu wiederholen.'),
(0000000006, 0000000015, 0000000002, 'Fortschritt'),
(0000000006, 0000000016, 0000000002, 'Fortsetzen'),
(0000000007, 0000000002, 0000000002, 'Protokolle'),
(0000000008, 0000000002, 0000000002, 'Kontakt'),
(0000000009, 0000000002, 0000000002, 'Einstellungen'),
(0000000009, 0000000004, 0000000002, 'Email Adresse'),
(0000000009, 0000000005, 0000000002, 'Neues Passwort'),
(0000000009, 0000000030, 0000000002, 'Neues Passwort wiederholen'),
(0000000009, 0000000031, 0000000002, 'Ändern'),
(0000000009, 0000000033, 0000000002, 'Passwort ändern'),
(0000000009, 0000000034, 0000000002, 'Account löschen'),
(0000000009, 0000000035, 0000000002, 'Löschen'),
(0000000009, 0000000036, 0000000002, 'Alle Benutzerdaten werden gelöscht.\r\nDas Löschen des Accounts ist permanent und kann nicht rückganging gemacht werden!'),
(0000000009, 0000000037, 0000000002, 'Löschen bestätigen'),
(0000000009, 0000000038, 0000000002, 'Abbrechen'),
(0000000009, 0000000039, 0000000002, 'Wollen sie ihren Account wirklich löschen? Bestätigen Sie dies indem Sie ihre email Adresse eingeben.'),
(0000000009, 0000000040, 0000000002, 'Das Passwort konnte nicht geändert werden.'),
(0000000009, 0000000041, 0000000002, 'Das Passwort wurde erfolgreich geändert.'),
(0000000009, 0000000042, 0000000002, 'Die Benutzerdaten konnten nicht gelöscht werden.'),
(0000000009, 0000000043, 0000000002, 'Die Benutzerdaten wurden erfolgreich gelöscht.'),
(0000000010, 0000000002, 0000000002, 'Seite nicht gefunden'),
(0000000011, 0000000002, 0000000002, 'Abmelden'),
(0000000012, 0000000002, 0000000002, 'Profil'),
(0000000013, 0000000002, 0000000002, 'Sitzung'),
(0000000013, 0000000018, 0000000002, 'Zurück'),
(0000000013, 0000000019, 0000000002, 'Weiter');

--
-- Dumping data for table `pages_sections`
--

INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES
(0000000001, 0000000009, NULL),
(0000000010, 0000000012, NULL);

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `id_styles`, `name`, `owner`) VALUES
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
(0000000015, 0000000004, 'go-home', NULL),
(0000000016, 0000000014, 'session1', NULL),
(0000000017, 0000000014, 'session2', NULL),
(0000000018, 0000000014, 'session3', NULL),
(0000000019, 0000000014, 'session1.1', NULL),
(0000000020, 0000000014, 'session1.2', NULL),
(0000000021, 0000000014, 'session1.3', NULL),
(0000000023, 0000000014, 'session2.1', NULL),
(0000000025, 0000000016, 'session-navigation', NULL),
(0000000026, 0000000008, 'session1-content', NULL),
(0000000027, 0000000008, 'session1.1-content', NULL),
(0000000028, 0000000017, 'session1.1-alert', NULL),
(0000000029, 0000000008, 'session1.1-alert-content', NULL),
(0000000030, 0000000008, 'session1.2-content', NULL),
(0000000031, 0000000017, 'session1.2-alert', NULL),
(0000000032, 0000000008, 'session1.2-alert-contnet', NULL),
(0000000033, 0000000008, 'session1.3-content1', NULL),
(0000000034, 0000000008, 'session1.3-content2', NULL),
(0000000035, 0000000017, 'session1.3-alert', NULL),
(0000000036, 0000000008, 'session1.3-alert-content', NULL),
(0000000037, 0000000025, 'session1.3-alert-figure', NULL),
(0000000038, 0000000014, 'session2.2', NULL),
(0000000039, 0000000014, 'session2.3', NULL),
(0000000040, 0000000014, 'session2.4', NULL),
(0000000041, 0000000008, 'session2.4-content', NULL),
(0000000042, 0000000026, 'session2.4-video', NULL),
(0000000043, 0000000027, 'session2.4-video-source1', NULL),
(0000000044, 0000000027, 'session2.4-video-source2', NULL),
(0000000045, 0000000017, 'session2.4-alert', NULL),
(0000000046, 0000000008, 'session2.4-alert-content', NULL),
(0000000047, 0000000017, 'session2.1-alert', NULL),
(0000000048, 0000000017, 'session2.1-alert-alert', NULL),
(0000000049, 0000000008, 'session2.1-alert-alert-content', NULL),
(0000000050, 0000000014, 'session2.11', NULL),
(0000000051, 0000000028, 'session2.11-quiz1', NULL),
(0000000052, 0000000028, 'session2.11-quiz2', NULL);

--
-- Dumping data for table `sections_fields_translation`
--

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `content`) VALUES
(0000000004, 0000000013, 0000000002, 'Kein Zugriff'),
(0000000005, 0000000013, 0000000002, 'Sie haben keine Zugriffsrechte für diese Seite.'),
(0000000006, 0000000013, 0000000002, 'Um diese Seite zu erreichen müssen Sie eingeloggt sein.'),
(0000000007, 0000000002, 0000000002, 'Zurück'),
(0000000007, 0000000003, 0000000001, ':back'),
(0000000008, 0000000002, 0000000002, 'Zum Login'),
(0000000008, 0000000003, 0000000001, 'login'),
(0000000010, 0000000013, 0000000002, 'Willkommen zum Schlaf Coach'),
(0000000011, 0000000013, 0000000002, 'Eine kurze Enführung zum Schlaf Coach.'),
(0000000013, 0000000013, 0000000002, 'Seite nicht gefunden'),
(0000000014, 0000000013, 0000000002, 'Diese Seite konnte leider nicht gefunden werden.'),
(0000000015, 0000000002, 0000000002, 'Zur Startseite'),
(0000000015, 0000000003, 0000000001, 'home'),
(0000000016, 0000000001, 0000000002, 'Einführung'),
(0000000017, 0000000001, 0000000002, 'Zum Schlafexperten werden'),
(0000000018, 0000000001, 0000000002, 'Schlafrestriktion: Bettzeit beschränken und Schlafeffizienz verbessern'),
(0000000019, 0000000001, 0000000002, 'Wie steht es mit Ihrer Motivation?'),
(0000000020, 0000000001, 0000000002, 'Warum die Anstrengung auf sich nehmen?'),
(0000000021, 0000000001, 0000000002, 'LOHNT es sich, das Programm durchzuführen?'),
(0000000023, 0000000001, 0000000002, 'Funktionen des Schlafs'),
(0000000025, 0000000001, 0000000002, 'Sitzung'),
(0000000026, 0000000013, 0000000002, '![zum Schlafexperten werden](ZumSchlafexpertenWerden.jpg \"Zum Schlafexperten werden\"){width:300px; float:right}\r\nSchlaf ist ein universelles Phänomen. Nicht nur wir Menschen, sondern auch alle Lebewesen schlafen. Kennzeichnend für uns Menschen ist, dass wir uns ideale Bedingungen zum Schlafen geschaffen haben. Unser Schlafzimmer ist sicher, gut temperiert, lässt sich verdunkeln und das Bett ist bequem. Legen wir uns schlafen, lauert auf uns kein wildes Tier. Ganz im Gegensatz zur freien Natur!\r\n\r\nObwohl wir ein Drittel unseres Lebens schlafend verbringen und wir häufig über unseren Schlaf diskutieren, sind wir nicht automatisch Schlafexperten. Dies wird uns dann bewusst, wenn der Schlaf nicht mehr richtig zu \"funktionieren\" scheint: Wenn wir Schwierigkeiten haben einzuschlafen, durchzuschlafen oder immer früh Morgens in der Dunkelheit aufwachen und nicht wieder einschlafen können. Ebenfalls kursieren in unserer Gesellschaft auch viele Schlafmythen, wie z.B. „wer vor Mitternacht nicht schlafen geht, hat einen weniger erholsamen Schlaf“ oder „nach einer schlechten Nacht ist man nicht leistungsfähig“. Diese sind oft inkorrekt und vermiesen uns den Schlaf noch zusätzlich. Denn falsche Überzeugungen können nicht nur unnötig Ängste erzeugen, sondern verstärken die Schlafprobleme.\r\n\r\nZiel dieses Online Tools ist es, Ihnen Wissen und Werkzeug über den Schlaf und Ihre Schlafstörung mitzugeben, welche Ihnen den Umgang mit Ihrer Schlafstörung erleichtern und Ihren Schlaf verbessern sollen. Schritt für Schritt werden Sie zum Schlafexperten.\r\n\r\nBevor Sie aber mit dem ersten Modul starten, geht es zunächst um Ihre MOTIVATION mit dem Schlafcoach zu arbeiten.'),
(0000000027, 0000000013, 0000000002, '#### Üben ist der Schlüssel zum Erfolg\r\n![motivation](motivation2.jpg \"Motivation\"){width:300px; float:right}\r\nDas Wichtigste zuerst: Üben ist der Schlüssel zum Erfolg! Die Übungen und Techniken, die wir in diesem Online Programm einführen, wirken nicht wie Pillen. Sie können nicht einfach geschluckt werden wie ein Aspirin bei Kopfschmerzen. Sie müssen intensiv erlernt und Teil Ihres Alltags werden. Genauso wie Sie viel üben müssen, wenn Sie gut Klavier spielen wollen, oder wenn Sie Ihre Muskeln trainieren, führt der Weg zum Erfolg auch in diesem Programm über Üben, Üben und Üben.\r\n\r\n#### Sich motivieren können\r\n\r\nVielleicht sind Sie wie viele andere auch: Nicht sehr gut darin, sich immer wieder für teils auch unangenehme Dinge wie das Üben bestimmter Aufgaben zu motivieren. Motivation ist deshalb ein wichtiger Aspekt des vorliegenden Programms. Wenn Sie Ihre Motivation zur Veränderung aufrechterhalten können, werden Ihnen die Übungen leicht fallen. Wenn Ihre Motivation nachlässt, wird es schwieriger. Wahrscheinlich wird Ihre Motivation im Verlauf des Programms manchmal zu und manchmal abnehmen. Wenn Sie mit einer Übung erfolgreich sind - und wir hoffen, dass Sie viele erfolgreiche Erfahrungen machen -, wird Ihre Motivation steigen. Wenn Sie einen Misserfolg erleben (und zweifellos wird das auch vorkommen), wird Ihre Motivation nachlassen. Dann ist es besonders wichtig, sich wieder motivieren zu können. '),
(0000000029, 0000000013, 0000000002, 'In dieser ersten Sitzung werden deshalb zunächst zwei Fragen präsentiert, die Ihnen helfen können, Ihre Motivation zu steigern und aufrechtzuerhalten:\r\n\r\n - Warum soll überhaupt die Anstrengung unternommen werden an den Schlafstörungen zu arbeiten?\r\n - Lohnt es sich überhaupt, die Anstrengung zu unternehmen und das Programm intensiv durchzuarbeiten?'),
(0000000030, 0000000013, 0000000002, '![anstrengung](anstrengung.jpg \"Anstrengung\"){width:300px; float:right; z-index:10; position:relative}\r\nWie können wir uns über längere Zeit für eine anstrengende Aufgabe wie das Bewältigen von Schlafstörungen motivieren? Wichtig ist, sich immer wieder bewusst zu machen, warum überhaupt die Anstrengung unternommen und an den Schlafstörungen gearbeitet werden soll! Was spricht eigentlich dafür, sich zu verändern? Wie und wann schränken die Schlafstörungen Sie in Ihrem Leben ein? Wie wäre Ihr Leben, wenn Sie besser schlafen würden?\r\n\r\nSich zu Beginn dieser längerfristigen Aufgabe mit solchen Fragen zu beschäftigen, lohnt sich! Denken Sie an einen Menschen, der abnehmen möchte. Dieser Mensch wird motivierter sein, eine Diät zu befolgen oder Sport zu treiben, wenn er sich immer wieder klar macht, dass er zum Beispiel dank der Anstrengung in der Badehose eine gute Figur machen wird. '),
(0000000032, 0000000013, 0000000002, 'Bevor wir mit dem eigentlichen Programm beginnen, möchten wir Sie bitten sich etwas Zeit zu nehmen und vertieft darüber nachzudenken...\r\n\r\n 1. ...wie die Schlafstörung Ihr Leben beeinflusst. Wie und wann werden Sie in Ihrem Leben durch die Schlafstörung eingeschränkt? Denken Sie an offensichtliche Aspekte: An Situationen oder Aktivitäten auf die Sie aufgrund der Schlafstörung verzichten oder nur unter sehr erschwerten Bedingungen bewältigen können?\r\n 2. ...wie Ihr Leben wäre, was Sie machen würden, was sich verändern würde, wenn Sie besser und erholter schlafen würden. Denken Sie an die Zukunft bzw. an eine Zukunft, in welcher Sie die Schlafstörung überwunden haben. \r\n\r\n**Tipp:** Schreiben Sie Ihre Antworten auf die beiden Fragen auf. Falls Sie im Laufe des Programms Mühe haben sich zu motivieren, können Sie diese hervorholen und lesen.'),
(0000000033, 0000000013, 0000000002, '#### Erwarten Sie, dass Ihnen das Programm hilft?\r\n\r\nSich bewusst zu machen, warum sich eine Anstrengung lohnt, reicht noch nicht, um sich für eine Aufgabe motivieren zu können. Nur wenn Sie auch daran glauben, nur wenn Sie erwarten, dass Ihnen dieses Programm helfen kann, werden Sie sich motivieren können. Ob es sich lohnt, das Programm intensiv durchzuarbeiten, können Sie jetzt noch nicht wissen. Deshalb möchten wir Ihnen ein paar Informationen zur Wirksamkeit entsprechender Programme geben.'),
(0000000034, 0000000013, 0000000002, '#### Zwei Drittel der Teilnehmer profitieren stark\r\n\r\nÜber verschiedene Studien hinweg konnte gezeigt werden, dass jeweils zwei Drittel der Teilnehmer von der Nutzung der Programme stark profitierten und den Schlaf sowie die Lebensqualität bedeutsam verbessern können.\r\n\r\n#### Wie können Sie die Wirkung beeinflussen?\r\n\r\nWie können Sie beeinflussen, dass Sie zu jenen Teilnehmern gehören, die gut vom Programm profitieren können? In der bisherigen Forschung konnten wir generell feststellen, dass je intensiver ein Programm durchgearbeitet wird (z.B. je mehr die im Programm eingeführten Techniken und Übungen angewendet werden), desto stärker können Teilnehmer profitieren. Deshalb ist uns Ihre Motivation auch so wichtig! Je konzentrierter und konsequenter Sie das Programm durcharbeiten, und je mehr Sie üben, desto besser werden Sie davon profitieren können.\r\n\r\n#### Legen Sie fest, wann Sie das Programm verwenden wollen\r\n\r\nEs lohnt sich, zu Beginn etwas Verbindlichkeit zu schaffen und sich festzulegen, wann und wie oft Sie mit dem Programm arbeiten können und möchten. Damit Sie das können, wird im Folgenden erklärt, wie das Programm funktioniert und wie Sie es nutzen sollten. '),
(0000000036, 0000000013, 0000000002, '#### Verschiedene Studien belegen die Wirkung\r\n\r\nInternetbasierte Interventionen werden seit einigen Jahren weltweit erforscht. Dabei haben sich Programme zur Behandlung von Schlafstörungen als ausgesprochen wirksam erwiesen. Aus bisherigen Studien wissen wir, dass Teilnehmer in der Regel stark von Programmen profitieren, die auf den gleichen therapeutischen Prinzipien aufbauen, wie das hier vorliegende Programm. Auf Nachfrage senden wir Ihnen die in Fachzeitschriften publizierten Studien gerne zu. '),
(0000000037, 0000000001, 0000000001, 'Guided Internet-delivered cognitive behavioural treatment for insomnia: a randomized trial'),
(0000000037, 0000000020, 0000000002, 'Beispiel einer Studie.'),
(0000000037, 0000000021, 0000000002, 'Beispiel einer Studie, die in der renommierten Fachzeitschrift *Psychological Medicine* erschienen ist.'),
(0000000037, 0000000025, 0000000001, 'Artikel_Psychological_Medicine.png'),
(0000000038, 0000000001, 0000000002, 'Was geschieht während des Schlafens?'),
(0000000039, 0000000001, 0000000002, 'Die Schlafphasen und unser Gehirn'),
(0000000040, 0000000001, 0000000002, 'Zirkadianer Rhythmus und Schlaf-Wach-Rhythmus'),
(0000000041, 0000000013, 0000000002, 'Normalerweise orientiert sich der Mensch an äußeren Zeitgebern (Uhr, Sonnenlicht, aber auch an sozialen Zeitgebern wie dem gemeinsamen Nachtessen) und passt den Tagesrhythmus an. Wir synchronisieren uns insbesondere mit der Sonne, ein Vorgang der auch als zirkadianer Rhythmus bezeichnet wird und unseren Schlaf-Wach-Rhythmus beeinflusst. Beispielsweise weiss man, dass das Sonnenlicht, welches durch die Augen aufgenommen wird, schlafhemmende oder schlaffördernde Prozesse im Gehirn reguliert. Grundsätzlich passen sich viele Prozesse, die im Körper ablaufen, dem zirkadianen Rhythmus an.\r\n\r\nDer Schlaf-Wach-Rhythmus wird durch zwei Faktoren besonders beeinflusst. Dies sind der eben erwähnte **zirkadiane Rhythmus (C)** sowie der **Schlafdruck (S)**. Unter Schlafdruck wird im Wesentlichen das Bedürfnis zu Schlafen verstanden. Das Zusammenspiel der beiden Faktoren soll Ihnen anhand des Videos verdeutlicht werden.'),
(0000000043, 0000000024, 0000000001, 'video/mp4'),
(0000000043, 0000000025, 0000000001, 'Zugeschnitten_Schlafvideo.mp4'),
(0000000044, 0000000024, 0000000001, 'video/ogg'),
(0000000044, 0000000025, 0000000001, 'Zugeschnitten_Schlafvideo.ogg'),
(0000000046, 0000000013, 0000000002, 'Was Sie mitnehmen sollten...\r\n\r\n1. Es ist erstrebenswert, einen Schlaf-Wach-Rhythmus anzustreben, der in Einklang mit dem zirkadianen Rhythmus ist.\r\n2. Nach schlechten Nächten folgen häufig auch gute Nächte. Dies aufgrund des Schlafdrucks und des vermehrt auftretenden Tiefschlafs.\r\n'),
(0000000049, 0000000013, 0000000001, 'A highlighting box inside a highlighting box.'),
(0000000050, 0000000001, 0000000002, 'Quiz zu den Schlafmythen'),
(0000000051, 0000000001, 0000000002, '**Mythos:** Der Schlaf verläuft nach dem Einschlafen gradlinig und durchläuft zuerst tiefe und dann gegen den Morgen wieder oberflächlichere Schlafphasen.'),
(0000000051, 0000000028, 0000000002, 'Dies hätte durchaus sein können, aber der Schlaf ist ein aktiver Prozess. Er verläuft in einem ungefähr 90-100 minütigen Zyklus und durchläuft verschiedene Schlafphasen und Schlaf-Tiefen.'),
(0000000051, 0000000029, 0000000002, 'Sehr gute Antwort. Der Schlaf ist ein aktiver Prozess. Er verläuft in einem ungefähr 90-100 minütigen Zyklus und durchläuft verschiedene Schlafphasen und Schlaf-Tiefen.'),
(0000000052, 0000000001, 0000000002, '**Mythos:** Der meiste Anteil des Schlafs in der Nacht muss aus Tiefschlaf bestehen.'),
(0000000052, 0000000028, 0000000002, 'Leider ist Ihre Antwort nicht korrekt. Es wird vor allem in der ersten Hälfte des Schlafs vermehrt Zeit im Tiefschlaf verbracht, d.h. pro Nacht ungefähr 15 bis 25%. Klar ist, dass der Tiefschlaf sehr wichtig ist jedoch nur einen kleinen Anteil unseres Schlafes ausmacht.'),
(0000000052, 0000000029, 0000000002, 'Falsch ist die richtige Antwort. Es wird vor allem in der ersten Hälfte des Schlafs vermehrt Zeit im Tiefschlaf verbracht, d.h. pro Nacht ungefähr 15 bis 25%. Klar ist, dass der Tiefschlaf sehr wichtig ist jedoch nur einen kleinen Anteil unseres Schlafes ausmacht.');

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
(0000000012, 0000000015, 3),
(0000000016, 0000000026, NULL),
(0000000019, 0000000027, 1),
(0000000019, 0000000028, 2),
(0000000020, 0000000030, 1),
(0000000020, 0000000031, 2),
(0000000021, 0000000033, 1),
(0000000021, 0000000034, 3),
(0000000021, 0000000035, 2),
(0000000023, 0000000047, NULL),
(0000000028, 0000000029, NULL),
(0000000031, 0000000032, NULL),
(0000000035, 0000000036, 1),
(0000000035, 0000000037, 2),
(0000000040, 0000000041, 1),
(0000000040, 0000000042, 2),
(0000000040, 0000000045, 3),
(0000000042, 0000000043, 1),
(0000000042, 0000000044, 2),
(0000000045, 0000000046, NULL),
(0000000047, 0000000048, NULL),
(0000000048, 0000000049, NULL),
(0000000050, 0000000051, 1),
(0000000050, 0000000052, 2);

--
-- Dumping data for table `sections_navigation`
--

INSERT INTO `sections_navigation` (`parent`, `child`, `position`) VALUES
(0000000016, 0000000019, 1),
(0000000016, 0000000020, 2),
(0000000016, 0000000021, 3),
(0000000017, 0000000023, 1),
(0000000017, 0000000038, 2),
(0000000017, 0000000039, 3),
(0000000017, 0000000040, 4),
(0000000017, 0000000050, 11),
(0000000025, 0000000016, 1),
(0000000025, 0000000017, 2),
(0000000025, 0000000018, 3);

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `name`) VALUES
(0000000003, 'jumbotron'),
(0000000004, 'button'),
(0000000005, 'link'),
(0000000007, 'card'),
(0000000008, 'plaintext'),
(0000000009, 'title-1'),
(0000000010, 'title-2'),
(0000000011, 'title-3'),
(0000000012, 'title-4'),
(0000000013, 'title-5'),
(0000000014, 'session'),
(0000000016, 'navigation'),
(0000000017, 'alert-primary'),
(0000000018, 'alert-secondary'),
(0000000019, 'alert-success'),
(0000000020, 'alert-danger'),
(0000000021, 'alert-warning'),
(0000000022, 'alert-info'),
(0000000023, 'alert-light'),
(0000000024, 'alert-dark'),
(0000000025, 'figure'),
(0000000026, 'video'),
(0000000027, 'video_source'),
(0000000028, 'quiz');

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
(0000000007, 0000000004),
(0000000007, 0000000005),
(0000000007, 0000000008),
(0000000007, 0000000009),
(0000000007, 0000000010),
(0000000007, 0000000011),
(0000000007, 0000000012),
(0000000007, 0000000013),
(0000000014, 0000000003),
(0000000014, 0000000004),
(0000000014, 0000000007),
(0000000014, 0000000008),
(0000000014, 0000000009),
(0000000014, 0000000010),
(0000000014, 0000000011),
(0000000014, 0000000012),
(0000000014, 0000000013),
(0000000026, 0000000027);

--
-- Dumping data for table `styles_fields`
--

INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES
(0000000003, 0000000012),
(0000000004, 0000000002),
(0000000004, 0000000003),
(0000000005, 0000000002),
(0000000005, 0000000003),
(0000000007, 0000000001),
(0000000007, 0000000012),
(0000000008, 0000000013),
(0000000009, 0000000013),
(0000000010, 0000000013),
(0000000011, 0000000013),
(0000000012, 0000000013),
(0000000013, 0000000013),
(0000000014, 0000000001),
(0000000014, 0000000012),
(0000000017, 0000000012),
(0000000018, 0000000012),
(0000000019, 0000000012),
(0000000020, 0000000012),
(0000000021, 0000000012),
(0000000022, 0000000012),
(0000000023, 0000000012),
(0000000024, 0000000012),
(0000000025, 0000000001),
(0000000025, 0000000020),
(0000000025, 0000000021),
(0000000025, 0000000022),
(0000000025, 0000000025),
(0000000026, 0000000012),
(0000000026, 0000000020),
(0000000027, 0000000003),
(0000000027, 0000000024),
(0000000028, 0000000001),
(0000000028, 0000000026),
(0000000028, 0000000027),
(0000000028, 0000000028),
(0000000028, 0000000029);

--
-- Dumping data for table `styles_fields_translation`
--

INSERT INTO `styles_fields_translation` (`id_styles`, `id_fields`, `id_languages`, `content`) VALUES
(0000000025, 0000000022, 0000000002, 'Abbildung'),
(0000000026, 0000000020, 0000000002, 'Ihr Browser unterstützt kein Video Tag.'),
(0000000028, 0000000026, 0000000002, 'Richtig'),
(0000000028, 0000000027, 0000000002, 'Falsch');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`) VALUES
(0000000001, 'guest', '$2y$10$YAGdZtBk.eLsC48iaNG2huAcjppEQ7EusPaJRdRYa86PPfv4FFEBG'),
(0000000002, 'me@mydomain.com', '$2y$10$cQiBxXfF4uvjCegE5DMobuzcn0uE93Wk47m0V6KAHFG5wXPd7nKf2');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
