-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 19, 2020 at 02:47 PM
-- Server version: 5.7.23-log
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `selfhelp`
--

-- --------------------------------------------------------

--
-- Table structure for table `acl_groups`
--

DROP TABLE IF EXISTS `acl_groups`;
CREATE TABLE IF NOT EXISTS `acl_groups` (
  `id_groups` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_groups`,`id_pages`),
  KEY `id_pages` (`id_pages`) USING BTREE,
  KEY `id_groups` (`id_groups`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_groups`
--

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
(0000000001, 0000000001, 1, 1, 1, 1),
(0000000001, 0000000002, 1, 1, 1, 1),
(0000000001, 0000000003, 1, 0, 1, 0),
(0000000001, 0000000004, 1, 1, 1, 1),
(0000000001, 0000000005, 1, 0, 1, 0),
(0000000001, 0000000006, 1, 1, 1, 1),
(0000000001, 0000000007, 1, 1, 1, 1),
(0000000001, 0000000008, 1, 1, 1, 1),
(0000000001, 0000000009, 1, 0, 0, 0),
(0000000001, 0000000010, 1, 0, 0, 0),
(0000000001, 0000000011, 1, 1, 0, 0),
(0000000001, 0000000012, 1, 0, 1, 0),
(0000000001, 0000000013, 1, 0, 0, 1),
(0000000001, 0000000014, 1, 0, 0, 0),
(0000000001, 0000000015, 1, 1, 0, 0),
(0000000001, 0000000016, 1, 0, 1, 0),
(0000000001, 0000000017, 1, 0, 0, 1),
(0000000001, 0000000018, 1, 0, 0, 0),
(0000000001, 0000000019, 1, 1, 0, 0),
(0000000001, 0000000020, 1, 0, 1, 0),
(0000000001, 0000000021, 1, 0, 0, 1),
(0000000001, 0000000022, 1, 0, 0, 0),
(0000000001, 0000000023, 1, 0, 0, 0),
(0000000001, 0000000024, 1, 0, 0, 0),
(0000000001, 0000000025, 1, 1, 0, 0),
(0000000001, 0000000026, 1, 0, 1, 0),
(0000000001, 0000000027, 1, 0, 0, 1),
(0000000001, 0000000028, 1, 0, 0, 0),
(0000000001, 0000000030, 1, 1, 1, 1),
(0000000001, 0000000031, 1, 1, 1, 1),
(0000000001, 0000000032, 1, 1, 1, 1),
(0000000001, 0000000033, 1, 1, 1, 1),
(0000000001, 0000000035, 1, 1, 1, 1),
(0000000001, 0000000036, 1, 1, 0, 0),
(0000000001, 0000000037, 1, 0, 1, 0),
(0000000001, 0000000042, 1, 0, 0, 1),
(0000000001, 0000000043, 1, 0, 1, 0),
(0000000001, 0000000045, 1, 1, 0, 0),
(0000000001, 0000000046, 1, 0, 1, 0),
(0000000001, 0000000047, 1, 0, 1, 0),
(0000000001, 0000000048, 1, 1, 1, 1),
(0000000001, 0000000049, 1, 0, 1, 0),
(0000000001, 0000000050, 1, 0, 1, 0),
(0000000001, 0000000051, 1, 1, 1, 1),
(0000000001, 0000000052, 1, 1, 1, 1),
(0000000001, 0000000053, 1, 1, 1, 1),
(0000000001, 0000000054, 1, 0, 0, 0),
(0000000001, 0000000055, 1, 0, 1, 0),
(0000000001, 0000000056, 1, 1, 1, 1),
(0000000001, 0000000057, 1, 1, 1, 1),
(0000000001, 0000000058, 1, 0, 0, 0),
(0000000002, 0000000001, 1, 0, 0, 0),
(0000000002, 0000000002, 1, 0, 0, 0),
(0000000002, 0000000003, 1, 0, 0, 0),
(0000000002, 0000000004, 1, 0, 0, 0),
(0000000002, 0000000005, 1, 0, 0, 0),
(0000000002, 0000000006, 1, 0, 0, 0),
(0000000002, 0000000007, 1, 0, 0, 0),
(0000000002, 0000000008, 1, 0, 0, 0),
(0000000002, 0000000009, 1, 0, 0, 0),
(0000000002, 0000000010, 0, 0, 0, 0),
(0000000002, 0000000011, 0, 0, 0, 0),
(0000000002, 0000000012, 0, 0, 0, 0),
(0000000002, 0000000013, 0, 0, 0, 0),
(0000000002, 0000000014, 1, 0, 0, 0),
(0000000002, 0000000015, 1, 1, 0, 0),
(0000000002, 0000000016, 1, 0, 1, 0),
(0000000002, 0000000017, 0, 0, 0, 0),
(0000000002, 0000000018, 1, 0, 0, 0),
(0000000002, 0000000019, 1, 1, 0, 0),
(0000000002, 0000000020, 1, 0, 1, 0),
(0000000002, 0000000021, 0, 0, 0, 0),
(0000000002, 0000000022, 0, 0, 0, 0),
(0000000002, 0000000023, 0, 0, 0, 0),
(0000000002, 0000000024, 0, 0, 0, 0),
(0000000002, 0000000025, 0, 0, 0, 0),
(0000000002, 0000000026, 0, 0, 0, 0),
(0000000002, 0000000027, 0, 0, 0, 0),
(0000000002, 0000000028, 1, 0, 0, 0),
(0000000002, 0000000030, 1, 0, 0, 0),
(0000000002, 0000000031, 1, 0, 0, 0),
(0000000002, 0000000032, 1, 0, 0, 0),
(0000000002, 0000000033, 1, 0, 0, 0),
(0000000002, 0000000035, 1, 0, 0, 0),
(0000000002, 0000000036, 1, 1, 0, 0),
(0000000002, 0000000037, 0, 0, 0, 0),
(0000000002, 0000000056, 1, 0, 0, 0),
(0000000002, 0000000057, 1, 1, 0, 0),
(0000000003, 0000000001, 1, 0, 0, 0),
(0000000003, 0000000002, 1, 0, 0, 0),
(0000000003, 0000000003, 1, 0, 0, 0),
(0000000003, 0000000004, 1, 0, 0, 0),
(0000000003, 0000000005, 1, 0, 0, 0),
(0000000003, 0000000006, 1, 0, 0, 0),
(0000000003, 0000000007, 1, 0, 0, 0),
(0000000003, 0000000008, 1, 0, 0, 0),
(0000000003, 0000000009, 0, 0, 0, 0),
(0000000003, 0000000010, 0, 0, 0, 0),
(0000000003, 0000000011, 0, 0, 0, 0),
(0000000003, 0000000012, 0, 0, 0, 0),
(0000000003, 0000000013, 0, 0, 0, 0),
(0000000003, 0000000014, 0, 0, 0, 0),
(0000000003, 0000000015, 0, 0, 0, 0),
(0000000003, 0000000016, 0, 0, 0, 0),
(0000000003, 0000000017, 0, 0, 0, 0),
(0000000003, 0000000018, 0, 0, 0, 0),
(0000000003, 0000000019, 0, 0, 0, 0),
(0000000003, 0000000020, 0, 0, 0, 0),
(0000000003, 0000000021, 0, 0, 0, 0),
(0000000003, 0000000022, 0, 0, 0, 0),
(0000000003, 0000000023, 0, 0, 0, 0),
(0000000003, 0000000024, 0, 0, 0, 0),
(0000000003, 0000000025, 0, 0, 0, 0),
(0000000003, 0000000026, 0, 0, 0, 0),
(0000000003, 0000000027, 0, 0, 0, 0),
(0000000003, 0000000028, 1, 0, 0, 0),
(0000000003, 0000000030, 1, 0, 0, 0),
(0000000003, 0000000031, 1, 0, 0, 0),
(0000000003, 0000000032, 1, 0, 0, 0),
(0000000003, 0000000033, 1, 0, 0, 0),
(0000000003, 0000000035, 1, 0, 0, 0),
(0000000003, 0000000036, 0, 0, 0, 0),
(0000000003, 0000000056, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `acl_users`
--

DROP TABLE IF EXISTS `acl_users`;
CREATE TABLE IF NOT EXISTS `acl_users` (
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_users`,`id_pages`),
  KEY `id_users` (`id_users`),
  KEY `id_pages` (`id_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_users`
--

INSERT INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
(0000000001, 0000000001, 1, 0, 0, 0),
(0000000001, 0000000028, 1, 0, 0, 0),
(0000000001, 0000000033, 1, 0, 0, 0),
(0000000001, 0000000035, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
CREATE TABLE IF NOT EXISTS `actions` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`id`, `name`) VALUES
(0000000001, 'custom'),
(0000000002, 'component'),
(0000000003, 'sections');

-- --------------------------------------------------------

--
-- Table structure for table `activityType`
--

DROP TABLE IF EXISTS `activityType`;
CREATE TABLE IF NOT EXISTS `activityType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `activityType`
--

INSERT INTO `activityType` (`id`, `name`) VALUES
(0000000001, 'experiment'),
(0000000002, 'export');

-- --------------------------------------------------------

--
-- Table structure for table `callbackLogs`
--

DROP TABLE IF EXISTS `callbackLogs`;
CREATE TABLE IF NOT EXISTS `callbackLogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callback_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `remote_addr` varchar(200) DEFAULT NULL,
  `redirect_url` varchar(1000) DEFAULT NULL,
  `callback_params` longtext,
  `status` varchar(200) DEFAULT NULL,
  `callback_output` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_snd` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_rcv` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `content` longtext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_rcv_group` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_snd` (`id_snd`) USING BTREE,
  KEY `id_rcv` (`id_rcv`) USING BTREE,
  KEY `fk_chat_id_rcv_group` (`id_rcv_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `chatRecipiants`
--

DROP TABLE IF EXISTS `chatRecipiants`;
CREATE TABLE IF NOT EXISTS `chatRecipiants` (
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_chat` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_room_users` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `is_new` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_users`,`id_chat`),
  KEY `id_users` (`id_users`),
  KEY `id_chat` (`id_chat`),
  KEY `id_room_users` (`id_room_users`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chatRoom`
--

DROP TABLE IF EXISTS `chatRoom`;
CREATE TABLE IF NOT EXISTS `chatRoom` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `chatRoom`
--

INSERT INTO `chatRoom` (`id`, `name`, `description`, `title`) VALUES
(0000000001, 'root', 'The main room where every user is part of', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chatRoom_users`
--

DROP TABLE IF EXISTS `chatRoom_users`;
CREATE TABLE IF NOT EXISTS `chatRoom_users` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_chatRoom` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_chatRoom_2` (`id_chatRoom`,`id_users`),
  KEY `id_chatRoom` (`id_chatRoom`),
  KEY `id_users` (`id_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cmsPreferences`
--

DROP TABLE IF EXISTS `cmsPreferences`;
CREATE TABLE IF NOT EXISTS `cmsPreferences` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `callback_api_key` varchar(500) DEFAULT NULL,
  `default_language_id` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cmsPreferences_language` (`default_language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cmsPreferences`
--

INSERT INTO `cmsPreferences` (`id`, `callback_api_key`, `default_language_id`) VALUES
(0000000001, NULL, 0000000002);

-- --------------------------------------------------------

--
-- Table structure for table `codes_groups`
--

DROP TABLE IF EXISTS `codes_groups`;
CREATE TABLE IF NOT EXISTS `codes_groups` (
  `code` varchar(16) NOT NULL,
  `id_groups` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`code`,`id_groups`),
  KEY `fk_id_groups` (`id_groups`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;
CREATE TABLE IF NOT EXISTS `fields` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000002',
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_type` (`id_type`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES
(0000000001, 'label_user', 0000000001, 1),
(0000000002, 'label_pw', 0000000001, 1),
(0000000003, 'label_login', 0000000001, 1),
(0000000004, 'label_pw_reset', 0000000001, 1),
(0000000005, 'alert_fail', 0000000001, 1),
(0000000006, 'children', 0000000006, 0),
(0000000007, 'login_title', 0000000001, 1),
(0000000008, 'label', 0000000001, 1),
(0000000009, 'label_pw_confirm', 0000000001, 1),
(0000000010, 'label_change', 0000000001, 1),
(0000000011, 'pw_change_title', 0000000001, 1),
(0000000012, 'delete_title', 0000000001, 1),
(0000000013, 'label_delete', 0000000001, 1),
(0000000014, 'delete_content', 0000000002, 1),
(0000000015, 'label_delete_confirm', 0000000001, 1),
(0000000016, 'delete_confirm_content', 0000000002, 1),
(0000000017, 'alert_pw_fail', 0000000001, 1),
(0000000018, 'alert_pw_success', 0000000001, 1),
(0000000019, 'alert_del_fail', 0000000001, 1),
(0000000020, 'alert_del_success', 0000000001, 1),
(0000000021, 'level', 0000000005, 0),
(0000000022, 'title', 0000000007, 1),
(0000000023, 'css', 0000000001, 0),
(0000000024, 'text', 0000000002, 1),
(0000000025, 'text_md', 0000000004, 1),
(0000000026, 'text_md_inline', 0000000007, 1),
(0000000027, 'url', 0000000001, 0),
(0000000028, 'type', 0000000009, 0),
(0000000029, 'is_fluid', 0000000003, 0),
(0000000030, 'alt', 0000000001, 1),
(0000000031, 'title_prefix', 0000000001, 1),
(0000000032, 'experimenter', 0000000001, 1),
(0000000033, 'subjects', 0000000001, 1),
(0000000034, 'subtitle', 0000000001, 1),
(0000000035, 'alert_success', 0000000001, 1),
(0000000036, 'label_name', 0000000001, 1),
(0000000037, 'name_placeholder', 0000000001, 1),
(0000000038, 'name_description', 0000000007, 1),
(0000000039, 'label_gender', 0000000001, 1),
(0000000040, 'gender_male', 0000000001, 1),
(0000000041, 'gender_female', 0000000001, 1),
(0000000042, 'label_activate', 0000000001, 1),
(0000000043, 'pw_placeholder', 0000000001, 1),
(0000000044, 'success', 0000000001, 1),
(0000000045, 'is_dismissable', 0000000003, 0),
(0000000046, 'is_expanded', 0000000003, 0),
(0000000047, 'is_collapsible', 0000000003, 0),
(0000000048, 'url_edit', 0000000001, 0),
(0000000049, 'caption_title', 0000000001, 1),
(0000000050, 'caption', 0000000007, 1),
(0000000051, 'label_cancel', 0000000001, 1),
(0000000052, 'url_cancel', 0000000001, 0),
(0000000053, 'source', 0000000001, 1),
(0000000054, 'type_input', 0000000010, 0),
(0000000055, 'placeholder', 0000000001, 1),
(0000000056, 'is_required', 0000000003, 0),
(0000000057, 'name', 0000000001, 0),
(0000000058, 'value', 0000000002, 0),
(0000000059, 'is_paragraph', 0000000003, 0),
(0000000060, 'count', 0000000005, 0),
(0000000061, 'count_max', 0000000005, 0),
(0000000062, 'label_right', 0000000001, 1),
(0000000063, 'label_wrong', 0000000001, 1),
(0000000064, 'right_content', 0000000001, 1),
(0000000065, 'wrong_content', 0000000001, 1),
(0000000066, 'items', 0000000008, 1),
(0000000067, 'is_multiple', 0000000003, 0),
(0000000068, 'labels', 0000000008, 1),
(0000000069, 'min', 0000000005, 0),
(0000000070, 'max', 0000000005, 0),
(0000000071, 'sources', 0000000008, 1),
(0000000072, 'label_root', 0000000001, 1),
(0000000073, 'label_back', 0000000001, 1),
(0000000074, 'label_next', 0000000001, 1),
(0000000075, 'has_navigation_buttons', 0000000003, 0),
(0000000077, 'search_text', 0000000001, 1),
(0000000078, 'is_sortable', 0000000003, 0),
(0000000079, 'is_editable', 0000000003, 0),
(0000000080, 'url_delete', 0000000001, 0),
(0000000081, 'label_add', 0000000001, 1),
(0000000082, 'url_add', 0000000001, 0),
(0000000083, 'id_prefix', 0000000001, 0),
(0000000084, 'id_active', 0000000005, 0),
(0000000085, 'is_inline', 0000000003, 0),
(0000000086, 'open_in_new_tab', 0000000003, 0),
(0000000087, 'is_log', 0000000003, 0),
(0000000088, 'label_date_time', 0000000001, 1),
(0000000089, 'css_nav', 0000000001, 0),
(0000000090, 'label_submit', 0000000001, 1),
(0000000091, 'condition', 0000000008, 0),
(0000000092, 'email_activate', 0000000011, 1),
(0000000094, 'email_reminder', 0000000011, 1),
(0000000095, 'label_lobby', 0000000001, 1),
(0000000096, 'label_new', 0000000001, 1),
(0000000097, 'debug', 0000000003, 0),
(0000000099, 'has_controls', 0000000003, 0),
(0000000100, 'has_indicators', 0000000003, 0),
(0000000101, 'is_striped', 0000000003, 0),
(0000000102, 'has_label', 0000000003, 0),
(0000000103, 'has_crossfade', 0000000003, 0),
(0000000104, 'has_navigation_menu', 0000000003, 0),
(0000000105, 'json', 0000000008, 1),
(0000000106, 'description', 0000000002, 1),
(0000000107, 'code', 0000000012, 1),
(0000000108, 'admins', 0000000008, 0),
(0000000109, 'email_admins', 0000000011, 1),
(0000000110, 'email_user', 0000000011, 1),
(0000000111, 'subject_user', 0000000001, 1),
(0000000112, 'attachments_user', 0000000008, 1),
(0000000113, 'do_store', 0000000003, 0),
(0000000114, 'is_html', 0000000003, 0),
(0000000115, 'maintenance', 0000000004, 1),
(0000000116, 'maintenance_date', 0000000013, 0),
(0000000117, 'maintenance_time', 0000000014, 0),
(0000000118, 'name_value_field', 0000000001, 0),
(0000000119, 'callback_class', 0000000001, 0),
(0000000120, 'callback_method', 0000000001, 0),
(0000000121, 'data-source', 0000000015, 0),
(0000000122, 'traces', 0000000008, 0),
(0000000123, 'layout', 0000000008, 1),
(0000000124, 'config', 0000000008, 0),
(0000000125, 'form_field_names', 0000000008, 1),
(0000000126, 'value_types', 0000000008, 1),
(0000000127, 'link_color', 0000000001, 0),
(0000000128, 'link_alpha', 0000000001, 0),
(0000000129, 'has_type_labels', 0000000003, 0),
(0000000130, 'has_field_labels', 0000000003, 0),
(0000000131, 'is_grouped', 0000000003, 0),
(0000000132, 'single_user', 0000000003, 0),
(0000000133, 'raw', 0000000001, 0),
(0000000134, 'values', 0000000008, 0),
(0000000135, 'is_vertical', 0000000003, 0),
(0000000136, 'hole', 0000000005, 0),
(0000000137, 'hoverinfo', 0000000001, 0),
(0000000138, 'textinfo', 0000000001, 0),
(0000000139, 'anchor', 0000000016, 0),
(0000000140, 'open_registration', 0000000003, 0),
(0000000141, 'live_search', 0000000003, 0),
(0000000142, 'disabled', 0000000003, 0),
(0000000143, 'group', 0000000017, 0),
(0000000144, 'qualtricsSurvey', 0000000018, 0),
(0000000145, 'data_config', 0000000008, 0),
(0000000146, 'export_pdf', 0000000003, 0),
(0000000147, 'prefix', 0000000001, 1),
(0000000148, 'suffix', 0000000001, 1),
(0000000149, 'submit_and_send_email', 0000000003, 1),
(0000000150, 'submit_and_send_label', 0000000001, 1),
(0000000151, 'email_subject', 0000000004, 1),
(0000000152, 'email_body', 0000000004, 1);

-- --------------------------------------------------------

--
-- Table structure for table `fieldType`
--

DROP TABLE IF EXISTS `fieldType`;
CREATE TABLE IF NOT EXISTS `fieldType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `fieldType`
--

INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES
(0000000001, 'text', 10),
(0000000002, 'textarea', 30),
(0000000003, 'checkbox', 60),
(0000000004, 'markdown', 40),
(0000000005, 'number', 50),
(0000000006, 'style-list', 70),
(0000000007, 'markdown-inline', 20),
(0000000008, 'json', 45),
(0000000009, 'style-bootstrap', 5),
(0000000010, 'type-input', 4),
(0000000011, 'email', 90),
(0000000012, 'code', 42),
(0000000013, 'date', 25),
(0000000014, 'time', 24),
(0000000015, 'data-source', 15),
(0000000016, 'anchor-section', 14),
(0000000017, 'select-group', 7),
(0000000018, 'select-qualtrics-survey', 7);

-- --------------------------------------------------------

--
-- Table structure for table `genders`
--

DROP TABLE IF EXISTS `genders`;
CREATE TABLE IF NOT EXISTS `genders` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `genders`
--

INSERT INTO `genders` (`id`, `name`) VALUES
(0000000001, 'male'),
(0000000002, 'female');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(0000000001, 'admin', 'full access'),
(0000000002, 'therapist', 'access to home, legal, profile, experiment, manage experiment'),
(0000000003, 'subject', 'access to home, legal, profile, experiment');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL COMMENT '"e.g en-GB, de-CH"',
  `language` varchar(100) NOT NULL,
  `csv_separator` varchar(1) NOT NULL DEFAULT ',',
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`),
  UNIQUE KEY `language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `locale`, `language`, `csv_separator`) VALUES
(0000000001, 'all', 'Independent', ','),
(0000000002, 'de-CH', 'Deutsch (Schweiz)', ','),
(0000000003, 'en-GB', 'English (GB)', ',');

-- --------------------------------------------------------

--
-- Table structure for table `lookups`
--

DROP TABLE IF EXISTS `lookups`;
CREATE TABLE IF NOT EXISTS `lookups` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `type_code` varchar(100) NOT NULL,
  `lookup_code` varchar(100) DEFAULT NULL,
  `lookup_value` varchar(200) DEFAULT NULL,
  `lookup_description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lookup_code` (`lookup_code`),
  UNIQUE KEY `lookup_value` (`lookup_value`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `lookups`
--

INSERT INTO `lookups` (`id`, `type_code`, `lookup_code`, `lookup_value`, `lookup_description`) VALUES
(0000000001, 'notificationTypes', 'email', 'Email', 'The notification will be sent by email'),
(0000000002, 'qualtricScheduleTypes', 'immediately', 'Immediately', 'Shcedule and send the mail immediately'),
(0000000003, 'qualtricScheduleTypes', 'on_fixed_datetime', 'On specific fixed datetime', 'Shcedule and send the mail on specific fixed datetime'),
(0000000004, 'qualtricScheduleTypes', 'after_period', 'After time period', 'Schedule the mail after specific time period'),
(0000000005, 'qualtricScheduleTypes', 'after_period_on_day_at_time', 'After time period on a weekday at given time', 'Schedule the mail after specific time on specific day from the week at specific time'),
(0000000006, 'qualtricsSurveyTypes', 'baseline', 'Baseline', 'Baselin surveys are the leadign surveys. They record the user in the contact list'),
(0000000007, 'qualtricsSurveyTypes', 'follow_up', 'Follow-up', 'Folloup surveys get a user from the contact list and use it.'),
(0000000008, 'qualtricsProjectActionTriggerTypes', 'started', 'Started', 'When the user start the survey'),
(0000000009, 'qualtricsProjectActionTriggerTypes', 'finished', 'Finished', 'When the user finish the survey'),
(0000000010, 'qualtricsProjectActionAdditionalFunction', 'workwell_evaluate_personal_strenghts', '[Workwell] Evaluate personal strengths', 'Function that will evaluate the personal strengths and it will send an email for project workwell'),
(0000000011, 'qualtricsActionScheduleTypes', 'nothing', 'Nothing', 'Nothing to be scheduled'),
(0000000012, 'qualtricsActionScheduleTypes', 'notification', 'Notification', 'Shcedule a notification eamil'),
(0000000013, 'qualtricsActionScheduleTypes', 'reminder', 'Reminder', 'Schedule a reminder email. If the survey was done the remider is canceled'),
(0000000014, 'timePeriod', 'seconds', 'Second(s)', 'Second(s)'),
(0000000015, 'timePeriod', 'minutes', 'Minute(s)', 'Minute(s)'),
(0000000016, 'timePeriod', 'hours', 'Hour(s)', 'Hour(s)'),
(0000000017, 'timePeriod', 'days', 'Day(s)', 'Day(s)'),
(0000000018, 'timePeriod', 'weeks', 'Week(s)', 'Week(s)'),
(0000000019, 'timePeriod', 'months', 'Month(s)', 'Month(s)'),
(0000000020, 'weekdays', 'monday', 'Monday', 'Monday'),
(0000000021, 'weekdays', 'tuesday', 'Tuesday', 'Tuesday'),
(0000000022, 'weekdays', 'wednesday', 'Wednesday', 'Wednesday'),
(0000000023, 'weekdays', 'thursday', 'Thursday', 'Thursday'),
(0000000024, 'weekdays', 'friday', 'Friday', 'Friday'),
(0000000025, 'weekdays', 'saturday', 'Saturday', 'Saturday'),
(0000000026, 'weekdays', 'sunday', 'Sunday', 'Sunday'),
(0000000027, 'mailQueueStatus', 'queued', 'Queued', 'Status for initialization. When the mail is queued it goes in this status'),
(0000000028, 'mailQueueStatus', 'deleted', 'Deleted', 'When the queue is deleted'),
(0000000029, 'mailQueueStatus', 'sent', 'Sent', 'When the mail is sent'),
(0000000030, 'mailQueueStatus', 'failed', 'Failed', 'When something happened and the mail sending failed'),
(0000000031, 'mailQueueSearchDateTypes', 'date_create', 'Entry date', 'The date that the queue record was created'),
(0000000032, 'mailQueueSearchDateTypes', 'date_to_be_sent', 'Date to be send', 'The date when the queue record should be sent'),
(0000000033, 'mailQueueSearchDateTypes', 'date_sent', 'Sent date', 'The date when the queue record was sent'),
(0000000034, 'transactionTypes', 'insert', 'Add new entry', 'Add new entry to a table'),
(0000000035, 'transactionTypes', 'select', 'View entry', 'View entry from a table'),
(0000000036, 'transactionTypes', 'update', 'Edit entry', 'Edit entry from a table'),
(0000000037, 'transactionTypes', 'delete', 'Delete entry', 'Delete entry from a table'),
(0000000038, 'transactionTypes', 'send_mail_ok', 'Send mail successfully', 'Send mail successfully'),
(0000000039, 'transactionTypes', 'send_mail_fail', 'Send mail failed', 'Send mail failed'),
(0000000040, 'transactionTypes', 'check_mailQueue', 'Check mail queue', 'Check mail queue and send mails if needed'),
(0000000041, 'transactionBy', 'by_mail_cron', 'By mail cronjob', 'The action was done by a mail cronjob'),
(0000000042, 'transactionBy', 'by_user', 'By user', 'The action was done by an user'),
(0000000043, 'transactionBy', 'by_qualtrics_callback', 'By qualtrics callback', 'The action was done by a qualtrics callback'),
(0000000044, 'qualtricsProjectActionAdditionalFunction', 'workwell_cg_ap_4', '[Workwell] CG Action plan Week 4 (Reminder or notification is required)', '[Workwell] CG Action plan Week 4 (Reminder or notification is required)'),
(0000000045, 'qualtricsProjectActionAdditionalFunction', 'workwell_cg_ap_5', '[Workwell] CG Action plan Week 5 (Reminder or notification is required)', '[Workwell] CG Action plan Week 5 (Reminder or notification is required)'),
(0000000046, 'qualtricsProjectActionAdditionalFunction', 'workwell_eg_ap_4', '[Workwell] EG Action plan Week 4 (Reminder or notification is required)', '[Workwell] EG Action plan Week 4 (Reminder or notification is required)'),
(0000000047, 'qualtricsProjectActionAdditionalFunction', 'workwell_eg_ap_5', '[Workwell] EG Action plan Week 5 (Reminder or notification is required)', '[Workwell] EG Action plan Week 5 (Reminder or notification is required)'),
(0000000048, 'qualtricsSurveyTypes', 'anonymous', 'Anonymous', 'Anonymous survey. No code or user is used.'),
(0000000049, 'qualtricsProjectActionAdditionalFunction', 'bmz_evaluate_motive', '[BMZ] Evaluate motive', 'Function that will evaluate the motive and genrate PDF file as a feedback');

-- --------------------------------------------------------

--
-- Table structure for table `mailAttachments`
--

DROP TABLE IF EXISTS `mailAttachments`;
CREATE TABLE IF NOT EXISTS `mailAttachments` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_mailQueue` int(10) UNSIGNED ZEROFILL NOT NULL,
  `attachment_name` varchar(100) NOT NULL,
  `attachment_path` varchar(1000) NOT NULL,
  `attachment_url` varchar(1000) NOT NULL,
  `template_path` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mailAttachments_fk_id_mailQueue` (`id_mailQueue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mailQueue`
--

DROP TABLE IF EXISTS `mailQueue`;
CREATE TABLE IF NOT EXISTS `mailQueue` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_mailQueueStatus` int(10) UNSIGNED ZEROFILL NOT NULL,
  `date_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_to_be_sent` datetime DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `from_email` varchar(100) NOT NULL,
  `from_name` varchar(100) NOT NULL,
  `reply_to` varchar(100) NOT NULL,
  `recipient_emails` text NOT NULL,
  `cc_emails` varchar(1000) DEFAULT NULL,
  `bcc_emails` varchar(1000) DEFAULT NULL,
  `subject` varchar(1000) NOT NULL,
  `body` longtext NOT NULL,
  `is_html` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `mailQueue_fk_id_mailQueueStatus` (`id_mailQueueStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `module_name` varchar(500) DEFAULT NULL,
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_name`, `enabled`) VALUES
(0000000001, 'moduleQualtrics', 1),
(0000000002, 'moduleMail', 1),
(0000000003, 'moduleChat', 1);

-- --------------------------------------------------------

--
-- Table structure for table `modules_pages`
--

DROP TABLE IF EXISTS `modules_pages`;
CREATE TABLE IF NOT EXISTS `modules_pages` (
  `id_modules` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_modules`,`id_pages`),
  KEY `id_modules` (`id_modules`),
  KEY `id_pages` (`id_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `modules_pages`
--

INSERT INTO `modules_pages` (`id_modules`, `id_pages`) VALUES
(0000000001, 0000000049),
(0000000001, 0000000051),
(0000000001, 0000000052),
(0000000002, 0000000050),
(0000000002, 0000000055);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(100) DEFAULT NULL COMMENT 'pipe seperated list of HTTP Methods (GET|POST)',
  `id_actions` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `id_navigation_section` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `parent` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `is_headless` tinyint(1) NOT NULL DEFAULT '0',
  `nav_position` int(11) DEFAULT NULL,
  `footer_position` int(11) DEFAULT NULL,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `parent` (`parent`),
  KEY `id_actions` (`id_actions`),
  KEY `id_navigation_section` (`id_navigation_section`),
  KEY `id_type` (`id_type`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES
(0000000001, 'login', '/login', 'GET|POST', 0000000003, NULL, NULL, 1, NULL, NULL, 0000000002),
(0000000002, 'home', '/', 'GET|POST', 0000000003, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000003, 'profile-link', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000004, 'profile', '/profile', 'GET|POST', 0000000003, NULL, 0000000003, 0, 10, NULL, 0000000002),
(0000000005, 'logout', '/login', 'GET', NULL, NULL, 0000000003, 0, 20, NULL, 0000000002),
(0000000006, 'missing', NULL, NULL, 0000000003, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000007, 'no_access', NULL, NULL, 0000000003, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000008, 'no_access_guest', NULL, NULL, 0000000003, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000009, 'admin-link', NULL, NULL, NULL, NULL, NULL, 0, 1000, NULL, 0000000001),
(0000000010, 'cmsSelect', '/admin/cms/[i:pid]?/[i:sid]?/[i:ssid]?', 'GET|POST', 0000000002, NULL, 0000000009, 0, 10, NULL, 0000000001),
(0000000011, 'cmsInsert', '/admin/cms_insert/[i:pid]?', 'GET|POST|PUT', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000012, 'cmsUpdate', '/admin/cms_update/[i:pid]?/[i:sid]?/[i:ssid]?/[update|insert|delete:mode]/[v:type]/[i:did]?', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000013, 'cmsDelete', '/admin/cms_delete/[i:pid]/[i:sid]?/[i:ssid]?', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000014, 'userSelect', '/admin/user/[i:uid]?', 'GET', 0000000002, NULL, 0000000009, 0, 20, NULL, 0000000001),
(0000000015, 'userInsert', '/admin/user_insert', 'GET|POST|PUT', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000016, 'userUpdate', '/admin/user_update/[i:uid]/[v:mode]/[i:did]?', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000017, 'userDelete', '/admin/user_delete/[i:uid]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000018, 'groupSelect', '/admin/group/[i:gid]?', 'GET', 0000000002, NULL, 0000000009, 0, 30, NULL, 0000000001),
(0000000019, 'groupInsert', '/admin/group_insert', 'GET|POST|PUT', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000020, 'groupUpdate', '/admin/group_update/[i:gid]', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000021, 'groupDelete', '/admin/group_delete/[i:gid]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000022, 'export', '/admin/export', 'GET|POST', 0000000002, NULL, 0000000009, 0, 40, NULL, 0000000001),
(0000000023, 'exportData', '/admin/export/[user_input|user_activity|validation_codes|user_input_form:selector]/[all|used|open:option]?/[i:id]?', 'GET', 0000000001, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000024, 'assetSelect', '/admin/asset', 'GET', 0000000002, NULL, 0000000009, 0, 15, NULL, 0000000001),
(0000000025, 'assetInsert', '/admin/asset_insert/[css|asset|static:mode]', 'GET|POST|PUT', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000026, 'assetUpdate', '/admin/asset_update/[v:file]', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000027, 'assetDelete', '/admin/asset_delete/[css|asset|static:mode]/[*:file]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000028, 'request', '/request/[v:class]/[v:method]?', 'GET|POST', 0000000001, NULL, NULL, 0, NULL, NULL, 0000000001),
(0000000030, 'agb', '/agb', 'GET', 0000000003, NULL, NULL, 0, NULL, 300, 0000000002),
(0000000031, 'impressum', '/impressum', 'GET', 0000000003, NULL, NULL, 0, NULL, 100, 0000000002),
(0000000032, 'disclaimer', '/disclaimer', 'GET', 0000000003, NULL, NULL, 0, NULL, 200, 0000000002),
(0000000033, 'validate', '/validate/[i:uid]/[a:token]', 'GET|POST', 0000000003, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000035, 'reset_password', '/reset', 'GET|POST', 0000000003, NULL, NULL, 0, NULL, NULL, 0000000002),
(0000000036, 'userGenCode', '/admin/user_gen_code', 'GET|POST|PUT', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000037, 'email', '/admin/email/[i:id]?', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, 11, NULL, 0000000001),
(0000000042, 'exportDelete', '/admin/exportDelete/[user_activity|user_input:selector]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000043, 'groupUpdateCustom', '/admin/group_update_custom/[i:gid]', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000044, 'callback', '/callback/[v:class]/[v:method]?', 'GET|POST', 0000000001, NULL, NULL, 0, NULL, NULL, 0000000001),
(0000000045, 'data', '/admin/data', 'GET|POST', 0000000002, NULL, 0000000009, 0, 39, NULL, 0000000001),
(0000000046, 'cmsPreferences', '/admin/cms_preferences', 'GET|POST', 0000000002, NULL, 0000000009, 0, 1000, NULL, 0000000001),
(0000000047, 'cmsPreferencesUpdate', '/admin/cms_preferences_update', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000048, 'language', '/admin/language/[i:lid]?', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000049, 'moduleQualtrics', '/admin/qualtrics', 'GET|POST', 0000000002, NULL, 0000000009, 0, 90, NULL, 0000000001),
(0000000050, 'moduleMail', '/admin/mailQueue/[i:mqid]?', 'GET|POST', 0000000002, NULL, 0000000009, 0, 80, NULL, 0000000001),
(0000000051, 'moduleQualtricsProject', '/admin/qualtrics/project/[select|update|insert|delete:mode]?/[i:pid]?', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000052, 'moduleQualtricsSurvey', '/admin/qualtrics/survey/[select|update|insert|delete:mode]?/[i:sid]?', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000053, 'moduleQualtricsProjectAction', '/admin/qualtrics/action/[i:pid]/[select|update|insert|delete:mode]?/[i:sid]?', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000054, 'moduleQualtricsSync', '/admin/qualtrics/sync/[i:pid]', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000055, 'moduleMailComposeEmail', '/admin/mailQueue/composeEmail', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001),
(0000000056, 'chatSubject', '/chat/subject/[i:gid]?/[i:uid]?', 'GET|POST', 0000000003, NULL, NULL, 0, NULL, NULL, 0000000003),
(0000000057, 'chatTherapist', '/chat/therapist/[i:gid]?/[i:uid]?', 'GET|POST', 0000000003, NULL, NULL, 0, NULL, NULL, 0000000003),
(0000000058, 'cmsExport', '/admin/cms_export/[page|section:type]/[i:id]', 'GET|POST', 0000000002, NULL, 0000000009, 0, NULL, NULL, 0000000001);

-- --------------------------------------------------------

--
-- Table structure for table `pages_fields`
--

DROP TABLE IF EXISTS `pages_fields`;
CREATE TABLE IF NOT EXISTS `pages_fields` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_pages`,`id_fields`),
  KEY `id_pages` (`id_pages`),
  KEY `id_fields` (`id_fields`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages_fields`
--

INSERT INTO `pages_fields` (`id_pages`, `id_fields`, `default_value`, `help`) VALUES
(0000000002, 0000000106, NULL, 'A short description of the research project. This field will be used as `meta:description` in the HTML header. Some services use this tag to provide the user with information on the webpage (e.g. automatic link-replacement in messaging tools on smartphones use this description.)'),
(0000000002, 0000000115, NULL, 'This field defines the content of the alert message that is shown when a date is set in the field `maintenance_date`. Use markdown with the special keywords `@date` and `@time` which will be replaced by a human-readable form of the fields `maintenance_date` and `maintenance_time`.'),
(0000000002, 0000000116, NULL, 'If set (together with the field `maintenance_time`), an alert message is shown at the top of the page displaying to content as defined in the field `maintenance` (where the key `@data` is replaced by this field).'),
(0000000002, 0000000117, NULL, 'If set (together with the field `maintenance_date`), an alert message is shown at the top of the page displaying to content as defined in the field `maintenance` (where the key `@time` is replaced by this field).');

-- --------------------------------------------------------

--
-- Table structure for table `pages_fields_translation`
--

DROP TABLE IF EXISTS `pages_fields_translation`;
CREATE TABLE IF NOT EXISTS `pages_fields_translation` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL,
  PRIMARY KEY (`id_pages`,`id_fields`,`id_languages`),
  KEY `id_pages` (`id_pages`),
  KEY `id_fields` (`id_fields`),
  KEY `id_languages` (`id_languages`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages_fields_translation`
--

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
(0000000001, 0000000008, 0000000002, 'Login'),
(0000000001, 0000000008, 0000000003, 'Login'),
(0000000002, 0000000008, 0000000002, 'Projekt Name'),
(0000000002, 0000000008, 0000000003, 'Project Name'),
(0000000002, 0000000115, 0000000002, 'Um eine Server-Wartung durchzuführen wird die Seite ab dem @date um @time für einen kurzen Moment nicht erreichbar sein. Wir bitten um Entschuldigung.'),
(0000000002, 0000000115, 0000000003, 'There will be a short service disruption on the @date at @time due to server maintenance. Please accept our apologies for the caused inconveniences.'),
(0000000003, 0000000008, 0000000002, 'Profil'),
(0000000003, 0000000008, 0000000003, 'Profile'),
(0000000004, 0000000008, 0000000002, 'Einstellungen'),
(0000000004, 0000000008, 0000000003, 'Settings'),
(0000000005, 0000000008, 0000000002, 'Logout'),
(0000000005, 0000000008, 0000000003, 'Logout'),
(0000000006, 0000000008, 0000000002, 'Seite nicht gefunden'),
(0000000006, 0000000008, 0000000003, 'Missing'),
(0000000007, 0000000008, 0000000002, 'Kein Zugriff'),
(0000000007, 0000000008, 0000000003, 'No Access'),
(0000000008, 0000000008, 0000000002, 'Kein Zugriff'),
(0000000008, 0000000008, 0000000003, 'No Access'),
(0000000009, 0000000008, 0000000001, 'Admin'),
(0000000010, 0000000008, 0000000001, 'CMS'),
(0000000011, 0000000008, 0000000001, 'Create Page'),
(0000000012, 0000000008, 0000000001, 'Update Content'),
(0000000013, 0000000008, 0000000001, 'Delete Page'),
(0000000014, 0000000008, 0000000001, 'Users'),
(0000000015, 0000000008, 0000000001, 'Create User'),
(0000000016, 0000000008, 0000000001, 'Modify User'),
(0000000017, 0000000008, 0000000001, 'Delete User'),
(0000000018, 0000000008, 0000000001, 'Groups'),
(0000000019, 0000000008, 0000000001, 'Create Group'),
(0000000020, 0000000008, 0000000001, 'Modify Group'),
(0000000021, 0000000008, 0000000001, 'Delete Group'),
(0000000022, 0000000008, 0000000001, 'Export'),
(0000000023, 0000000008, 0000000001, 'Export'),
(0000000024, 0000000008, 0000000001, 'Assets'),
(0000000025, 0000000008, 0000000001, 'Upload Asset'),
(0000000026, 0000000008, 0000000001, 'Rename Asset'),
(0000000027, 0000000008, 0000000001, 'Delete Asset'),
(0000000030, 0000000008, 0000000002, 'AGB'),
(0000000030, 0000000008, 0000000003, 'GTC'),
(0000000031, 0000000008, 0000000002, 'Impressum'),
(0000000031, 0000000008, 0000000003, 'Impressum'),
(0000000032, 0000000008, 0000000002, 'Disclaimer'),
(0000000032, 0000000008, 0000000003, 'Disclaimer'),
(0000000033, 0000000008, 0000000002, 'Benutzer Validierung'),
(0000000033, 0000000008, 0000000003, 'User Validation'),
(0000000035, 0000000008, 0000000002, 'Passwort zurücksetzen'),
(0000000035, 0000000008, 0000000003, 'Reset Password'),
(0000000036, 0000000008, 0000000001, 'Generate Validation Codes'),
(0000000037, 0000000008, 0000000001, 'Email CMS'),
(0000000037, 0000000092, 0000000002, 'Guten Tag\r\n\r\nUm Ihre Email Adresse zu verifizieren und Ihren @project Account zu aktivieren klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team'),
(0000000037, 0000000092, 0000000003, 'Hello\r\n\r\nTo verify you email address and to activate your @project account please click the link below.\r\n\r\n@link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team'),
(0000000037, 0000000094, 0000000002, 'Guten Tag\r\n\r\nSie waren für längere Zeit nicht mehr aktiv auf der @project Plattform.\r\nEs würde uns freuen wenn Sie wieder vorbeischauen würden.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),
(0000000037, 0000000094, 0000000003, 'Hello\r\n\r\nYou did not visit the @project platform for some time now.\r\nWe would be pleased if you would visit us again.\r\n\r\n@link\r\n\r\nSincerely, your @project team'),
(0000000042, 0000000008, 0000000002, 'Userdaten Löschen'),
(0000000042, 0000000008, 0000000003, 'Remove User Data'),
(0000000043, 0000000008, 0000000001, 'Custom Group Update'),
(0000000045, 0000000008, 0000000001, 'Data'),
(0000000046, 0000000008, 0000000001, 'CMS Preferecnes'),
(0000000047, 0000000008, 0000000001, 'CMS Preferecnes Update'),
(0000000048, 0000000008, 0000000001, 'Create Language'),
(0000000049, 0000000008, 0000000001, 'Module Qualtrics'),
(0000000050, 0000000008, 0000000001, 'Module Mail'),
(0000000051, 0000000008, 0000000001, 'Qualtrics Projects'),
(0000000052, 0000000008, 0000000001, 'Qualtrics Survey'),
(0000000053, 0000000008, 0000000001, 'Qualtrics Project Action'),
(0000000054, 0000000008, 0000000001, 'Qualtrics Synchronization'),
(0000000055, 0000000008, 0000000001, 'Compose Mail'),
(0000000056, 0000000008, 0000000002, 'Kontakt'),
(0000000056, 0000000008, 0000000003, 'Contact'),
(0000000057, 0000000008, 0000000002, 'Kontakt'),
(0000000057, 0000000008, 0000000003, 'Contact'),
(0000000058, 0000000008, 0000000001, 'CMS Export');

-- --------------------------------------------------------

--
-- Table structure for table `pages_sections`
--

DROP TABLE IF EXISTS `pages_sections`;
CREATE TABLE IF NOT EXISTS `pages_sections` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pages`,`id_sections`),
  KEY `id_pages` (`id_pages`),
  KEY `id_sections` (`id_sections`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages_sections`
--

INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES
(0000000001, 0000000036, NULL),
(0000000002, 0000000019, 0),
(0000000004, 0000000002, NULL),
(0000000006, 0000000003, NULL),
(0000000007, 0000000009, 0),
(0000000008, 0000000012, 0),
(0000000030, 0000000016, 0),
(0000000031, 0000000020, 0),
(0000000032, 0000000018, 0),
(0000000033, 0000000026, NULL),
(0000000035, 0000000028, NULL),
(0000000056, 0000000060, 1),
(0000000057, 0000000062, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pageType`
--

DROP TABLE IF EXISTS `pageType`;
CREATE TABLE IF NOT EXISTS `pageType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pageType`
--

INSERT INTO `pageType` (`id`, `name`) VALUES
(0000000001, 'intern'),
(0000000002, 'core'),
(0000000003, 'experiment'),
(0000000004, 'open');

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsActions`
--

DROP TABLE IF EXISTS `qualtricsActions`;
CREATE TABLE IF NOT EXISTS `qualtricsActions` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_qualtricsProjects` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_qualtricsSurveys` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(200) NOT NULL,
  `id_qualtricsProjectActionTriggerTypes` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_qualtricsActionScheduleTypes` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_qualtricsSurveys_reminder` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `schedule_info` text,
  PRIMARY KEY (`id`),
  KEY `qualtricsActions_fk_id_qualtricsProjects` (`id_qualtricsProjects`),
  KEY `qualtricsActions_fk_id_qualtricsSurveys` (`id_qualtricsSurveys`),
  KEY `qualtricsActions_fk_id_qualtricsSurveys_reminder` (`id_qualtricsSurveys_reminder`),
  KEY `qualtricsActions_fk_id_qualtricsActionScheduleTypes` (`id_qualtricsActionScheduleTypes`),
  KEY `qualtricsActions_fk_id_lookups_qualtricsProjectActionTriggerType` (`id_qualtricsProjectActionTriggerTypes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsActions_functions`
--

DROP TABLE IF EXISTS `qualtricsActions_functions`;
CREATE TABLE IF NOT EXISTS `qualtricsActions_functions` (
  `id_qualtricsActions` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_lookups` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_qualtricsActions`,`id_lookups`),
  KEY `id_qualtricsActions` (`id_qualtricsActions`),
  KEY `id_lookups` (`id_lookups`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsActions_groups`
--

DROP TABLE IF EXISTS `qualtricsActions_groups`;
CREATE TABLE IF NOT EXISTS `qualtricsActions_groups` (
  `id_qualtricsActions` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_groups` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_qualtricsActions`,`id_groups`),
  KEY `id_qualtricsActions` (`id_qualtricsActions`),
  KEY `id_groups` (`id_groups`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsProjects`
--

DROP TABLE IF EXISTS `qualtricsProjects`;
CREATE TABLE IF NOT EXISTS `qualtricsProjects` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `qualtrics_api` varchar(100) DEFAULT NULL,
  `api_library_id` varchar(100) DEFAULT NULL,
  `api_mailing_group_id` varchar(100) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsReminders`
--

DROP TABLE IF EXISTS `qualtricsReminders`;
CREATE TABLE IF NOT EXISTS `qualtricsReminders` (
  `id_qualtricsSurveys` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_mailQueue` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_qualtricsSurveys`,`id_users`,`id_mailQueue`),
  KEY `qualtricsReminders_fk_id_users` (`id_users`),
  KEY `qualtricsReminders_fk_id_mailQueue` (`id_mailQueue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsSurveys`
--

DROP TABLE IF EXISTS `qualtricsSurveys`;
CREATE TABLE IF NOT EXISTS `qualtricsSurveys` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `qualtrics_survey_id` varchar(100) DEFAULT NULL,
  `id_qualtricsSurveyTypes` int(10) UNSIGNED ZEROFILL NOT NULL,
  `participant_variable` varchar(100) DEFAULT NULL,
  `group_variable` int(11) DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `config` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `qualtrics_survey_id` (`qualtrics_survey_id`),
  KEY `qualtricsSurveys_fk_id_qualtricsSurveyTypes` (`id_qualtricsSurveyTypes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `qualtricsSurveysResponses`
--

DROP TABLE IF EXISTS `qualtricsSurveysResponses`;
CREATE TABLE IF NOT EXISTS `qualtricsSurveysResponses` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_surveys` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_qualtricsProjectActionTriggerTypes` int(10) UNSIGNED ZEROFILL NOT NULL,
  `survey_response_id` varchar(100) DEFAULT NULL,
  `started_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_response_id` (`survey_response_id`),
  KEY `qSurveysResponses_fk_id_users` (`id_users`),
  KEY `qSurveysResponses_fk_id_surveys` (`id_surveys`),
  KEY `qSurveysResponses_fk_id_qualtricsProjectActionTriggerTypes` (`id_qualtricsProjectActionTriggerTypes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `owner` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id_styles` (`id_styles`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `id_styles`, `name`, `owner`) VALUES
(0000000001, 0000000001, 'login-login', NULL),
(0000000002, 0000000002, 'profile-profile', NULL),
(0000000003, 0000000003, 'missing-container', NULL),
(0000000004, 0000000004, 'missing-jumbotron', NULL),
(0000000005, 0000000005, 'missing-heading', NULL),
(0000000006, 0000000006, 'missing-markdown', NULL),
(0000000007, 0000000008, 'goBack-button', NULL),
(0000000008, 0000000008, 'goHome-button', NULL),
(0000000009, 0000000003, 'no_access-container', NULL),
(0000000010, 0000000004, 'no_access-jumbotron', NULL),
(0000000011, 0000000005, 'no_access-heading', NULL),
(0000000012, 0000000003, 'no_access_guest-container', NULL),
(0000000013, 0000000004, 'no_access_guest-jumbotron', NULL),
(0000000014, 0000000006, 'no_access_guest-markdown', NULL),
(0000000015, 0000000006, 'no_access-markdown', NULL),
(0000000016, 0000000003, 'agb-container', NULL),
(0000000017, 0000000003, 'contact-container', NULL),
(0000000018, 0000000003, 'disclaimer-container', NULL),
(0000000019, 0000000003, 'home-container', NULL),
(0000000020, 0000000003, 'impressum-container', NULL),
(0000000025, 0000000010, 'contact-chat', NULL),
(0000000026, 0000000009, 'validate-validate', NULL),
(0000000027, 0000000008, 'toLogin-button', NULL),
(0000000028, 0000000035, 'resetPassword-resetPassword', NULL),
(0000000029, 0000000004, 'impressum-jumbotron', NULL),
(0000000030, 0000000005, 'impressum-heading', NULL),
(0000000031, 0000000012, 'impressum-card', NULL),
(0000000032, 0000000006, 'impressum-markdown', NULL),
(0000000033, 0000000012, 'impressum-ext-card', NULL),
(0000000034, 0000000006, 'impressum-ext-markdown', NULL),
(0000000035, 0000000041, 'register-register', NULL),
(0000000036, 0000000003, 'login-container', NULL),
(0000000037, 0000000003, 'profile-container', NULL),
(0000000038, 0000000040, 'profile-row-div', NULL),
(0000000039, 0000000040, 'profile-col1-div', NULL),
(0000000040, 0000000040, 'profile-col2-div', NULL),
(0000000041, 0000000012, 'profile-username-card', NULL),
(0000000042, 0000000012, 'profile-password-card', NULL),
(0000000043, 0000000012, 'profile-delete-card', NULL),
(0000000044, 0000000014, 'profile-username-form', NULL),
(0000000045, 0000000016, 'profile-username-input', NULL),
(0000000046, 0000000014, 'profile-password-form', NULL),
(0000000047, 0000000016, 'profile-password-input', NULL),
(0000000048, 0000000016, 'profile-password-confirm-input', NULL),
(0000000049, 0000000006, 'profile-delete-markdown', NULL),
(0000000050, 0000000014, 'profile-delete-form', NULL),
(0000000051, 0000000016, 'profile-delete-input', NULL),
(0000000052, 0000000006, 'profile-username-markdown', NULL),
(0000000053, 0000000012, 'profile-notification-card', NULL),
(0000000054, 0000000006, 'profile-notification-markdown', NULL),
(0000000055, 0000000036, 'profile-notification-formUserInput', NULL),
(0000000056, 0000000016, 'profile-notification-chat-input', NULL),
(0000000057, 0000000016, 'profile-notification-reminder-input', NULL),
(0000000058, 0000000016, 'profile-notification-phone-input', NULL),
(0000000059, 0000000060, 'impressum-version', NULL),
(0000000060, 0000000003, 'chatSubject-container', NULL),
(0000000061, 0000000010, 'chatSubject-chat', NULL),
(0000000062, 0000000003, 'chatTherapist-container', NULL),
(0000000063, 0000000010, 'chatTherapist-chat', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections_fields_translation`
--

DROP TABLE IF EXISTS `sections_fields_translation`;
CREATE TABLE IF NOT EXISTS `sections_fields_translation` (
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `id_genders` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL,
  PRIMARY KEY (`id_sections`,`id_fields`,`id_languages`,`id_genders`),
  KEY `id_sections` (`id_sections`),
  KEY `id_fields` (`id_fields`),
  KEY `id_languages` (`id_languages`),
  KEY `id_genders` (`id_genders`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections_fields_translation`
--

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(0000000001, 0000000001, 0000000002, 0000000001, 'Email'),
(0000000001, 0000000001, 0000000003, 0000000001, 'Email'),
(0000000001, 0000000002, 0000000002, 0000000001, 'Passwort'),
(0000000001, 0000000002, 0000000003, 0000000001, 'Password'),
(0000000001, 0000000003, 0000000002, 0000000001, 'Anmelden'),
(0000000001, 0000000003, 0000000003, 0000000001, 'Login'),
(0000000001, 0000000004, 0000000002, 0000000001, 'Passwort vergessen?'),
(0000000001, 0000000004, 0000000003, 0000000001, 'Forgotten the Password?'),
(0000000001, 0000000005, 0000000002, 0000000001, 'Die Email Adresse oder das Passwort ist nicht korrekt.'),
(0000000001, 0000000005, 0000000003, 0000000001, 'The email address or the password is not correct.'),
(0000000001, 0000000007, 0000000002, 0000000001, 'Bitte einloggen'),
(0000000001, 0000000007, 0000000003, 0000000001, 'Please Login'),
(0000000002, 0000000005, 0000000002, 0000000001, 'Die Benutzerdaten konnten nicht geändert werden.'),
(0000000002, 0000000005, 0000000003, 0000000001, 'Unable to change the user data.'),
(0000000002, 0000000019, 0000000002, 0000000001, 'Die Benutzerdaten konnten nicht gelöscht werden.'),
(0000000002, 0000000019, 0000000003, 0000000001, 'Unable to delete the account.'),
(0000000002, 0000000020, 0000000002, 0000000001, 'Die Benutzerdaten wurden erfolgreich gelöscht.'),
(0000000002, 0000000020, 0000000003, 0000000001, 'Successfully deleted the account.'),
(0000000002, 0000000023, 0000000001, 0000000001, ''),
(0000000002, 0000000035, 0000000002, 0000000001, 'Die Benutzerdaten wurden erfolgreich geändert.'),
(0000000002, 0000000035, 0000000003, 0000000001, 'The user data were successfully changed.'),
(0000000003, 0000000029, 0000000001, 0000000001, '0'),
(0000000004, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000005, 0000000021, 0000000001, 0000000001, '1'),
(0000000005, 0000000022, 0000000002, 0000000001, 'Seite nicht gefunden'),
(0000000005, 0000000022, 0000000003, 0000000001, 'Page not Found'),
(0000000006, 0000000025, 0000000002, 0000000001, 'Diese Seite konnte leider nicht gefunden werden.'),
(0000000006, 0000000025, 0000000003, 0000000001, 'This page could not be found.'),
(0000000007, 0000000008, 0000000002, 0000000001, 'Zurück'),
(0000000007, 0000000008, 0000000003, 0000000001, 'Back'),
(0000000007, 0000000027, 0000000001, 0000000001, '#back'),
(0000000007, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000008, 0000000008, 0000000002, 0000000001, 'Zur Startseite'),
(0000000008, 0000000008, 0000000003, 0000000001, 'Home'),
(0000000008, 0000000027, 0000000001, 0000000001, '#home'),
(0000000008, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000009, 0000000029, 0000000001, 0000000001, '0'),
(0000000010, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000011, 0000000021, 0000000001, 0000000001, '1'),
(0000000011, 0000000022, 0000000002, 0000000001, 'Kein Zugriff'),
(0000000011, 0000000022, 0000000003, 0000000001, 'No Access'),
(0000000012, 0000000029, 0000000001, 0000000001, '0'),
(0000000013, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000014, 0000000025, 0000000002, 0000000001, 'Um diese Seite zu erreichen müssen Sie eingeloggt sein.'),
(0000000014, 0000000025, 0000000003, 0000000001, 'To reach this page you must be logged in.'),
(0000000015, 0000000025, 0000000002, 0000000001, 'Sie haben keine Zugriffsrechte für diese Seite.'),
(0000000015, 0000000025, 0000000003, 0000000001, 'You do not have access to this page.'),
(0000000016, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000016, 0000000029, 0000000001, 0000000001, '0'),
(0000000017, 0000000029, 0000000001, 0000000001, '0'),
(0000000018, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000018, 0000000029, 0000000001, 0000000001, '0'),
(0000000019, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000019, 0000000029, 0000000001, 0000000001, '1'),
(0000000020, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000020, 0000000029, 0000000001, 0000000001, '0'),
(0000000025, 0000000005, 0000000002, 0000000001, 'Es ist ein Fehler aufgetreten. Die Nachricht konnte nicht gesendet werden.'),
(0000000025, 0000000005, 0000000003, 0000000001, 'An error occurred. The message could not be sent.'),
(0000000025, 0000000030, 0000000002, 0000000001, 'Bitte wählen Sie einen Probanden aus.'),
(0000000025, 0000000030, 0000000003, 0000000001, 'Please select a subject'),
(0000000025, 0000000031, 0000000002, 0000000001, 'Kommunikation mit'),
(0000000025, 0000000031, 0000000003, 0000000001, 'Communication with'),
(0000000025, 0000000032, 0000000002, 0000000001, 'ihrer Psychologin/ihrem Psychologe'),
(0000000025, 0000000032, 0000000003, 0000000001, 'your psychologist'),
(0000000025, 0000000033, 0000000002, 0000000001, 'Probanden'),
(0000000025, 0000000033, 0000000003, 0000000001, 'Subjects'),
(0000000025, 0000000090, 0000000002, 0000000001, 'Senden'),
(0000000025, 0000000090, 0000000003, 0000000001, 'Send'),
(0000000025, 0000000095, 0000000002, 0000000001, 'Lobby'),
(0000000025, 0000000095, 0000000003, 0000000001, 'Lobby'),
(0000000025, 0000000096, 0000000002, 0000000001, 'Neue Nachrichten'),
(0000000025, 0000000096, 0000000003, 0000000001, 'New Messages'),
(0000000025, 0000000110, 0000000002, 0000000001, 'Guten Tag\r\n\r\nSie haben eine neue Nachricht auf der @project Plattform erhalten.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),
(0000000025, 0000000110, 0000000003, 0000000001, 'Hello\r\n\r\nYou received a new message on the @project Plattform.\r\n\r\n@link\r\n\r\nSincerely, your @project team'),
(0000000025, 0000000111, 0000000002, 0000000001, '@project Chat Benachrichtigung'),
(0000000025, 0000000111, 0000000003, 0000000001, '@project Chat Notification'),
(0000000026, 0000000002, 0000000002, 0000000001, 'Passwort'),
(0000000026, 0000000002, 0000000003, 0000000001, 'Password'),
(0000000026, 0000000003, 0000000002, 0000000001, 'Zum Login'),
(0000000026, 0000000003, 0000000003, 0000000001, 'To Login'),
(0000000026, 0000000005, 0000000002, 0000000001, 'Das Aktivieren des Benutzers ist fehlgeschlagen.'),
(0000000026, 0000000005, 0000000003, 0000000001, 'The activation of the user has failed.'),
(0000000026, 0000000009, 0000000002, 0000000001, 'Bitte das Passwort bestätigen'),
(0000000026, 0000000009, 0000000003, 0000000001, 'Please confirm the password'),
(0000000026, 0000000022, 0000000002, 0000000001, 'Benutzer aktivieren'),
(0000000026, 0000000022, 0000000003, 0000000001, 'Activate User'),
(0000000026, 0000000034, 0000000002, 0000000001, 'Erforderliche Daten für die Aktivierung'),
(0000000026, 0000000034, 0000000003, 0000000001, 'Required Data to Activate the User'),
(0000000026, 0000000035, 0000000002, 0000000001, 'Sie können sich nun mit dem von Ihnen gewählten Passwort und Email einloggen und die Seite benutzen.'),
(0000000026, 0000000035, 0000000003, 0000000001, 'You are now able to login in to the web page with the chosen password and email.'),
(0000000026, 0000000036, 0000000002, 0000000001, 'Benutzername'),
(0000000026, 0000000036, 0000000003, 0000000001, 'Username'),
(0000000026, 0000000037, 0000000002, 0000000001, 'Bitte den Benutzernamen eingeben'),
(0000000026, 0000000037, 0000000003, 0000000001, 'Please enter a username'),
(0000000026, 0000000038, 0000000002, 0000000001, 'Ein Name mit dem Sie angesprochen werden wollen. Aus Gründen der Anonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.'),
(0000000026, 0000000038, 0000000003, 0000000001, 'The name with which you would like to be addressed. For reasons of anonymity pleas do **not** use your real name.'),
(0000000026, 0000000039, 0000000002, 0000000001, 'Geschlecht'),
(0000000026, 0000000039, 0000000003, 0000000001, 'Gender'),
(0000000026, 0000000040, 0000000002, 0000000001, 'männlich'),
(0000000026, 0000000040, 0000000003, 0000000001, 'male'),
(0000000026, 0000000041, 0000000002, 0000000001, 'weiblich'),
(0000000026, 0000000041, 0000000003, 0000000001, 'female'),
(0000000026, 0000000042, 0000000002, 0000000001, 'Benutzer aktivieren'),
(0000000026, 0000000042, 0000000003, 0000000001, 'Activate User'),
(0000000026, 0000000043, 0000000002, 0000000001, 'Bitte das Passwort eingeben'),
(0000000026, 0000000043, 0000000003, 0000000001, 'Please enter a password'),
(0000000026, 0000000044, 0000000002, 0000000001, 'Benutzer erfolgreich aktiviert'),
(0000000026, 0000000044, 0000000003, 0000000001, 'User was successfully Activated'),
(0000000027, 0000000008, 0000000002, 0000000001, 'Zum Login'),
(0000000027, 0000000008, 0000000003, 0000000001, 'To Login'),
(0000000027, 0000000027, 0000000001, 0000000001, '#login'),
(0000000027, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000028, 0000000003, 0000000002, 0000000001, 'Zum Login'),
(0000000028, 0000000003, 0000000003, 0000000001, 'To Login'),
(0000000028, 0000000004, 0000000002, 0000000001, 'Passwort zurücksetzen'),
(0000000028, 0000000004, 0000000003, 0000000001, 'Reset Password'),
(0000000028, 0000000005, 0000000002, 0000000001, 'Aktivierungs Email konnte nicht versendet werden.'),
(0000000028, 0000000005, 0000000003, 0000000001, 'Activation email could not be sent.'),
(0000000028, 0000000025, 0000000002, 0000000001, '# Passwort Zurücksetzen\r\n\r\nHier können sie Ihr Passwort zurücksetzen.\r\nBitte geben sie Ihre Email Adresse ein mit welcher sie bei @project registriert sind.\r\nSie werden eine Email erhalten mit einem neuen Aktivierungslink um Ihr Passwort zurück zu setzen.'),
(0000000028, 0000000025, 0000000003, 0000000001, '# Reset Password\r\n\r\nThis page allows you to reset your password.\r\nPlease enter the email address with which you are registered on @project.\r\nYou will receive an email with a new activation link which will allow you to reset the password.'),
(0000000028, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000028, 0000000035, 0000000002, 0000000001, 'Die Aktivierungs Email wurde versendet. Klicken sie auf den Aktivierungslink um Ihr Passwort zurück zu setzen.'),
(0000000028, 0000000035, 0000000003, 0000000001, 'The activation mail was sent. Click the activation link to rest your password.'),
(0000000028, 0000000044, 0000000002, 0000000001, 'Email versendet'),
(0000000028, 0000000044, 0000000003, 0000000001, 'Email Sent'),
(0000000028, 0000000055, 0000000002, 0000000001, 'Bitte Email eingeben'),
(0000000028, 0000000055, 0000000003, 0000000001, 'Please Enter Email'),
(0000000028, 0000000110, 0000000002, 0000000001, 'Guten Tag\r\n\r\nUm das Passwort von Ihrem @project Account zurück zu setzten klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team\r\n'),
(0000000028, 0000000110, 0000000003, 0000000001, 'Hello\r\n\r\nTo reset password of your @project account please click the link below.\r\n\r\n@link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team.\r\n'),
(0000000028, 0000000111, 0000000002, 0000000001, '@project Passwort zurück setzen'),
(0000000028, 0000000111, 0000000003, 0000000001, '@project Password Reset'),
(0000000028, 0000000114, 0000000001, 0000000001, '0'),
(0000000030, 0000000021, 0000000001, 0000000001, '1'),
(0000000030, 0000000022, 0000000002, 0000000001, 'Impressum'),
(0000000030, 0000000022, 0000000003, 0000000001, 'Impressum'),
(0000000031, 0000000023, 0000000001, 0000000001, 'mb-3'),
(0000000031, 0000000028, 0000000001, 0000000001, 'light'),
(0000000031, 0000000046, 0000000001, 0000000001, '0'),
(0000000031, 0000000047, 0000000001, 0000000001, '0'),
(0000000032, 0000000025, 0000000002, 0000000001, '![Logo University of Bern](%logo/Unibe_Logo_16pt_RGB_201807.png|250x|float-left,border-0,mr-5 \"Logo University of Bern\")\r\n\r\n**Universität Bern**  \r\n**Philosophisch-humanwissenschaftliche Fakultät**\r\n\r\nFabrikstrasse 8  \r\n3012 Bern\r\n\r\nTelefon: +41 31 631 55 11\r\n\r\n**Entwicklung:** [Technologieplatform (TPF)](http://www.philhum.unibe.ch/forschung/tpf/index_ger.html)'),
(0000000032, 0000000025, 0000000003, 0000000001, '![Logo University of Bern](%logo/Unibe_Logo_16pt_RGB_201807.png|250x|float-left,border-0,mr-5 \"Logo University of Bern\")\r\n\r\n**University of Bern**  \r\n**Faculty of Human Sciences**\r\n\r\nFabrikstrasse 8  \r\n3012 Bern\r\n\r\nPhone: +41 31 631 55 11\r\n\r\n**Development:** [Technologieplatform (TPF)](http://www.philhum.unibe.ch/forschung/tpf/index_ger.html)'),
(0000000033, 0000000028, 0000000001, 0000000001, 'light'),
(0000000033, 0000000046, 0000000001, 0000000001, '0'),
(0000000033, 0000000047, 0000000001, 0000000001, '0'),
(0000000034, 0000000023, 0000000001, 0000000001, ''),
(0000000034, 0000000025, 0000000002, 0000000001, '| Frameworks & Bibliotheken                                    | Version | Lizenz | Bemerkungen |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                         | 1.2.0   | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)            | 1.1.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                       | 4.4.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.4/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.4/about/license/) |\r\n| [Datatables](https://datatables.net/)                        | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Deepmerge](https://github.com/TehShrike/deepmerge)          | 4.2.2   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Font Awesome](https://fontawesome.com/)                     | 5.2.0   | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                    | 1.5.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                                | 3.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)     | 1.3.10  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [mermaid](https://mermaidjs.github.io/)                      | 8.2.3   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)             | 1.7.1   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [PHPMailer](https://github.com/PHPMailer/PHPMailer)          | 6.0.7   | [LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1)) | [License Details](https://github.com/PHPMailer/PHPMailer#license) |\r\n| [Plotly.js](https://plotly.com/javascript)                   | 1.52.3  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [ResizeSensor](https://github.com/marcj/css-element-queries) | 1.2.2   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)               | 1.7.0   | [MIT](https://tldrlegal.com/license/mit-license) | |'),
(0000000034, 0000000025, 0000000003, 0000000001, '| Frameworks & Libararies                                      | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                         | 1.2.0   | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)            | 1.1.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                       | 4.4.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.4/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.4/about/license/) |\r\n| [Datatables](https://datatables.net/)                        | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Deepmerge](https://github.com/TehShrike/deepmerge)          | 4.2.2   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Font Awesome](https://fontawesome.com/)                     | 5.2.0   | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                    | 1.5.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                                | 3.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)     | 1.3.10  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [mermaid](https://mermaidjs.github.io/)                      | 8.2.3   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)             | 1.7.1   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [PHPMailer](https://github.com/PHPMailer/PHPMailer)          | 6.0.7   | [LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1)) | [License Details](https://github.com/PHPMailer/PHPMailer#license) |\r\n| [Plotly.js](https://plotly.com/javascript)                   | 1.52.3  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [ResizeSensor](https://github.com/marcj/css-element-queries) | 1.2.2   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)               | 1.7.0   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n'),
(0000000035, 0000000001, 0000000002, 0000000001, 'Email'),
(0000000035, 0000000001, 0000000003, 0000000001, 'Email'),
(0000000035, 0000000002, 0000000002, 0000000001, 'Validierungs-Code'),
(0000000035, 0000000002, 0000000003, 0000000001, 'Validation Code'),
(0000000035, 0000000005, 0000000002, 0000000001, 'Die Email Adresse oder der Aktivierungs-Code ist ungültig'),
(0000000035, 0000000005, 0000000003, 0000000001, 'The email address or the activation code is invalid'),
(0000000035, 0000000022, 0000000002, 0000000001, 'Registration'),
(0000000035, 0000000022, 0000000003, 0000000001, 'Registration'),
(0000000035, 0000000023, 0000000001, 0000000001, 'mt-3'),
(0000000035, 0000000035, 0000000002, 0000000001, 'Der erste Schritt der Registrierung war erfolgreich. Sie werden in Kürze eine Email mit einem Aktivierunks-Link erhalten.\r\n\r\nBitte folgen Sie diesem Link um die Registrierung abzuschliessen.'),
(0000000035, 0000000035, 0000000003, 0000000001, 'The first step of the registration was successful.\r\nShortly you will receive an email with an activation link.\r\n\r\nPlease follow this activation link to complete the registration.'),
(0000000035, 0000000044, 0000000002, 0000000001, 'Registrierung erfolgreich'),
(0000000035, 0000000044, 0000000003, 0000000001, 'Registration Successful'),
(0000000035, 0000000090, 0000000002, 0000000001, 'Registrieren'),
(0000000035, 0000000090, 0000000003, 0000000001, 'Register'),
(0000000036, 0000000023, 0000000001, 0000000001, 'mt-3'),
(0000000037, 0000000023, 0000000001, 0000000001, 'my-3'),
(0000000037, 0000000029, 0000000001, 0000000001, '0'),
(0000000038, 0000000023, 0000000001, 0000000001, 'row'),
(0000000039, 0000000023, 0000000001, 0000000001, 'col-12 col-lg'),
(0000000040, 0000000023, 0000000001, 0000000001, 'col'),
(0000000041, 0000000022, 0000000002, 0000000001, 'Benutzername ändern'),
(0000000041, 0000000022, 0000000003, 0000000001, 'Change the Username'),
(0000000041, 0000000023, 0000000001, 0000000001, 'mb-3'),
(0000000041, 0000000028, 0000000001, 0000000001, 'light'),
(0000000041, 0000000046, 0000000001, 0000000001, '1'),
(0000000041, 0000000047, 0000000001, 0000000001, '0'),
(0000000041, 0000000048, 0000000001, 0000000001, ''),
(0000000042, 0000000022, 0000000002, 0000000001, 'Passwort ändern'),
(0000000042, 0000000022, 0000000003, 0000000001, 'Change the Password'),
(0000000042, 0000000023, 0000000001, 0000000001, ''),
(0000000042, 0000000028, 0000000001, 0000000001, 'light'),
(0000000042, 0000000046, 0000000001, 0000000001, '1'),
(0000000042, 0000000047, 0000000001, 0000000001, '0'),
(0000000042, 0000000048, 0000000001, 0000000001, ''),
(0000000043, 0000000022, 0000000002, 0000000001, 'Account löschen'),
(0000000043, 0000000022, 0000000003, 0000000001, 'Delete the Account'),
(0000000043, 0000000023, 0000000001, 0000000001, 'mt-3'),
(0000000043, 0000000028, 0000000001, 0000000001, 'danger'),
(0000000043, 0000000046, 0000000001, 0000000001, '0'),
(0000000043, 0000000047, 0000000001, 0000000001, '1'),
(0000000043, 0000000048, 0000000001, 0000000001, ''),
(0000000044, 0000000008, 0000000002, 0000000001, 'Ändern'),
(0000000044, 0000000008, 0000000003, 0000000001, 'Change'),
(0000000044, 0000000023, 0000000001, 0000000001, ''),
(0000000044, 0000000027, 0000000001, 0000000001, '#self'),
(0000000044, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000044, 0000000051, 0000000002, 0000000001, ''),
(0000000044, 0000000051, 0000000003, 0000000001, ''),
(0000000044, 0000000052, 0000000001, 0000000001, ''),
(0000000045, 0000000008, 0000000002, 0000000001, ''),
(0000000045, 0000000008, 0000000003, 0000000001, ''),
(0000000045, 0000000023, 0000000001, 0000000001, 'mb-3'),
(0000000045, 0000000054, 0000000001, 0000000001, 'text'),
(0000000045, 0000000055, 0000000002, 0000000001, 'Neuer Benutzername'),
(0000000045, 0000000055, 0000000003, 0000000001, 'New Username'),
(0000000045, 0000000056, 0000000001, 0000000001, '1'),
(0000000045, 0000000057, 0000000001, 0000000001, 'user_name'),
(0000000045, 0000000058, 0000000001, 0000000001, ''),
(0000000046, 0000000008, 0000000002, 0000000001, 'Ändern'),
(0000000046, 0000000008, 0000000003, 0000000001, 'Change'),
(0000000046, 0000000023, 0000000001, 0000000001, ''),
(0000000046, 0000000027, 0000000001, 0000000001, '#self'),
(0000000046, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000046, 0000000051, 0000000002, 0000000001, ''),
(0000000046, 0000000051, 0000000003, 0000000001, ''),
(0000000046, 0000000052, 0000000001, 0000000001, ''),
(0000000047, 0000000008, 0000000002, 0000000001, ''),
(0000000047, 0000000008, 0000000003, 0000000001, ''),
(0000000047, 0000000023, 0000000001, 0000000001, 'mb-3'),
(0000000047, 0000000054, 0000000001, 0000000001, 'password'),
(0000000047, 0000000055, 0000000002, 0000000001, 'Neues Passwort'),
(0000000047, 0000000055, 0000000003, 0000000001, 'New Password'),
(0000000047, 0000000056, 0000000001, 0000000001, '1'),
(0000000047, 0000000057, 0000000001, 0000000001, 'password'),
(0000000047, 0000000058, 0000000001, 0000000001, ''),
(0000000048, 0000000008, 0000000002, 0000000001, ''),
(0000000048, 0000000008, 0000000003, 0000000001, ''),
(0000000048, 0000000023, 0000000001, 0000000001, 'mb-3'),
(0000000048, 0000000054, 0000000001, 0000000001, 'password'),
(0000000048, 0000000055, 0000000002, 0000000001, 'Neues Passwort wiederholen'),
(0000000048, 0000000055, 0000000003, 0000000001, 'Repeat New Password'),
(0000000048, 0000000056, 0000000001, 0000000001, '1'),
(0000000048, 0000000057, 0000000001, 0000000001, 'verification'),
(0000000048, 0000000058, 0000000001, 0000000001, ''),
(0000000049, 0000000023, 0000000001, 0000000001, ''),
(0000000049, 0000000025, 0000000002, 0000000001, 'Alle Benutzerdaten werden gelöscht. Das Löschen des Accounts ist permanent und kann **nicht** rückgängig gemacht werden!\r\n\r\nWenn sie ihren Account wirklich löschen wollen bestätigen Sie dies indem Sie ihre Email Adresse eingeben.'),
(0000000049, 0000000025, 0000000003, 0000000001, 'All user data will be deleted. The deletion of the account is permanent and **cannot** be undone!\r\n\r\nIf you are sure you want to delete the account confirm this by entering your email address.'),
(0000000050, 0000000008, 0000000002, 0000000001, 'Löschen'),
(0000000050, 0000000008, 0000000003, 0000000001, 'Delete'),
(0000000050, 0000000023, 0000000001, 0000000001, ''),
(0000000050, 0000000027, 0000000001, 0000000001, '#self'),
(0000000050, 0000000028, 0000000001, 0000000001, 'danger'),
(0000000050, 0000000051, 0000000002, 0000000001, ''),
(0000000050, 0000000051, 0000000003, 0000000001, ''),
(0000000050, 0000000052, 0000000001, 0000000001, ''),
(0000000051, 0000000008, 0000000002, 0000000001, ''),
(0000000051, 0000000008, 0000000003, 0000000001, ''),
(0000000051, 0000000023, 0000000001, 0000000001, 'mb-3'),
(0000000051, 0000000054, 0000000001, 0000000001, 'email'),
(0000000051, 0000000055, 0000000002, 0000000001, 'Email Adresse'),
(0000000051, 0000000055, 0000000003, 0000000001, 'Email Address'),
(0000000051, 0000000056, 0000000001, 0000000001, '1'),
(0000000051, 0000000057, 0000000001, 0000000001, 'email'),
(0000000051, 0000000058, 0000000001, 0000000001, ''),
(0000000052, 0000000023, 0000000001, 0000000001, ''),
(0000000052, 0000000025, 0000000002, 0000000001, 'Dies ist der Name mit dem Sie angesprochen werden wollen. Aus Gründen der Anonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.'),
(0000000052, 0000000025, 0000000003, 0000000001, 'The name with which you would like to be addressed. For reasons of anonymity please do **not** use your real name.'),
(0000000053, 0000000022, 0000000002, 0000000001, 'Benachrichtigungen'),
(0000000053, 0000000022, 0000000003, 0000000001, 'Notifications'),
(0000000053, 0000000023, 0000000001, 0000000001, 'mb-3 mb-lg-0'),
(0000000053, 0000000028, 0000000001, 0000000001, 'light'),
(0000000053, 0000000046, 0000000001, 0000000001, '1'),
(0000000053, 0000000047, 0000000001, 0000000001, '0'),
(0000000053, 0000000048, 0000000001, 0000000001, ''),
(0000000054, 0000000023, 0000000001, 0000000001, ''),
(0000000054, 0000000025, 0000000002, 0000000001, 'Hier können sie automatische Benachrichtigungen ein- und ausschalten. Asserdem können sie eine Telefonnummer hinterlegen um per SMS benachrichtigt zu werden. '),
(0000000054, 0000000025, 0000000003, 0000000001, 'Here you can enable and disable automatic notifications.\r\nAlso, by entering a phone number you can choose to be notified by SMS.'),
(0000000055, 0000000008, 0000000002, 0000000001, 'Ändern'),
(0000000055, 0000000008, 0000000003, 0000000001, 'Change'),
(0000000055, 0000000023, 0000000001, 0000000001, ''),
(0000000055, 0000000028, 0000000001, 0000000001, 'primary'),
(0000000055, 0000000035, 0000000002, 0000000001, 'Die Einstellungen für Benachrichtigungen wurden erfolgreich gespeichert'),
(0000000055, 0000000035, 0000000003, 0000000001, 'The notification settings were successfully saved'),
(0000000055, 0000000057, 0000000001, 0000000001, 'notification'),
(0000000055, 0000000087, 0000000001, 0000000001, '0'),
(0000000056, 0000000008, 0000000002, 0000000001, 'Benachrichtigung bei neuer Nachricht im Chat'),
(0000000056, 0000000008, 0000000003, 0000000001, 'Notification on new chat message'),
(0000000056, 0000000023, 0000000001, 0000000001, ''),
(0000000056, 0000000054, 0000000001, 0000000001, 'checkbox'),
(0000000056, 0000000055, 0000000002, 0000000001, 'chat'),
(0000000056, 0000000055, 0000000003, 0000000001, 'chat'),
(0000000056, 0000000056, 0000000001, 0000000001, '0'),
(0000000056, 0000000057, 0000000001, 0000000001, 'chat'),
(0000000056, 0000000058, 0000000001, 0000000001, 'chat'),
(0000000057, 0000000008, 0000000002, 0000000001, 'Benachrichtung bei Inaktivität'),
(0000000057, 0000000008, 0000000003, 0000000001, 'Notification by inactivity'),
(0000000057, 0000000023, 0000000001, 0000000001, ''),
(0000000057, 0000000054, 0000000001, 0000000001, 'checkbox'),
(0000000057, 0000000055, 0000000002, 0000000001, 'reminder'),
(0000000057, 0000000055, 0000000003, 0000000001, 'reminder'),
(0000000057, 0000000056, 0000000001, 0000000001, '0'),
(0000000057, 0000000057, 0000000001, 0000000001, 'reminder'),
(0000000057, 0000000058, 0000000001, 0000000001, 'reminder'),
(0000000058, 0000000008, 0000000002, 0000000001, 'Telefonnummer für SMS Benachrichtigung'),
(0000000058, 0000000008, 0000000003, 0000000001, 'Phone Number for receiving SMS notifications'),
(0000000058, 0000000023, 0000000001, 0000000001, ''),
(0000000058, 0000000054, 0000000001, 0000000001, 'text'),
(0000000058, 0000000055, 0000000002, 0000000001, 'Bitte Telefonnummer eingeben'),
(0000000058, 0000000055, 0000000003, 0000000001, 'Please enter a phone number'),
(0000000058, 0000000056, 0000000001, 0000000001, '0'),
(0000000058, 0000000057, 0000000001, 0000000001, 'phone'),
(0000000058, 0000000058, 0000000001, 0000000001, ''),
(0000000061, 0000000005, 0000000002, 0000000001, 'Es ist ein Fehler aufgetreten. Die Nachricht konnte nicht gesendet werden.'),
(0000000061, 0000000005, 0000000003, 0000000001, 'An error occurred. The message could not be sent.'),
(0000000061, 0000000030, 0000000002, 0000000001, 'Bitte wählen Sie einen Probanden aus.'),
(0000000061, 0000000030, 0000000003, 0000000001, 'Please select a subject'),
(0000000061, 0000000031, 0000000002, 0000000001, 'Kommunikation mit'),
(0000000061, 0000000031, 0000000003, 0000000001, 'Communication with'),
(0000000061, 0000000032, 0000000002, 0000000001, 'ihrer Psychologin/ihrem Psychologe'),
(0000000061, 0000000032, 0000000003, 0000000001, 'your psychologist'),
(0000000061, 0000000033, 0000000002, 0000000001, 'Probanden'),
(0000000061, 0000000033, 0000000003, 0000000001, 'Subjects'),
(0000000061, 0000000090, 0000000002, 0000000001, 'Senden'),
(0000000061, 0000000090, 0000000003, 0000000001, 'Send'),
(0000000061, 0000000095, 0000000002, 0000000001, 'Lobby'),
(0000000061, 0000000095, 0000000003, 0000000001, 'Lobby'),
(0000000061, 0000000096, 0000000002, 0000000001, 'Neue Nachrichten'),
(0000000061, 0000000096, 0000000003, 0000000001, 'New Messages'),
(0000000061, 0000000110, 0000000002, 0000000001, 'Guten Tag\r\n\r\nSie haben eine neue Nachricht auf der @project Plattform erhalten.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),
(0000000061, 0000000110, 0000000003, 0000000001, 'Hello\r\n\r\nYou received a new message on the @project Plattform.\r\n\r\n@link\r\n\r\nSincerely, your @project team'),
(0000000061, 0000000111, 0000000002, 0000000001, '@project Chat Benachrichtigung'),
(0000000061, 0000000111, 0000000003, 0000000001, '@project Chat Notification'),
(0000000063, 0000000005, 0000000002, 0000000001, 'Es ist ein Fehler aufgetreten. Die Nachricht konnte nicht gesendet werden.'),
(0000000063, 0000000005, 0000000003, 0000000001, 'An error occurred. The message could not be sent.'),
(0000000063, 0000000030, 0000000002, 0000000001, 'Bitte wählen Sie einen Probanden aus.'),
(0000000063, 0000000030, 0000000003, 0000000001, 'Please select a subject'),
(0000000063, 0000000031, 0000000002, 0000000001, 'Kommunikation mit'),
(0000000063, 0000000031, 0000000003, 0000000001, 'Communication with'),
(0000000063, 0000000032, 0000000002, 0000000001, 'ihrer Psychologin/ihrem Psychologe'),
(0000000063, 0000000032, 0000000003, 0000000001, 'your psychologist'),
(0000000063, 0000000033, 0000000002, 0000000001, 'Probanden'),
(0000000063, 0000000033, 0000000003, 0000000001, 'Subjects'),
(0000000063, 0000000090, 0000000002, 0000000001, 'Senden'),
(0000000063, 0000000090, 0000000003, 0000000001, 'Send'),
(0000000063, 0000000095, 0000000002, 0000000001, 'Lobby'),
(0000000063, 0000000095, 0000000003, 0000000001, 'Lobby'),
(0000000063, 0000000096, 0000000002, 0000000001, 'Neue Nachrichten'),
(0000000063, 0000000096, 0000000003, 0000000001, 'New Messages'),
(0000000063, 0000000110, 0000000002, 0000000001, 'Guten Tag\r\n\r\nSie haben eine neue Nachricht auf der @project Plattform erhalten.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),
(0000000063, 0000000110, 0000000003, 0000000001, 'Hello\r\n\r\nYou received a new message on the @project Plattform.\r\n\r\n@link\r\n\r\nSincerely, your @project team'),
(0000000063, 0000000111, 0000000002, 0000000001, '@project Chat Benachrichtigung'),
(0000000063, 0000000111, 0000000003, 0000000001, '@project Chat Notification');

-- --------------------------------------------------------

--
-- Table structure for table `sections_hierarchy`
--

DROP TABLE IF EXISTS `sections_hierarchy`;
CREATE TABLE IF NOT EXISTS `sections_hierarchy` (
  `parent` int(10) UNSIGNED ZEROFILL NOT NULL,
  `child` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `parent` (`parent`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections_hierarchy`
--

INSERT INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
(0000000002, 0000000037, 0),
(0000000003, 0000000004, NULL),
(0000000004, 0000000005, 0),
(0000000004, 0000000006, 10),
(0000000004, 0000000007, 20),
(0000000004, 0000000008, 30),
(0000000009, 0000000010, 0),
(0000000010, 0000000007, 20),
(0000000010, 0000000008, 30),
(0000000010, 0000000011, 0),
(0000000010, 0000000015, 10),
(0000000012, 0000000013, 0),
(0000000013, 0000000007, 20),
(0000000013, 0000000011, 0),
(0000000013, 0000000014, 10),
(0000000013, 0000000027, 30),
(0000000017, 0000000025, 10),
(0000000020, 0000000029, 0),
(0000000020, 0000000031, 10),
(0000000020, 0000000033, 20),
(0000000020, 0000000059, 11),
(0000000029, 0000000030, 0),
(0000000031, 0000000032, 0),
(0000000033, 0000000034, 0),
(0000000036, 0000000001, 1),
(0000000036, 0000000035, 2),
(0000000037, 0000000038, 0),
(0000000038, 0000000039, 0),
(0000000038, 0000000040, 10),
(0000000039, 0000000041, 0),
(0000000039, 0000000053, 10),
(0000000040, 0000000042, 0),
(0000000040, 0000000043, 10),
(0000000041, 0000000044, 0),
(0000000042, 0000000046, 0),
(0000000043, 0000000049, 0),
(0000000043, 0000000050, 10),
(0000000044, 0000000045, 10),
(0000000044, 0000000052, 0),
(0000000046, 0000000047, 0),
(0000000046, 0000000048, 10),
(0000000050, 0000000051, 0),
(0000000053, 0000000054, 0),
(0000000053, 0000000055, 10),
(0000000055, 0000000056, 0),
(0000000055, 0000000057, 10),
(0000000055, 0000000058, 20),
(0000000060, 0000000061, 1),
(0000000062, 0000000063, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sections_navigation`
--

DROP TABLE IF EXISTS `sections_navigation`;
CREATE TABLE IF NOT EXISTS `sections_navigation` (
  `parent` int(10) UNSIGNED ZEROFILL NOT NULL,
  `child` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  KEY `parent` (`parent`),
  KEY `id_pages` (`id_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `styleGroup`
--

DROP TABLE IF EXISTS `styleGroup`;
CREATE TABLE IF NOT EXISTS `styleGroup` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styleGroup`
--

INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES
(0000000001, 'intern', NULL, NULL),
(0000000002, 'Form', 'A form is a wrapper for input fields. It allows to send content of the input fields to the server and store the data to the database. Several style are available:', 60),
(0000000003, 'Input', 'An input field must be placed inside a form wrapper. An input field allows a user to enter data and submit these to the server. The chosen form wrapper decides what happens with the submitted data. The following input fields styles are available:', 70),
(0000000004, 'Wrapper', 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:', 10),
(0000000005, 'Text', 'Text styles allow to control how text is displayed. These styles are used to create the main content. The following styles are available:', 20),
(0000000006, 'List', 'Lists are styles that allow to define more sophisticated lists than the markdown syntax allows. They come with attached javascript functionality. The following lists are available:', 50),
(0000000007, 'Media', 'The media styles allow to display different media on a webpage. The following styles are available:', 40),
(0000000008, 'Link', 'Link styles allow to render different types of links:', 30),
(0000000009, 'Admin', 'The admin styles are for user registration and access handling.\r\nThe following styles are available:', 80),
(0000000010, 'Graph', 'Graph styles allow to draw graps and diagrams based on static (uploaded assets) or dynamic (user input) data.', 55),
(0000000011, 'Filter', 'Filter styles allow to filter data sets and store the filter state in the session. This allows to filter all styles using the same data source with only one filter', 56);

-- --------------------------------------------------------

--
-- Table structure for table `styles`
--

DROP TABLE IF EXISTS `styles`;
CREATE TABLE IF NOT EXISTS `styles` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `id_group` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `description` longtext,
  PRIMARY KEY (`id`),
  KEY `id_type` (`id_type`),
  KEY `id_group` (`id_group`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES
(0000000001, 'login', 0000000002, 0000000009, 'provides a small form where the user can enter his or her email and password to access the WebApp. It also includes a link to reset a password.'),
(0000000002, 'profile', 0000000002, 0000000001, ''),
(0000000003, 'container', 0000000001, 0000000004, 'is an **invisible** wrapper.'),
(0000000004, 'jumbotron', 0000000001, 0000000004, 'is a **visible** wrapper that wraps its content in a grey box with large spacing.'),
(0000000005, 'heading', 0000000001, 0000000005, 'is used to display the 6 levels of HTML headings.'),
(0000000006, 'markdown', 0000000002, 0000000005, 'is the bread-and-butter style which allows to style content in a very flexible way. In addition to markdown syntax, pure HTML statements are allowed which makes this style very versatile. It is recommended to limit the usage of HTML to a minimum in order to keep the layout of the webpage consistent.'),
(0000000007, 'markdownInline', 0000000001, 0000000005, 'is similar to the markdown style but is intended for one-line text where emphasis is required.'),
(0000000008, 'button', 0000000001, 0000000008, 'renders a button-style link with several predefined colour schemes.'),
(0000000009, 'validate', 0000000002, 0000000001, ''),
(0000000010, 'chat', 0000000002, 0000000001, ''),
(0000000011, 'alert', 0000000001, 0000000004, 'is a **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.'),
(0000000012, 'card', 0000000001, 0000000004, 'is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.'),
(0000000013, 'figure', 0000000001, 0000000007, 'allows to attach a caption to media elements. A figure expects a media style as its immediate child.'),
(0000000014, 'form', 0000000001, 0000000002, 'provides only the client-side functionality and does not do anything with the submitted data. This is intended to be connected with a custom component (required PHP programming).'),
(0000000015, 'image', 0000000001, 0000000007, 'allows to render an image on a page.'),
(0000000016, 'input', 0000000002, 0000000003, 'is a one-line input field style that allows to enter different types of data (e.g. text, color, time, date, checkbox).'),
(0000000017, 'plaintext', 0000000001, 0000000005, 'renders simple text. No special syntax is allowed here.'),
(0000000018, 'link', 0000000001, 0000000008, 'renders a standard link but allows to open the target in a new tab.'),
(0000000019, 'progressBar', 0000000001, 0000000007, 'allows to render a static progress bar.'),
(0000000020, 'quiz', 0000000001, 0000000004, 'is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.'),
(0000000021, 'rawText', 0000000001, 0000000005, 'renders text in a mono-space font which makes it useful to display code.'),
(0000000022, 'select', 0000000002, 0000000003, 'is a input field style that provides a predefined set of choices which can be selected with a dropdown menu. In contrast to the radio style the select style has a different visual appearance and provides a list of options where also multiple options can be chosen.'),
(0000000023, 'slider', 0000000002, 0000000003, 'is an extension of the style input of type range. It allows to provide a label for each position of the slider.'),
(0000000024, 'tab', 0000000001, 0000000004, 'is a child element of the style `tabs`.'),
(0000000025, 'tabs', 0000000001, 0000000004, 'is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.'),
(0000000026, 'textarea', 0000000002, 0000000003, 'is a multi-line input field style that allows to enter multiple lines of text.'),
(0000000027, 'video', 0000000001, 0000000007, 'allows to load and display a video on a page.'),
(0000000028, 'accordionList', 0000000002, 0000000006, 'is a **hierarchical** list where the root level is rendered as an accordion with only one root item expanded at a time.'),
(0000000030, 'navigationContainer', 0000000001, 0000000004, 'is an **invisible** wrapper and is used specifically for navigation pages.'),
(0000000031, 'navigationAccordion', 0000000003, 0000000001, ''),
(0000000032, 'nestedList', 0000000002, 0000000006, 'is a **hierarchical** list where each root item item can be collapsed and expanded by clicking on a chevron.'),
(0000000033, 'navigationNested', 0000000003, 0000000001, ''),
(0000000034, 'sortableList', 0000000001, 0000000006, 'is **non-hierarchical** but can be sorted, new items can be added as well as items can be deleted. Note that only the visual aspects of these functions are rendered. The implementation of the functions need to be defined separately with javascript (See <a href=\"https://github.com/RubaXa/Sortable\" target=\"_blank\">Sortable</a> for more details).'),
(0000000035, 'resetPassword', 0000000002, 0000000001, ''),
(0000000036, 'formUserInput', 0000000002, 0000000002, 'stores the data from all child input fields into the database and displays the latest set of data in the database as values in the child input field (if `is_log` is unchecked).'),
(0000000038, 'radio', 0000000002, 0000000003, 'allows to predefine a set of options for the user to select. It provides a list of options where only one option can be chosen.'),
(0000000039, 'showUserInput', 0000000002, 0000000002, 'allows to display user input data. Use the name of a form to display the corresponding data.'),
(0000000040, 'div', 0000000001, 0000000004, 'allows to wrap its children in a simple HTML `<div>` tag. This allows to create more complex layouts with the help of bootstrap classes.'),
(0000000041, 'register', 0000000002, 0000000009, 'provides a small form to allow a user to register for the WebApp. In order to register a user must provide a valid email and activation code. Activation codes can be generated in the admin section of the WebApp. The list of available codes can be exported.'),
(0000000042, 'conditionalContainer', 0000000002, 0000000004, 'is an **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition resolves to true.'),
(0000000043, 'audio', 0000000001, 0000000007, 'allows to load and replay an audio source on a page.'),
(0000000044, 'carousel', 0000000001, 0000000007, 'allows to render multiple images as a slide-show.'),
(0000000045, 'json', 0000000002, 0000000004, 'allows to describe styles with `json` Syntax'),
(0000000046, 'userProgress', 0000000002, 0000000009, 'A progress bar to indicate the overall experiment progress of a user.'),
(0000000047, 'mermaidForm', 0000000002, 0000000002, 'Style to create diagrams using markdown syntax. Use <a href=\"https://mermaidjs.github.io/demos.html\" target=\"_blank\">mermaid markdown</a> syntax here.'),
(0000000048, 'emailForm', 0000000002, 0000000002, 'A form to accept an email address and automatically send two emails: An email to the address entered in the form and another email to admins, specified in the style.'),
(0000000049, 'autocomplete', 0000000001, 0000000001, 'Provides a text input field which executes an AJAX request on typing.\r\nA AJAX request class and method must be defined for this to work.'),
(0000000050, 'graph', 0000000002, 0000000010, 'The most general graph style which allows to render a vast variety of graphs but requires extensive configuration. All other graph styles are based on this style.'),
(0000000051, 'graphSankey', 0000000002, 0000000010, 'Create a Sankey diagram from user input data or imported static data.'),
(0000000052, 'filterToggle', 0000000002, 0000000011, 'Create a toggle button which will enable or disable a filter on a set of data.'),
(0000000053, 'filterToggleGroup', 0000000002, 0000000011, 'Create a group of toggle buttons which will enable or disable a filter on a set of data. Multiple active buttons are combinde with the logic or function.'),
(0000000054, 'graphPie', 0000000002, 0000000010, 'Create a pie diagram from user input data or imported static data.'),
(0000000055, 'graphBar', 0000000002, 0000000010, 'Create a bar diagram from user input data or imported static data.'),
(0000000056, 'graphLegend', 0000000001, 0000000010, 'Render colored list of items. This can be used to show one global legend for multiple graphs.'),
(0000000057, 'navigationBar', 0000000001, 0000000001, 'Provides a navigation bar style'),
(0000000058, 'qualtricsSurvey', 0000000002, 0000000002, 'Visualize a qualtrics survey. It is shown in iFrame.'),
(0000000059, 'search', 0000000002, 0000000003, 'Add search input box. Used for pages that accept additional paramter. On click the text is assigned in the url and it can be used as a parameter'),
(0000000060, 'version', 0000000002, 0000000009, 'Add information about the DB version and for the git version of Selfhelp');

-- --------------------------------------------------------

--
-- Table structure for table `styles_fields`
--

DROP TABLE IF EXISTS `styles_fields`;
CREATE TABLE IF NOT EXISTS `styles_fields` (
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_styles`,`id_fields`),
  KEY `id_styles` (`id_styles`),
  KEY `id_fields` (`id_fields`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styles_fields`
--

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES
(0000000001, 0000000001, NULL, 'The placeholder in the email input field.'),
(0000000001, 0000000002, NULL, 'The placeholder in the password input field.'),
(0000000001, 0000000003, NULL, 'The text on the login button.'),
(0000000001, 0000000004, NULL, 'The name of the password reset link.'),
(0000000001, 0000000005, NULL, 'This text is displayed in a danger-alert-box whenever the login fails.'),
(0000000001, 0000000007, NULL, 'The text displayed in the login card header.'),
(0000000001, 0000000028, 'dark', 'This allows to choose the colour scheme for the login form.'),
(0000000002, 0000000005, NULL, 'This text will be displayed if a user was unable to change his settings.'),
(0000000002, 0000000006, NULL, 'This field holds the main structure of the profile page content. Styles were used to construct this in order to keep the layout consistent. In order to change specific aspects of the profile page, navigate through the children and make changes where needed.'),
(0000000002, 0000000019, NULL, 'This text will be displayed if a user wants to delete his or her account but the operation failed.'),
(0000000002, 0000000020, NULL, 'This text will be displayed upon successful deletion of a user account.'),
(0000000002, 0000000035, NULL, 'This text will be displayed upon successful change of user settings.'),
(0000000003, 0000000006, NULL, 'The child sections to be added to the `container` body. This can hold any style.'),
(0000000003, 0000000029, '0', 'Select for a full width container, spanning the entire width of the viewport.'),
(0000000003, 0000000146, '0', 'If `export_pdf` is checked, the container has an export button in the top righ corner. All children in the container can be exported to a PDF file.\n\nadd class `skipPDF` to the `css` field in an element which should not be exported inthe PDF file\n\nadd class `pdfStartNewPage` to the `css` field in an element which should be on a new page\n\nadd class `pdfStartNewPageAfter` to the `css` field in an element which should insert a new page after it is loaded on the page\n'),
(0000000004, 0000000006, NULL, 'The child sections to be added to the `jumbotron` body. This can hold any style.'),
(0000000005, 0000000021, '1', 'The HTML heading level (1-6)'),
(0000000005, 0000000022, NULL, 'The text to be rendered as heading.'),
(0000000006, 0000000025, NULL, 'Use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> syntax here.'),
(0000000006, 0000000145, '', 'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: \n ```\n [\n	{\n		\"type\": \"static|dynamic\",\n		\"table\": \"table_name | #url_param1\",\n        \"retrieve\": \"first | last | all\",\n		\"fields\": [\n			{\n				\"field_name\": \"name | #url_param2\",\n				\"field_holder\": \"@field_1\",\n				\"not_found_text\": \"my field was not found\"				\n			}\n		]\n	}\n]\n```\nIf the page supports parameters, then the parameter can be accessed with `#` and the name of the paramer. Example `#url_param_name`. \n\nIn order to inlcude the retrieved data in the markdown field, include the `field_holder` that wa defined in the markdown text.\n\nWe can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)'),
(0000000007, 0000000026, NULL, 'Only use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> elements that can be displayed inline (e.g. bold, italic, etc).'),
(0000000008, 0000000008, NULL, 'The text to appear on the button.'),
(0000000008, 0000000027, NULL, 'Use a full URL or any special characters as defined <a href=\"https://selfhelp.psy.unibe.ch/demo/style/440\" target=\"_blank\">here</a>.'),
(0000000008, 0000000028, 'primary', 'The <a href=\"https://getbootstrap.com/docs/4.1/components/buttons/#examples\" target=\"_blank\">bootstrap type</a> of the button.'),
(0000000009, 0000000002, NULL, 'The label above the password input fields.'),
(0000000009, 0000000003, NULL, 'The text on the back to login link on the success page.'),
(0000000009, 0000000005, NULL, 'This text is displayed in an danger-alert-box if the validation fails.'),
(0000000009, 0000000006, NULL, 'This field is intended for custom input fields that allow the collect further information about a user (also see field `name`)'),
(0000000009, 0000000009, NULL, 'The placeholder for the password confirmation input field.'),
(0000000009, 0000000022, NULL, 'The title of the validation page.'),
(0000000009, 0000000034, NULL, 'The text in the card header of the validation form.'),
(0000000009, 0000000035, NULL, 'On successful validation a new page appears where the content of this field is displayed in a `jumbotron`.'),
(0000000009, 0000000036, NULL, 'The label above the username input field.'),
(0000000009, 0000000037, NULL, 'The placeholder for the username input field.'),
(0000000009, 0000000038, NULL, 'The small description text below the username input field.'),
(0000000009, 0000000039, NULL, 'The label above the gender selection radio buttons.'),
(0000000009, 0000000040, NULL, 'The label next to the male radio button option.'),
(0000000009, 0000000041, NULL, 'The label next to the female radio button option.'),
(0000000009, 0000000042, NULL, 'The text on the submit button to activate the user account.'),
(0000000009, 0000000043, NULL, 'The placeholder for the password input field.'),
(0000000009, 0000000044, NULL, 'On successful validation a new page appears where the content of this field is displayed in as a heading in a `jumbotron`.'),
(0000000009, 0000000057, NULL, 'The validate style allows to add custom input fields in order to collect further data about the user. This data is stored in the database like any other user input. The content of the field `name` will be used in the column `form_name` of the user data export (in the menu *Admin/Export*) for all custom validation input fields.'),
(0000000010, 0000000005, NULL, 'The here defined text will be displayed in an danger-alert-box if it was not possible to send the message. If this alert is shown there is probably an issue with the server.'),
(0000000010, 0000000030, NULL, 'This message is displayed to a user in the role `Therapist` if no `Subject` is selected.'),
(0000000010, 0000000031, NULL, 'This is the first part of the text that is displayed in the message card header. The second part of this text depends on the role of the user.'),
(0000000010, 0000000032, NULL, 'This is the second part of the message card header if the role of the user is `Therapist`.'),
(0000000010, 0000000033, NULL, 'This is the second part of the message card header if the role of the user is `Subject`.'),
(0000000010, 0000000090, NULL, 'The text on the submit button.'),
(0000000010, 0000000095, 'Lobby', 'The name of the root chat room (the room every user is part of).'),
(0000000010, 0000000096, 'New Messages', 'This text is used as a divider between messages that are already read and new messages.'),
(0000000010, 0000000110, NULL, 'The notification email to be sent to receiver of the chat message. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@link` will be replaced by the link to the chat page.'),
(0000000010, 0000000111, NULL, 'The subject of the notification email to be sent to the receiver of the chat message. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.'),
(0000000010, 0000000114, '0', 'If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext'),
(0000000011, 0000000006, NULL, 'The child elements to be added to the alert wrapper.'),
(0000000011, 0000000028, 'primary', 'The bootstrap color styling of the alert wrapper.'),
(0000000011, 0000000045, '0', 'If *checked* the alert wrapper can be dismissed by clicking on a close symbol.\r\nIf *unchecked* the close symbol is not rendered.'),
(0000000012, 0000000006, NULL, 'The child elements to be added to the card body.'),
(0000000012, 0000000022, NULL, 'The content of the card header. If not set, the card will be rendered without a header section.'),
(0000000012, 0000000028, 'light', 'A bootstrap-esque color styling of the card border and header.'),
(0000000012, 0000000046, '1', 'If the field `is_collapsible` is *checked* and the field `is_expanded` is *unchecked* the card is collapsed by default and only by clicking on the header will the body be shown. This field has no effect if `is_collapsible` is left *unchecked*.'),
(0000000012, 0000000047, '0', 'If *checked* the card body can be collapsed into the header by clicking on the header.\nIf left *unchecked* no such interaction is possible.'),
(0000000012, 0000000048, NULL, 'The target url of the edit button. If set, an edit button will appear on right of the card header and link to the specified url. If not set no button will be shown.'),
(0000000013, 0000000006, NULL, 'The child sections to be added to the `figure` body. Add only sections of style `image` or `audio` here.'),
(0000000013, 0000000049, NULL, 'The title to be prepended to the text defined in filed `caption`.'),
(0000000013, 0000000050, NULL, 'The caption of the figure.'),
(0000000014, 0000000006, NULL, 'The child sections to be added to the `form` body. This can hold any style.'),
(0000000014, 0000000008, NULL, 'The label on the submit button.'),
(0000000014, 0000000027, NULL, 'The submit URL.'),
(0000000014, 0000000028, NULL, 'The visual appearance of the submit button as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).'),
(0000000014, 0000000051, NULL, 'If set, a cancel button will be rendered. The here defined text will be used as label for this button.'),
(0000000014, 0000000052, NULL, 'The target URL of the cancel button.'),
(0000000014, 0000000149, '0', 'Selecting submit and send email will add additional button to the form. If the user click on that button the data inut will ne send on his/her email.'),
(0000000014, 0000000150, '', 'The label on the submit and send button'),
(0000000015, 0000000022, NULL, 'The text to be shown when hovering over the image.'),
(0000000015, 0000000029, '1', 'If enabled the image scales responsively.'),
(0000000015, 0000000030, NULL, 'The alternative text to be shown if the image cannot be loaded.'),
(0000000015, 0000000053, NULL, 'The image source. If the image is an asset simply use the full name of the asset here.'),
(0000000016, 0000000008, NULL, 'If this field is set, a this text will be rendered above the input field.'),
(0000000016, 0000000054, 'text', 'A selection of HTML input types. Note that support for these types depends on the browser. Uf a type is not supported by a browser, usually the type `text` is used.'),
(0000000016, 0000000055, NULL, 'If this field is set, the text will be rendered as background inside the input field and will disappear when a value is enterd.'),
(0000000016, 0000000056, '0', 'If enabled the form can only be submitted if a value is enterd in this input field.'),
(0000000016, 0000000057, NULL, 'The name of the input form field. This name must be unique within a form.'),
(0000000016, 0000000058, NULL, 'The default value of the input field.'),
(0000000017, 0000000024, NULL, 'The text to be rendered.'),
(0000000017, 0000000059, '0', 'If enabled the text will be rendered within HTML `<p></p>` tags. If disabled the text will be rendered without any wrapping tags.'),
(0000000018, 0000000008, NULL, 'Specifies the clickable text. If left empty the URL as specified in the field `url` will be used.'),
(0000000018, 0000000027, NULL, 'Use a full URL or any special characters as defined <a href=\"https://selfhelp.psy.unibe.ch/demo/style/440\" target=\"_blank\">here</a>.'),
(0000000018, 0000000086, NULL, 'If checked the link will be opened in a new tab. If unchecked the link will open in the current tab.'),
(0000000019, 0000000028, 'primary', 'The visual appearance of the progrres bar as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).'),
(0000000019, 0000000060, '0', 'The current value of the progress bar.'),
(0000000019, 0000000061, '1', 'The maximal value of the prpgress bar. The minimal value is 0.'),
(0000000019, 0000000101, '1', 'If enabled diagonal stripes are visualized on the progress bar.'),
(0000000019, 0000000102, '1', 'If enabled a label of the form `<count>/<count_max>` is displayed on the proggress bar where `<count>` is the value defined in field `count` and `<count_max>` the value defined in field `count_max`.'),
(0000000020, 0000000028, 'light', 'The visual appearance of the buttons as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).'),
(0000000020, 0000000050, NULL, 'A question with a binary answer (e.g. Right, Wrong).'),
(0000000020, 0000000062, NULL, 'The label on the first answer button (e.g. right). Clicking this button will reveal the content as defined in the field `right_content`.'),
(0000000020, 0000000063, NULL, 'The label on the second answer button (e.g. wrong). Clicking this button will reveal the content as defined in the field `wrong_content`.'),
(0000000020, 0000000064, NULL, 'The body to the first answer button as defined in field `label_right`. The content of this field usually states whether this answer was correct or false and provides an explanation as to why.'),
(0000000020, 0000000065, NULL, 'The body to the second answer button as defined in field `label_wrong`. The content of this field usually states whether this answer was correct or false and provides an explanation as to why.'),
(0000000021, 0000000024, NULL, 'The text to be rendered with mono-space font.'),
(0000000022, 0000000008, NULL, 'If this field is set, a this text will be rendered above the selection.'),
(0000000022, 0000000030, NULL, 'This field specifies the text that is displayed on the disabled option when no default value is defined'),
(0000000022, 0000000056, '0', 'If enabled the form can only be submitted if a value is selected.'),
(0000000022, 0000000057, NULL, 'The name of the selection form field. This name must be unique within a form.'),
(0000000022, 0000000058, NULL, 'The preselected item of the selection elements.'),
(0000000022, 0000000066, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of select objects where each object has the following keys:\n- `value`: the value to be submitted if this item is selected\n-`text`: the text rendered as selection option.\n\nAn Example\n```\n[{\n  \"value\":\"1\",\n  \"text\": \"Item1\"\n},\n{\n  \"value\":\"2\",\n  \"text\":\"Item2\"\n},\n{\n  \"value\":\"3\",\n  \"text\": \"Item3\"\n}]\n```'),
(0000000022, 0000000067, '0', 'If enabled the selction items will be rendered as a list where multiple items can be selected as opposed to a dropdown menu.'),
(0000000022, 0000000070, '5', 'Set the maximum elements that can be shown in the drop down list before the scroller appears'),
(0000000022, 0000000141, '0', 'If checked the select component will have a live search text box which can filter the values'),
(0000000022, 0000000142, '0', 'If checked the select component is disabled'),
(0000000023, 0000000008, NULL, 'If this field is set, a this text will be rendered above the slider.'),
(0000000023, 0000000057, NULL, 'The name of the slider form field. This name must be unique within a form.'),
(0000000023, 0000000058, NULL, 'The preselected position of the slider.'),
(0000000023, 0000000068, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of labels. Each label will be assigned to a slider position so make sure that the number of labels matches the range defined with the fields `min` and `max`.'),
(0000000023, 0000000069, '0', 'The minimal value of the range.'),
(0000000023, 0000000070, '5', 'The maximal value of the range'),
(0000000024, 0000000006, NULL, 'The child sections to be added to the `tab` body. This can hold any style.'),
(0000000024, 0000000008, NULL, NULL),
(0000000024, 0000000028, 'light', NULL),
(0000000024, 0000000046, '0', NULL),
(0000000025, 0000000006, NULL, 'The child sections to be added to the `tabs` body. Add only sections of style `tab` here.'),
(0000000026, 0000000008, NULL, 'If this field is set, a this text will be rendered above the textarea.'),
(0000000026, 0000000055, NULL, 'If this field is set, the text will be rendered as background inside the textarea and will disappear when a value is enterd.'),
(0000000026, 0000000056, '0', 'If enabled the form can only be submitted if a value is enterd in this textarea.'),
(0000000026, 0000000057, NULL, 'The name of the textarea form field. This name must be unique within a form.'),
(0000000026, 0000000058, NULL, 'The default value of the textarea form field.'),
(0000000027, 0000000029, '1', NULL),
(0000000027, 0000000030, NULL, 'The alternative text to be displayed if the video cannot be loaded.'),
(0000000027, 0000000071, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of source objects where each object has the following keys:\n - `source`: The source of the video file. If it is an asset, simply use the full name of the asset.\n - `type`: The [type](!https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Containers) of the video file.\n\nFor example:\n```\n[{\n  \"source\": \"video_name.mp4\",\n  \"type\": \"video/mp4\"\n}, {\n  \"source\":\"video_name.ogg\",\n  \"type\": \"video/ogg\"\n}, {\n  \"source\":\"video_name.webm\",\n  \"type\": \"video/webm\"\n}]\n```\n'),
(0000000028, 0000000031, NULL, 'This text will be added as a perfix to each root item.'),
(0000000028, 0000000066, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of objects where each object has the following keys:\n - `id`: A unique identifier of the item\n - `title`: The name of the item.\n - `url`: An URL to where this item will link. This field is optional.\n - `children`: A list of objects, again with the keys `id` and `title` and the optional keys `url` and `children`\n\nFor example:\n```\n[{\n  \"id\": 1,\n  \"title\": \"Item1\",\n  \"children\": [{\n    \"id\": 2,\n    \"title\": \"Item1.1\"\n  }, {\n    \"id\": 3,\n    \"title\": \"Item1.2\",\n    \"children\": [{\n      \"id\": 4,\n      \"title\": \"Item1.2.1\"\n    }]\n  }]\n},\n{\n  \"id\": 5,\n  \"title\": \"Item2\",\n  \"children\": [{\n     \"id\": 5,\n     \"title\": \"Item2.1\"\n  }] \n},\n{\n  \"id\": 6,\n  \"title\": \"Item3\",\n  \"children\": [{\n     \"id\": 7,\n     \"title\": \"Item3.1\"\n  }]\n}]\n```\n.'),
(0000000028, 0000000072, NULL, 'This field only has an effect if root items have an URL defined (see field `items`). If not defined, links on root items will be displayed as symbols. To expand the root item one would click on the root item and to follow to the link one would click on the link symbol. If this field is set instead of the link symbol, a new chlid element will be generated which serves as a link to the URL of the root item. The here defined text will be used as label for this link.'),
(0000000028, 0000000083, NULL, 'Define any unique name here if multiple accordionList styles are used on the same page.'),
(0000000028, 0000000084, '0', 'Defines which id is marked as active. This will also cause the corresponding root item to be expanded.'),
(0000000030, 0000000006, NULL, 'Add sections here wich will be rendered below the content defined in field `text_md`.'),
(0000000030, 0000000022, NULL, 'All navigation sections of a navigation page can be rendered as a list style. This field specifies the name of this navigation section to be used in such a list style.'),
(0000000030, 0000000025, '# @title', 'The content (markdown) of this field will be rendered at the top of the navigation section. Further sections added through the field `children` will be rendered below this. Note that here, the keyword `@title` can be used and will be replaced by the content of the field `title`.'),
(0000000031, 0000000029, '1', NULL),
(0000000031, 0000000031, NULL, NULL),
(0000000031, 0000000072, NULL, NULL),
(0000000031, 0000000073, NULL, NULL),
(0000000031, 0000000074, NULL, NULL),
(0000000031, 0000000075, NULL, NULL),
(0000000031, 0000000104, '1', NULL),
(0000000032, 0000000031, NULL, 'If this is set the list will be collapse on small screens and the here defined text will be displayed as title of the collpsed list.'),
(0000000032, 0000000046, '0', 'If enabled all items in the list will be expanded by default. If disabled all items will be collapsed by default. This field only has an effect if `is_collapsible` is enabled.'),
(0000000032, 0000000047, '1', 'If enabled all items with child items are collapsible.'),
(0000000032, 0000000066, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of objects where each object has the following keys:\n - `id`: A unique identifier of the item\n - `title`: The name of the item.\n - `url`: An URL to where this item will link. This field is optional.\n - `children`: A list of objects, again with the keys `id` and `title` and the optional keys `url` and `children`\n\nFor example:\n```\n[{\n  \"id\": 1,\n  \"title\": \"Item1\",\n  \"children\": [{\n    \"id\": 2,\n    \"title\": \"Item1.1\"\n  }, {\n    \"id\": 3,\n    \"title\": \"Item1.2\",\n    \"children\": [{\n      \"id\": 4,\n      \"title\": \"Item1.2.1\"\n    }]\n  }]\n},\n{\n  \"id\": 5,\n  \"title\": \"Item2\",\n  \"children\": [{\n     \"id\": 5,\n     \"title\": \"Item2.1\"\n  }] \n},\n{\n  \"id\": 6,\n  \"title\": \"Item3\",\n  \"children\": [{\n     \"id\": 7,\n     \"title\": \"Item3.1\"\n  }]\n}]\n```\n.'),
(0000000032, 0000000077, NULL, 'If defined a small text input field is rendered on top of the list. This input field allows to search for any item within the list.'),
(0000000032, 0000000083, NULL, 'Defines which id is marked as active. This will also cause the corresponding root item to be expanded.'),
(0000000032, 0000000084, '0', NULL),
(0000000033, 0000000023, NULL, 'Use this field to add custom CSS classes to the root navigation page container.'),
(0000000033, 0000000029, '1', NULL),
(0000000033, 0000000031, NULL, NULL),
(0000000033, 0000000046, '1', NULL),
(0000000033, 0000000047, '0', NULL),
(0000000033, 0000000073, NULL, NULL),
(0000000033, 0000000074, NULL, NULL),
(0000000033, 0000000075, '1', NULL),
(0000000033, 0000000077, NULL, NULL),
(0000000033, 0000000089, NULL, 'Use this field to add custom CSS classes to the navigation menu of a navigation page.'),
(0000000033, 0000000104, '1', NULL),
(0000000034, 0000000066, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of objects where each object has the following keys:\n - `id`: A unique identifier of the item\n - `title`: The name of the item.\n - `url`: An URL to where this item will link. This field is optional.\n - `css`: A custom css class to be added to this item. This is useful for ignoring or blocking items when dragging.\n\nFor example:\n```\n[{\n  \"id\": 1,\n  \"title\": \"Item 1\"\n},{\n  \"id\": 2,\n  \"title\": \"Item 2\",\n  \"url\": \"#\",\n  \"css\": \"custom\"\n},{\n  \"id\": 3,\n  \"title\": \"Item 3\"\n}]\n```\n.'),
(0000000034, 0000000078, '0', 'If enabled the list is sortable. Note that this feature requires additional javascript code. This only has an effect if the field `is_editable` is enabled.'),
(0000000034, 0000000079, '0', 'If enabled the list can be changed (see the fields `is_sortable`, `url_delete`, `url_add`).'),
(0000000034, 0000000080, NULL, 'If set next to each item in the list a cross symbol will be rendered. Each symbol is a link with an URL as defined here. The string `:did` will be replaced with the id of the clicked item. For this field to have an effect, the field `is_editable` must be enabled.'),
(0000000034, 0000000081, NULL, 'This text will be used on the button to add new elements to the list. This field only has an effect if the field `is_editable` is enabled and the field `url_add` is defined.'),
(0000000034, 0000000082, NULL, 'If set, at the top of the list a button with the text as defined with the field `label_add` is rendered. This field defines the link URL of the button. For this field to have an effect, the field `is_editable` must be enabled.'),
(0000000035, 0000000004, NULL, 'The label on the submit button.'),
(0000000035, 0000000025, NULL, 'The description to be displayed on the page when a user wants to reset the password.'),
(0000000035, 0000000028, NULL, 'The bootstrap color of the submit button.'),
(0000000035, 0000000035, NULL, 'The success message to be shown when an email address was successfully stored in the database (if enabled) and the automatic emails were sent successfully.'),
(0000000035, 0000000055, NULL, 'The placeholder in the email input field.'),
(0000000035, 0000000110, NULL, 'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@link` will be replaced by the activation link the user needs to reset the password.'),
(0000000035, 0000000111, NULL, 'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.'),
(0000000035, 0000000114, '0', 'If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext'),
(0000000036, 0000000006, NULL, 'The child sections to be added to the `formUserInput` body. This can hold any style.'),
(0000000036, 0000000008, NULL, 'The label on the submit button.'),
(0000000036, 0000000028, 'primary', 'The visual appearance of the submit button as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).'),
(0000000036, 0000000035, NULL, 'The here defined text will be rendered upon successful submission of data. If the submission fails, an error message will indicate the reason.'),
(0000000036, 0000000057, NULL, 'A unique name to identify the form when exporting the collected data.'),
(0000000036, 0000000087, '0', 'This fiels allows to control how the data is saved in the database:\n - `disabled`: The submission of data will always overwrite prior submissions of the same user. This means that the user will be able to continously update the data that was submitted here. Any input field that is used within this form will always show the current value stored in the database (if nothing has been submitted as of yet, the input field will be empty or set to a default).\n - `enabled`: Each submission will create a new entry in the database. Once entered, an entry cannot be removed or modified. Any input field within this form will always be empty or set to a default value (nothing will be read from the database).'),
(0000000036, 0000000139, NULL, 'Search for the name of the anchor section to jump to after submitting the form. The ID of the section will be used as anchor. If this field is not set the section ID of the form itself will be used as anchor. This is useful if the form is placed within a collapsable card and the form anchor is hidden. In this case it makes sense to use the parent card as anchor here.'),
(0000000036, 0000000145, '', 'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: \n ```\n [\n	{\n		\"type\": \"static|dynamic\",\n		\"table\": \"table_name | #url_param1\",\n        \"retrieve\": \"first | last | all\",\n		\"fields\": [\n			{\n				\"field_name\": \"name | #url_param2\",\n				\"field_holder\": \"@field_1\",\n				\"not_found_text\": \"my field was not found\"				\n			}\n		]\n	}\n]\n```\nIf the page supports parameters, then the parameter can be accessed with `#` and the name of the paramer. Example `#url_param_name`. \n\nIn order to inlcude the retrieved data in the markdown field, include the `field_holder` that wa defined in the markdown text.\n\nWe can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)\n\n[More information](https://selfhelp.psy.unibe.ch/demo/style/454)'),
(0000000036, 0000000149, '0', 'Selecting submit and send email will add additional button to the form. If the user click on that button the data inut will ne send on his/her email.'),
(0000000036, 0000000150, '', 'The label on the submit and send button'),
(0000000036, 0000000151, '', 'The email subject that will be send. It could be dynamically configured. [More information](https://selfhelp.psy.unibe.ch/demo/style/454)'),
(0000000036, 0000000152, '', 'The email boy that will be send. It could be dynamically configured. [More information](https://selfhelp.psy.unibe.ch/demo/style/454)'),
(0000000038, 0000000008, NULL, 'If this field is set, a this text will be rendered above the radio elements.'),
(0000000038, 0000000056, '0', 'If enabled the form can only be submitted if a value is selected.'),
(0000000038, 0000000057, NULL, 'The name of the radio form field. This name must be unique within a form.'),
(0000000038, 0000000058, NULL, 'The preselected item of the radio elements.'),
(0000000038, 0000000066, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of radio objects where each object has the following keys:\n- `value`: the value to be submitted if this item is selected\n-`text`: the text rendered next to the radio button.\n\nAn Example\n```\n[{\n  \"value\":\"1\",\n  \"text\": \"Item1\"\n},\n{\n  \"value\":\"2\",\n  \"text\":\"Item2\"\n},\n{\n  \"value\":\"3\",\n  \"text\": \"Item3\"\n}]\n```'),
(0000000038, 0000000085, NULL, 'If enabled the radio items will be rendered in one line as opposed to one below the other.'),
(0000000039, 0000000012, NULL, 'The title of the modal form that pops up when the delete button is clicked.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.'),
(0000000039, 0000000013, NULL, 'The label of the remove button of the modal form.\n\nNote the following important points:\n- this field only has an effect if `is_log` is enabled.\n- if this field is not set, the remove button is not rendered.\n- entries that are removed with this button are only marked as removed but not deleted from the DB.'),
(0000000039, 0000000014, NULL, 'The content of the modal form that pops up when the delete button is clicked.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.'),
(0000000039, 0000000053, NULL, 'The name of the source form (i.e. the field `name` of the target form style).'),
(0000000039, 0000000087, '0', 'If *checked*, the style will render a table where each row represents all fields of the source form at the time instant of data submission.\n\nIf left *unchecked*, a table is rendered where each row represents one field of the source form.\n\nNote the following important points:\n- Check this only if the source form also has `is_log` checked.\n- The fields, `delete_title`, `label_date_time`, `label_delete`, and `delete_content` only have an effect if `is_log` is *checked*.'),
(0000000039, 0000000088, NULL, 'The column title of the timestamp column.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.'),
(0000000039, 0000000139, NULL, 'Search for the name of the anchor section to jump to after submitting the delete form. The ID of the section will be used as anchor. If this field is not set the page will jump to the top after submission.'),
(0000000040, 0000000006, NULL, 'The child sections to be added to the `div` body. This can hold any style.'),
(0000000041, 0000000001, NULL, 'The placeholder in the email input field.'),
(0000000041, 0000000002, NULL, 'The placeholder in the validation code input field.'),
(0000000041, 0000000005, NULL, 'This text is displayed in a danger-alert-box whenever the registration fails.'),
(0000000041, 0000000022, NULL, 'The text displayed in the register card header.'),
(0000000041, 0000000028, 'success', 'This allows to choose the colour scheme for the register form.'),
(0000000041, 0000000035, NULL, 'Upon successful registration the registration form is replaced with a `jumbotron` which hold this text.'),
(0000000041, 0000000044, NULL, 'Upon successful registration the registration form is replaced with a `jumbotron` which holds this text as a heading.'),
(0000000041, 0000000090, NULL, 'The text on the registration button.'),
(0000000041, 0000000140, '0', 'If checked any user can register without a registration code. The code will be automatically generated upon registration'),
(0000000041, 0000000143, '3', 'Select the default group in which evey new user is assigned.'),
(0000000042, 0000000006, NULL, 'The children to be rendered if the condition defined by the field `condition` resolves to true.'),
(0000000042, 0000000091, NULL, 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `\"@__form_name__#__from_field_name__\"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.'),
(0000000042, 0000000097, '0', 'If *checked*, debug messages will be rendered to the screen. These might help to understand the result of a condition evaluation. **Make sure that this field is *unchecked* once the page is productive**.'),
(0000000043, 0000000030, NULL, 'The alternative text to be displayed if the audio cannot be loaded.'),
(0000000043, 0000000071, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of source objects where each object has the following keys:\n - `source`: The source of the audio file. If it is an asset, simply use the full name of the asset.\n - `type`: The [type](!https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Containers) of the audio file.\n\nFor example:\n```\n[{\n  \"source\": \"audio_name.mp3\",\n  \"type\": \"audio/mpeg\"\n}, {\n  \"source\":\"audio_name.ogg\",\n  \"type\": \"audio/ogg\"\n}]\n```\n'),
(0000000044, 0000000071, NULL, 'This field expects a [JSON](!https://www.json.org/json-en.html) list of source objects where each object has the following keys:\n - `source`: The source of the image file. If it is an asset, simply use the full name of the asset.\n - `alt`: The alternative text to be displayed if the image connot be loaded.\n - `caption`: The image caption to be displayed at the bottom of the image.\n\nFor example:\n```[{\n  \"source\": \"slide1.svg\",\n  \"alt\": \"Image Description of Slide 1\",\n  \"caption\": \"Image Caption of Slide 1\"\n}, {\n  \"source\":\"slide2.svg\",\n  \"alt\": \"Image Description of Slide 2\",\n  \"caption\": \"Image Caption of Slide 2\"\n}, {\n  \"source\":\"slide3.svg\",\n  \"alt\": \"Image Description of Slide 3\",\n  \"caption\": \"Image Caption of Slide 3\"\n}]\n```\n'),
(0000000044, 0000000083, NULL, 'Define any unique name here if multiple carousel styles are used on the same page.'),
(0000000044, 0000000099, '1', 'If enabled the carusel is rendered with control arrows on either side of the image.'),
(0000000044, 0000000100, '0', 'If enabled the carousel is rendered with carousel position indicaters at the bottom of the image.'),
(0000000044, 0000000103, '0', 'If enabled images will fade from one to another instead of using the default sliding animation.'),
(0000000045, 0000000105, NULL, 'The JSON string to specify the (potentially) nested base styles.\r\n\r\nThere are a few things to note:\r\n - the key `_baseStyle` must be used to indicate that the assigned object is a *style object*\r\n - the *style object* must contain the key `_name` where the value must match a style name\r\n - the *style object* must contain the key `_fields` where the value is an object holding all required fields of the style (refer to the <a href=\"https://selfhelp.psy.unibe.ch/demo/styles\" target=\"_blank\">style documentation</a> for more information)'),
(0000000046, 0000000028, NULL, '.Use the type to change the appearance of individual progress bars'),
(0000000046, 0000000101, NULL, 'iIf set apply a stripe via CSS gradient over the progress bar’s background color.'),
(0000000046, 0000000102, NULL, 'If set display the progress in numbers ontop of the progress bar.'),
(0000000047, 0000000006, NULL, 'The child sections to be added to the `fromMermaid` body. Add only sections of style `input` here. If the field `name` of a child section matches the name of a node in the mermaid diagram, this node becomes editable. To edit, simply click on the node and a modal form is opened.'),
(0000000047, 0000000008, NULL, 'The label on the submit button in the edit window. This field only has an effect if a mermaid node is editable.'),
(0000000047, 0000000028, NULL, 'The visual appearance of the submit button in the edit window as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).'),
(0000000047, 0000000035, NULL, 'The here defined text will be rendered upon successful submission of data. If the submission fails, an error message will indicate the reason.'),
(0000000047, 0000000057, NULL, 'A unique name to identify the form when exporting the collected data.'),
(0000000047, 0000000107, NULL, 'Use <a href=\"https://mermaidjs.github.io/demos.html\" target=\"_blank\">mermaid markdown</a> syntax here.'),
(0000000048, 0000000008, NULL, 'The label on the submit button.'),
(0000000048, 0000000028, NULL, 'The bootstrap color of the submit button.'),
(0000000048, 0000000035, NULL, 'The success message to be shown when an email address was successfully stored in the database (if enabled) and the automatic emails were sent successfully.'),
(0000000048, 0000000055, NULL, 'The placeholder in the email input field.'),
(0000000048, 0000000108, NULL, 'A list of email addresses to be notified on submit with an email as defined in field `email_admins`. Use `json` syntax to specify the list of admins (e.g. `[\"__admin_1__\", ..., \"__admin_n__\"]`) where `__admin_*__` is the email address of an admin.'),
(0000000048, 0000000109, NULL, 'The email to be sent to the the list of admins defined in the field `admins`. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@email` will be replaced by the email address entered in the form.'),
(0000000048, 0000000110, NULL, 'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content.\n'),
(0000000048, 0000000111, NULL, 'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.'),
(0000000048, 0000000112, NULL, 'The list of attachments to the email to be sent to the the address that was entered into the form. Use `json` syntax to specify a list of assets (e.g. `[\"__asset_1__\", ..., \"__asset_n__\"]`) where `__asset_*__` is the name of an uploaded asset.'),
(0000000048, 0000000113, '0', 'If checked, the entered email address will be stored in the database.'),
(0000000048, 0000000114, '0', 'If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext'),
(0000000049, 0000000008, NULL, 'The label to be displayed above the autocomplete input field.'),
(0000000049, 0000000055, NULL, 'The placeholder text to be displayed in the autocomplete input field.'),
(0000000049, 0000000056, NULL, 'True if the field is required, false otherwise.'),
(0000000049, 0000000057, NULL, 'The name of the autocomplete input field.'),
(0000000049, 0000000058, NULL, 'The default value to be set in the hidden autocomplete value input field.'),
(0000000049, 0000000097, NULL, 'If set to true, debug information is shown in an alert box.'),
(0000000049, 0000000118, NULL, 'The name of the hidden autocomplete value input field.'),
(0000000049, 0000000119, NULL, 'The name of the class to be instantiated in the AJAX request.'),
(0000000049, 0000000120, NULL, 'The name of the method to be called on the class instance as defined in `callback_class`.'),
(0000000050, 0000000022, NULL, 'The title to be rendered on top of teh graph. This field is here purely for convenience as the title of a graph can also be defined in the field `layout`'),
(0000000050, 0000000122, NULL, 'Define the data traces to be rendered. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000050, 0000000123, NULL, 'Define the layout of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000050, 0000000124, NULL, 'Define the configuration of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000050, 0000000145, '', 'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: \n ```\n [\n	{\n		\"type\": \"static|dynamic\",\n		\"table\": \"table_name | #url_param1\",\n        \"retrieve\": \"first | last | all\",\n		\"fields\": [\n			{\n				\"field_name\": \"name | #url_param2\",\n				\"field_holder\": \"@field_1\",\n				\"not_found_text\": \"my field was not found\"				\n			}\n		]\n	}\n]\n```\nIf the page supports parameters, then the parameter can be accessed with `#` and the name of the paramer. Example `#url_param_name`. \n\nIn order to inlcude the retrieved data in the markdown field, include the `field_holder` that wa defined in the markdown text.\n\nWe can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)'),
(0000000051, 0000000022, NULL, 'The title of the Sankey diagram.'),
(0000000051, 0000000069, '1', 'The minimal required item count to form a link. In other words: what is the minimal required link width for a link to be displayed'),
(0000000051, 0000000121, NULL, 'The source of the data to be used to draw the Sankey diagram.'),
(0000000051, 0000000125, NULL, 'In order to create a Sankey diagram from a set of user input data two types of information are required:\r\n 1. the form field names defined here (think of it as the column headers of a table where each row holds the data of one subject)\r\n 2. the value types defined in `value_types` (the value entered by the subject).\r\n\r\nThe Sankey diagram consist of *nodes* and *links*. All possible combinations of form field names (1) and value types (2) define the nodes in a Sankey diagram. The links are computed by accumulating all values of the same type (2) when transitioning from one field name (1) to another.\r\n\r\nThis field expects an ordered list (`json` syntax) which specifies the form field names (1) to be used to generate the Sankey diagram. The order is important because two consecutive form field names (1) form a transition. Each list item is an object with the following fields:\r\n - `key`: the name of the field. When using static data this refers to a column name from the table specified in the field `data-source`. When using dynamic data this refers to a user input field name of the form specified in the field `data-source`.\r\n - `label`: A human-readable label which can be displayed on the diagram.\r\n\r\nAn Example\r\n```\r\n[\r\n  { \"key\": \"field1\", \"label\": \"Field 1\" },\r\n  { \"key\": \"field2\", \"label\": \"Field 2\" }\r\n]\r\n```'),
(0000000051, 0000000126, NULL, 'In order to create a Sankey diagram from a set of user input data two types of information are required:\n 1. the form field names defined in `form_field_names` (think of it as the column headers of a table where each row holds the data of one subject)\n 2. the value types defined here (the value entered by the subject).\n\nThe Sankey diagram consist of *nodes* and *links*. All possible combinations of form field names (1) and value types (2) define the nodes in a Sankey diagram. The links are computed by accumulating all values of the same type (2) when transitioning from one field name (1) to another.\n\nThis field expects an ordered list (`json` syntax) which specifies the value types (2) to be used to generate the Sankey diagram. The order is important because it may be used for node placement. Each list item is an object with the following fields:\n - `key`: the value of the value type.\n - `label`: A human-readable label which can be displayed on the diagram.\n - `color`: A hex string definig the color of the node of this type. Use a string of the following from `\"#FF0000\"`\n\nAn Example\n```\n[\n  { \"key\": 1, \"label\": \"Value Type 1\", \"color\": \"#FF0000\" },\n  { \"key\": 2, \"label\": \"Value Type 2\", \"color\": \"#00FF00\" }\n]\n```'),
(0000000051, 0000000127, NULL, 'Define the color of the links. There are four options:\n - `source`: use the color of the source node\n - `target`: use the color of the target node\n - a hex string of the from `#FF0000` to define the same color for all links\n - the empty string to use the default translucent gray'),
(0000000051, 0000000128, NULL, 'Define the alpha value of the color of the links. There are two options:\n - `sum`: compute the alpha value based on the width of the link\n - any number from 0 to 1: the same alpha value for all links as defined'),
(0000000051, 0000000129, '0', 'If checked, the labels defined in `value_types` are displayed next to a node with the corresponding type'),
(0000000051, 0000000130, '1', 'If checked, the label defined in `form_field_names` is displayed on top of a grouped node column. This field only has an effect if `is_grouped` is enabled.'),
(0000000051, 0000000131, '1', 'If checked, the nodes are positioned as follows:\n - each node with the same form field name is aligned vertically (same x coordinate)\n - within one column nodes are sorted by value types (by their indices as defined in `value_types`'),
(0000000051, 0000000132, '1', 'This option only takes effect when using **dynamic** data. If checked, only data from the current logged-in user is used. If unchecked, data form all users is used.'),
(0000000052, 0000000008, NULL, 'The name to be rendered on the filter button.'),
(0000000052, 0000000028, NULL, 'The visual apperance of the button as predefined by bootstrap.'),
(0000000052, 0000000057, NULL, 'The name of the table column or form field to filter on.'),
(0000000052, 0000000058, NULL, 'The value of the filter. All data sets of the data source (as specified by `data-source`) where the field (as specified by `name`) holds a value equal to the one indicated here will be selected.'),
(0000000052, 0000000121, NULL, 'The source of the data to be filtered.'),
(0000000053, 0000000028, NULL, 'The visual apperance of the buttons as predefined by bootstrap.'),
(0000000053, 0000000029, '0', 'If checked, the button group is streched to fill 100% of the available width. If unchecked, the button group is stretched to fit all text within each button but never more than available space.'),
(0000000053, 0000000057, NULL, 'The name of the table column or form field to filter on.'),
(0000000053, 0000000068, NULL, 'The names to be rendered on the filter buttons. Use a JSON array to specify all labels. The labels must correspond to the values as specified in `values`'),
(0000000053, 0000000121, NULL, 'The source of the data to be filtered.'),
(0000000053, 0000000134, NULL, 'The value of each filter button. All data sets of the data source (as specified by `data-source`) where the field (as specified by `name`) holds a value equal to the one indicated here will be selected. Use a JSON array to specify all values. The values must correspond to the labels as specified in `labels`.'),
(0000000053, 0000000135, '0', 'If checked, the button group is rendered as a vertical stack. If unchecked, the button group is rendered as a vertical list.'),
(0000000054, 0000000057, NULL, 'The name of the table column or form field to use to render a pie diagram.'),
(0000000054, 0000000121, NULL, 'The source of the data to be used to render a pie diagram.'),
(0000000054, 0000000123, NULL, 'Define the layout of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000054, 0000000124, NULL, 'Define the configuration of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000054, 0000000126, NULL, 'Defines the label and color for each distinct data value. Use a JSON array where each item has the following keys:\n - `key`: the data value to which the color and label will be assigned\n - `label`: to the label of the data value\n - `color`: the color of the data value (optional)\n\nAn example:\n```\n[\n  { \"key\": \"value_1\", \"label\", \"Label 1\", \"color\": \"#ff0000\" },\n  { \"key\": \"value_2\", \"label\", \"Label 2\", \"color\": \"#00ff00\" }\n}\n```'),
(0000000054, 0000000132, '1', 'This option only takes effect when using **dynamic** data. If checked, only data from the current logged-in user is used. If unchecked, data form all users is used.'),
(0000000054, 0000000136, '0', 'Use this to render a donut chart. Use a percentage from 0 to 100 where 0% means no hole and 100% a hole as big as the chart.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES
(0000000054, 0000000137, NULL, 'Allows to define the information to be rendered in the hover box. Use \"none\" to disable the hover box. Refer to the [Plotly.js documentation](!https://plotly.com/javascript/reference/#pie-hoverinfo) for more information.'),
(0000000054, 0000000138, NULL, 'Allows to define the information to be rendered on each pie slice. Use \"none\" to show no text. Refer to the [Plotly.js documentation](!https://plotly.com/javascript/reference/#pie-textinfo) for more information.'),
(0000000055, 0000000057, NULL, 'The name of the table column or form field to use to render a pie diagram.'),
(0000000055, 0000000121, NULL, 'The source of the data to be used to render a pie diagram.'),
(0000000055, 0000000123, NULL, 'Define the layout of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000055, 0000000124, NULL, 'Define the configuration of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information'),
(0000000055, 0000000126, NULL, 'Defines the label and color for each distinct data value. Use a JSON array where each item has the following keys:\n - `key`: the data value to which the color and label will be assigned\n - `label`: to the label of the data value\n - `color`: the color of the data value (optional)\n\nAn example:\n```\n[\n  { \"key\": \"value_1\", \"label\", \"Label 1\", \"color\": \"#ff0000\" },\n  { \"key\": \"value_2\", \"label\", \"Label 2\", \"color\": \"#00ff00\" }\n}\n```'),
(0000000055, 0000000132, '1', 'This option only takes effect when using **dynamic** data. If checked, only data from the current logged-in user is used. If unchecked, data form all users is used.'),
(0000000056, 0000000126, NULL, 'Defines the label and color for each distinct data value. Use a JSON array where each item has the following keys:\n - `key`: the data value to which the color and label will be assigned\n - `label`: to the label of the data value\n - `color`: the color of the data value\n\nAn example:\n```\n[\n  { \"key\": \"value_1\", \"label\", \"Label 1\", \"color\": \"#ff0000\" },\n  { \"key\": \"value_2\", \"label\", \"Label 2\", \"color\": \"#00ff00\" }\n}\n```'),
(0000000057, 0000000066, NULL, 'JSON structure for the navigation bar'),
(0000000058, 0000000144, '', 'Select a survey. TIP: A Survey should be assigned to a project (added as a action)'),
(0000000059, 0000000008, '', 'Label for the button'),
(0000000059, 0000000055, '', 'Placeholder for the input field'),
(0000000059, 0000000147, '', 'Add prefix to the search text'),
(0000000059, 0000000148, '', 'Add suffix to the search text');

-- --------------------------------------------------------

--
-- Table structure for table `styleType`
--

DROP TABLE IF EXISTS `styleType`;
CREATE TABLE IF NOT EXISTS `styleType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styleType`
--

INSERT INTO `styleType` (`id`, `name`) VALUES
(0000000001, 'view'),
(0000000002, 'component'),
(0000000003, 'navigation');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `transaction_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_transactionTypes` int(10) UNSIGNED DEFAULT NULL,
  `id_transactionBy` int(10) UNSIGNED DEFAULT NULL,
  `id_users` int(10) UNSIGNED DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `id_table_name` int(10) UNSIGNED DEFAULT NULL,
  `transaction_log` text,
  PRIMARY KEY (`id`),
  KEY `transactions_fk_id_transactionTypes` (`id_transactionTypes`),
  KEY `transactions_fk_id_transactionBy` (`id_transactionBy`),
  KEY `transactions_fk_id_users` (`id_users`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_time`, `id_transactionTypes`, `id_transactionBy`, `id_users`, `table_name`, `id_table_name`, `transaction_log`) VALUES
(0000000001, '2020-10-05 08:45:52', 35, 42, 2, 'pages', 53, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/qualtrics\\/action\\/3\\/update\\/6\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":67,\"sid\":67,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/qualtrics\\/action\\/3\\/update\\/6\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"chat_room\":null}}'),
(0000000002, '2020-10-05 08:45:55', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":67,\"sid\":67,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"chat_room\":null}}'),
(0000000003, '2020-10-05 08:46:02', 35, 42, 2, 'pages', 49, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/qualtrics\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":67,\"sid\":67,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/qualtrics\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"chat_room\":null}}'),
(0000000004, '2020-10-05 08:46:04', 35, 42, 2, 'pages', 52, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/qualtrics\\/survey\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":67,\"sid\":67,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/qualtrics\\/survey\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"chat_room\":null}}'),
(0000000005, '2020-10-05 08:46:06', 35, 42, 2, 'pages', 52, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/qualtrics\\/survey\\/insert\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":67,\"sid\":67,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/qualtrics\\/survey\\/insert\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"chat_room\":null}}'),
(0000000006, '2020-10-05 08:46:10', 35, 42, 2, 'pages', 52, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/qualtrics\\/survey\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":67,\"sid\":67,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/qualtrics\\/survey\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"chat_room\":null}}'),
(0000000007, '2020-11-06 09:46:00', 35, 42, 2, 'pages', 14, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/user\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/user\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/179\\/1263\",\"chat_room\":11}}'),
(0000000008, '2020-11-06 09:46:08', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/179\\/1263\",\"chat_room\":11}}'),
(0000000009, '2020-11-06 09:46:13', 35, 42, 2, 'pages', 45, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/data\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/data\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/user\",\"chat_room\":11}}'),
(0000000010, '2020-11-06 09:46:22', 35, 42, 2, 'pages', 45, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/data\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/data\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\",\"chat_room\":11}}'),
(0000000011, '2020-11-06 09:46:31', 35, 42, 2, 'pages', 49, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/qualtrics\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/qualtrics\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\",\"chat_room\":11}}'),
(0000000012, '2020-11-06 09:46:35', 35, 42, 2, 'pages', 50, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/mailQueue\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/mailQueue\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/data\",\"chat_room\":11}}'),
(0000000013, '2020-11-06 09:46:39', 35, 42, 2, 'pages', 55, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/mailQueue\\/composeEmail\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/mailQueue\\/composeEmail\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/qualtrics\",\"chat_room\":11}}'),
(0000000014, '2020-11-06 09:46:57', 35, 42, 2, 'pages', 46, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_preferences\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":179,\"sid\":1263,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_preferences\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/mailQueue\",\"chat_room\":11}}'),
(0000000015, '2020-11-19 13:37:05', 35, 42, 2, 'pages', 2, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":175,\"sid\":1255,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/175\\/1255\",\"chat_room\":11}}'),
(0000000016, '2020-11-19 13:37:09', 35, 42, 2, 'pages', 31, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/impressum\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":175,\"sid\":1255,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/impressum\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/175\\/1255\",\"chat_room\":11}}'),
(0000000017, '2020-11-19 13:37:14', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":175,\"sid\":1255,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/\",\"chat_room\":11}}'),
(0000000018, '2020-11-19 13:37:17', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/5\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":175,\"sid\":1255,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/5\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/impressum\",\"chat_room\":11}}'),
(0000000019, '2020-11-19 13:37:22', 35, 42, 2, 'pages', 6, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/[i:id]\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":5,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/[i:id]\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\",\"chat_room\":11}}'),
(0000000020, '2020-11-19 13:37:27', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/5\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":5,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/5\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/5\",\"chat_room\":11}}'),
(0000000021, '2020-11-19 13:37:35', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/3\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":5,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/3\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\",\"chat_room\":11}}'),
(0000000022, '2020-11-19 13:37:43', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/1\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":5,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/1\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/5\",\"chat_room\":11}}'),
(0000000023, '2020-11-19 13:38:24', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":1,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/3\",\"chat_room\":11}}'),
(0000000024, '2020-11-19 13:38:28', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/1\",\"chat_room\":11}}'),
(0000000025, '2020-11-19 13:38:32', 35, 42, 2, 'pages', 58, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\",\"chat_room\":11}}'),
(0000000026, '2020-11-19 13:39:58', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"chat_room\":11}}'),
(0000000027, '2020-11-19 13:40:01', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/3\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/3\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"chat_room\":11}}'),
(0000000028, '2020-11-19 13:40:02', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/5\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/5\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\",\"chat_room\":11}}'),
(0000000029, '2020-11-19 13:40:05', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":5,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/3\",\"chat_room\":11}}'),
(0000000030, '2020-11-19 13:40:08', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/5\",\"chat_room\":11}}'),
(0000000031, '2020-11-19 13:40:28', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\",\"chat_room\":11}}'),
(0000000032, '2020-11-19 13:41:52', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\",\"chat_room\":11}}'),
(0000000033, '2020-11-19 13:41:58', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\",\"chat_room\":11}}'),
(0000000034, '2020-11-19 13:42:03', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":null,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"chat_room\":11}}'),
(0000000035, '2020-11-19 13:42:05', 35, 42, 2, 'pages', 58, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\",\"chat_room\":11}}'),
(0000000036, '2020-11-19 13:42:12', 35, 42, 2, 'pages', 10, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":2,\"sid\":19,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/2\\/19\",\"chat_room\":11}}'),
(0000000037, '2020-11-19 13:44:01', 35, 42, 2, 'pages', 58, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/1635\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":183,\"sid\":1635,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/1635\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/183\\/1635\",\"chat_room\":11}}'),
(0000000038, '2020-11-19 13:44:21', 35, 42, 2, 'pages', 6, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/fsdf\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":183,\"sid\":1635,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/fsdf\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/183\\/1635\",\"chat_room\":11}}'),
(0000000039, '2020-11-19 13:44:25', 35, 42, 2, 'pages', 58, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/1\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":183,\"sid\":1635,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/1\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/183\\/1635\",\"chat_room\":11}}'),
(0000000040, '2020-11-19 13:44:30', 35, 42, 2, 'pages', 58, '{\"verbal_log\":\"Transaction type: `select` from table: `pages` triggered by_user\",\"url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/11111\",\"session\":{\"gender\":\"male\",\"user_gender\":\"male\",\"cms_gender\":\"male\",\"language\":\"de-CH\",\"user_language\":\"de-CH\",\"cms_language\":\"de-CH\",\"cms_edit_url\":{\"pid\":183,\"sid\":1635,\"ssid\":null,\"did\":null,\"mode\":\"update\",\"type\":\"prop\"},\"active_section_id\":null,\"project\":\"Projekt Name\",\"target_url\":\"\\/selfhelp\\/admin\\/cms_export\\/section\\/11111\",\"logged_in\":true,\"id_user\":\"0000000002\",\"requests\":[],\"last_user_page\":\"http:\\/\\/localhost\\/selfhelp\\/admin\\/cms\\/183\\/1635\",\"chat_room\":11}}');

-- --------------------------------------------------------

--
-- Table structure for table `uploadCells`
--

DROP TABLE IF EXISTS `uploadCells`;
CREATE TABLE IF NOT EXISTS `uploadCells` (
  `id_uploadRows` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadCols` int(10) UNSIGNED ZEROFILL NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id_uploadRows`,`id_uploadCols`),
  KEY `id_uploadRows` (`id_uploadRows`),
  KEY `id_uploadCols` (`id_uploadCols`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadCols`
--

DROP TABLE IF EXISTS `uploadCols`;
CREATE TABLE IF NOT EXISTS `uploadCols` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_uploadTables` (`id_uploadTables`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadRows`
--

DROP TABLE IF EXISTS `uploadRows`;
CREATE TABLE IF NOT EXISTS `uploadRows` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_uploadTables` (`id_uploadTables`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadTables`
--

DROP TABLE IF EXISTS `uploadTables`;
CREATE TABLE IF NOT EXISTS `uploadTables` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_uploadTables_name_timestamp` (`name`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_genders` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `id_status` int(10) UNSIGNED ZEROFILL DEFAULT '0000000001',
  `intern` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(32) DEFAULT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `is_reminded` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` date DEFAULT NULL,
  `last_url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_genders` (`id_genders`),
  KEY `id_languages` (`id_languages`),
  KEY `id_status` (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `password`, `id_genders`, `blocked`, `id_status`, `intern`, `token`, `id_languages`, `is_reminded`, `last_login`, `last_url`) VALUES
(0000000001, 'guest', '', NULL, NULL, 0, NULL, 1, NULL, NULL, 0, NULL, NULL),
(0000000002, 'admin', 'admin', '$2y$10$lqb/Eieowq8lWTUxVrb1MOHrZ1ZDvbnU4RNvWxqP5pa8/QOdwFB8e', NULL, 0, 0000000003, 0, NULL, NULL, 1, '2020-04-29', NULL),
(0000000003, 'tpf', 'TPF', '$2y$10$VxLANpP09THlDIDDfvL7PurilxKZ8vU8WzdGdfCYkdeBgy7hUkiUu', 0000000001, 0, 0000000003, 0, NULL, NULL, 0, NULL, NULL),
(0000000004, 'sysadmin', 'sysadmin', '$2y$10$H5MhmUF3cLLMNayuIQ4g.OXikV528bDOkConwtVBjdpj4rqrUtAXu', 0000000001, 0, 0000000003, 0, NULL, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `userStatus`
--

DROP TABLE IF EXISTS `userStatus`;
CREATE TABLE IF NOT EXISTS `userStatus` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `userStatus`
--

INSERT INTO `userStatus` (`id`, `name`, `description`) VALUES
(0000000001, 'interested', 'This user has shown interest in the platform but has not yet met the preconditions to be invited.'),
(0000000002, 'invited', 'This user was invited to join the platform but has not yet validated the email address.'),
(0000000003, 'active', 'This user can log in and visit all accessible pages.'),
(0000000005, 'auto_created', 'This user was auto created. The user has only code and cannot login. If the real user register later with the code the user will be activated to normal user.');

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE IF NOT EXISTS `users_groups` (
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_groups` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_users`,`id_groups`),
  KEY `id_users` (`id_users`),
  KEY `id_groups` (`id_groups`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`id_users`, `id_groups`) VALUES
(0000000002, 0000000001),
(0000000003, 0000000001),
(0000000004, 0000000001);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
CREATE TABLE IF NOT EXISTS `user_activity` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `url` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_type` (`id_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_activity`
--

INSERT INTO `user_activity` (`id`, `id_users`, `url`, `timestamp`, `id_type`) VALUES
(0000000001, 0000000002, '/selfhelp/admin/export/user_input_form/all/55', '2019-11-28 16:49:46', 0000000002),
(0000000002, 0000000002, '/selfhelp/admin/export/user_input_form/all/55', '2020-01-24 14:32:37', 0000000002),
(0000000003, 0000000002, '/selfhelp/admin/data', '2020-11-06 10:46:14', 0000000002),
(0000000004, 0000000002, '/selfhelp/admin/data', '2020-11-06 10:46:22', 0000000002);

-- --------------------------------------------------------

--
-- Table structure for table `user_input`
--

DROP TABLE IF EXISTS `user_input`;
CREATE TABLE IF NOT EXISTS `user_input` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_section_form` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_user_input_record` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `value` longtext NOT NULL,
  `edit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `removed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_sections` (`id_sections`),
  KEY `id_section_form` (`id_section_form`),
  KEY `id_user_input_record` (`id_user_input_record`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_input`
--

INSERT INTO `user_input` (`id`, `id_users`, `id_sections`, `id_section_form`, `id_user_input_record`, `value`, `edit_time`, `removed`) VALUES
(0000000001, 0000000003, 0000000056, 0000000055, NULL, '', '2019-11-19 11:21:28', 0),
(0000000002, 0000000003, 0000000057, 0000000055, NULL, '', '2019-11-19 11:21:28', 0),
(0000000003, 0000000004, 0000000056, 0000000055, NULL, '', '2019-11-19 11:21:28', 0),
(0000000004, 0000000004, 0000000057, 0000000055, NULL, '', '2019-11-19 11:21:28', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_input_record`
--

DROP TABLE IF EXISTS `user_input_record`;
CREATE TABLE IF NOT EXISTS `user_input_record` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `validation_codes`
--

DROP TABLE IF EXISTS `validation_codes`;
CREATE TABLE IF NOT EXISTS `validation_codes` (
  `code` varchar(16) NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `consumed` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `id_users` (`id_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
CREATE TABLE IF NOT EXISTS `version` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `version` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `version`
--

INSERT INTO `version` (`id`, `version`) VALUES
(0000000001, 'v3.4.0');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acl_groups`
--
ALTER TABLE `acl_groups`
  ADD CONSTRAINT `fk_acl_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_acl_groups_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `acl_users`
--
ALTER TABLE `acl_users`
  ADD CONSTRAINT `acl_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `acl_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `fk_chat_id_rcv_group` FOREIGN KEY (`id_rcv_group`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_id_rcv_user` FOREIGN KEY (`id_rcv`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_id_send` FOREIGN KEY (`id_snd`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chatRecipiants`
--
ALTER TABLE `chatRecipiants`
  ADD CONSTRAINT `chatRecipiants_fk_id_chat` FOREIGN KEY (`id_chat`) REFERENCES `chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chatRecipiants_fk_id_room_users` FOREIGN KEY (`id_room_users`) REFERENCES `chatRoom_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chatRecipiants_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chatRoom_users`
--
ALTER TABLE `chatRoom_users`
  ADD CONSTRAINT `chatRoom_users_fk_id_chatRoom` FOREIGN KEY (`id_chatRoom`) REFERENCES `chatRoom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chatRoom_users_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cmsPreferences`
--
ALTER TABLE `cmsPreferences`
  ADD CONSTRAINT `fk_cmsPreferences_language` FOREIGN KEY (`default_language_id`) REFERENCES `languages` (`id`);

--
-- Constraints for table `codes_groups`
--
ALTER TABLE `codes_groups`
  ADD CONSTRAINT `fk_codes` FOREIGN KEY (`code`) REFERENCES `validation_codes` (`code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fields`
--
ALTER TABLE `fields`
  ADD CONSTRAINT `fields_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `fieldType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mailAttachments`
--
ALTER TABLE `mailAttachments`
  ADD CONSTRAINT `mailAttachments_fk_id_mailQueue` FOREIGN KEY (`id_mailQueue`) REFERENCES `mailQueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mailQueue`
--
ALTER TABLE `mailQueue`
  ADD CONSTRAINT `mailQueue_fk_id_mailQueueStatus` FOREIGN KEY (`id_mailQueueStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `modules_pages`
--
ALTER TABLE `modules_pages`
  ADD CONSTRAINT `modules_pages_fk_id_modules` FOREIGN KEY (`id_modules`) REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `modules_pages_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_fk_id_actions` FOREIGN KEY (`id_actions`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fk_id_navigation_section` FOREIGN KEY (`id_navigation_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `pageType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fk_parent` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pages_fields`
--
ALTER TABLE `pages_fields`
  ADD CONSTRAINT `fk_page_fields_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_page_fields_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pages_fields_translation`
--
ALTER TABLE `pages_fields_translation`
  ADD CONSTRAINT `pages_fields_translation_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fields_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fields_translation_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pages_sections`
--
ALTER TABLE `pages_sections`
  ADD CONSTRAINT `pages_sections_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_sections_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualtricsActions`
--
ALTER TABLE `qualtricsActions`
  ADD CONSTRAINT `qualtricsActions_fk_id_lookups_qualtricsProjectActionTriggerType` FOREIGN KEY (`id_qualtricsProjectActionTriggerTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsActions_fk_id_qualtricsActionScheduleTypes` FOREIGN KEY (`id_qualtricsActionScheduleTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsActions_fk_id_qualtricsProjects` FOREIGN KEY (`id_qualtricsProjects`) REFERENCES `qualtricsProjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsActions_fk_id_qualtricsSurveys` FOREIGN KEY (`id_qualtricsSurveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsActions_fk_id_qualtricsSurveys_reminder` FOREIGN KEY (`id_qualtricsSurveys_reminder`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualtricsActions_functions`
--
ALTER TABLE `qualtricsActions_functions`
  ADD CONSTRAINT `qualtricsActions_functions_fk_id_lookups` FOREIGN KEY (`id_lookups`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsActions_functions_fk_id_qualtricsActions` FOREIGN KEY (`id_qualtricsActions`) REFERENCES `qualtricsActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualtricsActions_groups`
--
ALTER TABLE `qualtricsActions_groups`
  ADD CONSTRAINT `qualtricsActions_groups_fk_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsActions_groups_fk_id_qualtricsActions` FOREIGN KEY (`id_qualtricsActions`) REFERENCES `qualtricsActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualtricsReminders`
--
ALTER TABLE `qualtricsReminders`
  ADD CONSTRAINT `qualtricsReminders_fk_id_mailQueue` FOREIGN KEY (`id_mailQueue`) REFERENCES `mailQueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsReminders_fk_id_qualtricsSurveys` FOREIGN KEY (`id_qualtricsSurveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qualtricsReminders_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualtricsSurveys`
--
ALTER TABLE `qualtricsSurveys`
  ADD CONSTRAINT `qualtricsSurveys_fk_id_qualtricsSurveyTypes` FOREIGN KEY (`id_qualtricsSurveyTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualtricsSurveysResponses`
--
ALTER TABLE `qualtricsSurveysResponses`
  ADD CONSTRAINT `qSurveysResponses_fk_id_qualtricsProjectActionTriggerTypes` FOREIGN KEY (`id_qualtricsProjectActionTriggerTypes`) REFERENCES `lookups` (`id`),
  ADD CONSTRAINT `qSurveysResponses_fk_id_surveys` FOREIGN KEY (`id_surveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qSurveysResponses_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_fk_owner` FOREIGN KEY (`owner`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections_fields_translation`
--
ALTER TABLE `sections_fields_translation`
  ADD CONSTRAINT `sections_fields_translation_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_fields_translation_fk_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_fields_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_fields_translation_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections_hierarchy`
--
ALTER TABLE `sections_hierarchy`
  ADD CONSTRAINT `sections_hierarchy_fk_child` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_hierarchy_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections_navigation`
--
ALTER TABLE `sections_navigation`
  ADD CONSTRAINT `sections_navigation_fk_child` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_navigation_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_navigation_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `styles`
--
ALTER TABLE `styles`
  ADD CONSTRAINT `styles_fk_id_group` FOREIGN KEY (`id_group`) REFERENCES `styleGroup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `styleType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `styles_fields`
--
ALTER TABLE `styles_fields`
  ADD CONSTRAINT `styles_fields_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_fields_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_fk_id_transactionBy` FOREIGN KEY (`id_transactionBy`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_fk_id_transactionTypes` FOREIGN KEY (`id_transactionTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadCells`
--
ALTER TABLE `uploadCells`
  ADD CONSTRAINT `uploadCells_fk_id_uploadCols` FOREIGN KEY (`id_uploadCols`) REFERENCES `uploadCols` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uploadCells_fk_id_uploadRows` FOREIGN KEY (`id_uploadRows`) REFERENCES `uploadRows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadCols`
--
ALTER TABLE `uploadCols`
  ADD CONSTRAINT `uploadCols_fk_id_uploadTables` FOREIGN KEY (`id_uploadTables`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadRows`
--
ALTER TABLE `uploadRows`
  ADD CONSTRAINT `uploadRows_fk_id_uploadTables` FOREIGN KEY (`id_uploadTables`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_id_status` FOREIGN KEY (`id_status`) REFERENCES `userStatus` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD CONSTRAINT `fk_users_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_groups_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `fk_user_activity_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `activityType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_activity_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_input`
--
ALTER TABLE `user_input`
  ADD CONSTRAINT `user_input_fk_id_section_form` FOREIGN KEY (`id_section_form`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_input_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_input_fk_id_user_input_record` FOREIGN KEY (`id_user_input_record`) REFERENCES `user_input_record` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_input_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `validation_codes`
--
ALTER TABLE `validation_codes`
  ADD CONSTRAINT `validation_codes_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
