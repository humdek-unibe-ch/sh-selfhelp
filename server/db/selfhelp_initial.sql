-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 27, 2018 at 10:27 AM
-- Server version: 5.7.23-0ubuntu0.18.04.1
-- PHP Version: 7.2.10-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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

CREATE TABLE `acl_groups` (
  `id_groups` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0'
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
(0000000001, 0000000029, 1, 1, 1, 1),
(0000000001, 0000000030, 1, 1, 1, 1),
(0000000001, 0000000031, 1, 1, 1, 1),
(0000000001, 0000000032, 1, 1, 1, 1),
(0000000001, 0000000033, 1, 1, 1, 1),
(0000000001, 0000000034, 1, 1, 1, 1),
(0000000002, 0000000001, 1, 0, 0, 0),
(0000000002, 0000000002, 1, 0, 0, 0),
(0000000002, 0000000003, 1, 0, 0, 0),
(0000000002, 0000000004, 1, 0, 0, 0),
(0000000002, 0000000005, 1, 0, 0, 0),
(0000000002, 0000000006, 1, 0, 0, 0),
(0000000002, 0000000007, 1, 0, 0, 0),
(0000000002, 0000000008, 1, 0, 0, 0),
(0000000002, 0000000009, 1, 0, 0, 0),
(0000000002, 0000000010, 1, 0, 0, 0),
(0000000002, 0000000011, 1, 1, 0, 0),
(0000000002, 0000000012, 1, 0, 1, 0),
(0000000002, 0000000013, 1, 0, 0, 1),
(0000000002, 0000000014, 1, 0, 0, 0),
(0000000002, 0000000015, 1, 1, 0, 0),
(0000000002, 0000000016, 1, 0, 1, 0),
(0000000002, 0000000017, 0, 0, 0, 0),
(0000000002, 0000000018, 1, 0, 0, 0),
(0000000002, 0000000019, 1, 1, 0, 0),
(0000000002, 0000000020, 1, 0, 1, 0),
(0000000002, 0000000021, 0, 0, 0, 0),
(0000000002, 0000000022, 1, 0, 0, 0),
(0000000002, 0000000023, 1, 0, 0, 0),
(0000000002, 0000000024, 1, 0, 0, 0),
(0000000002, 0000000025, 1, 1, 0, 0),
(0000000002, 0000000026, 1, 0, 1, 0),
(0000000002, 0000000027, 1, 0, 0, 1),
(0000000002, 0000000028, 1, 0, 0, 0),
(0000000002, 0000000029, 1, 0, 0, 0),
(0000000002, 0000000030, 1, 0, 0, 0),
(0000000002, 0000000031, 1, 0, 0, 0),
(0000000002, 0000000032, 1, 0, 0, 0),
(0000000002, 0000000033, 1, 0, 0, 0),
(0000000002, 0000000034, 1, 0, 0, 0),
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
(0000000003, 0000000029, 1, 0, 0, 0),
(0000000003, 0000000030, 1, 0, 0, 0),
(0000000003, 0000000031, 1, 0, 0, 0),
(0000000003, 0000000032, 1, 0, 0, 0),
(0000000003, 0000000033, 1, 0, 0, 0),
(0000000003, 0000000034, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `acl_users`
--

CREATE TABLE `acl_users` (
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_users`
--

INSERT INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
(0000000001, 0000000001, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`id`, `name`) VALUES
(0000000001, 'custom'),
(0000000002, 'component'),
(0000000003, 'sections');

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_snd` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_rcv` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `content` longtext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000002',
  `display` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(0000000022, 'title', 0000000001, 1),
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
(0000000053, 'source', 0000000001, 0),
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
(0000000071, 'sources', 0000000008, 0),
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
(0000000084, 'id_active', 0000000005, 0);

-- --------------------------------------------------------

--
-- Table structure for table `fieldType`
--

CREATE TABLE `fieldType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(0000000010, 'type-input', 4);

-- --------------------------------------------------------

--
-- Table structure for table `genders`
--

CREATE TABLE `genders` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `groups` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(0000000001, 'admin', 'full access'),
(0000000002, 'experimenter', 'access to home, legal, profile, experiment, manage experiment'),
(0000000003, 'subject', 'access to home, legal, profile, experiment');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `locale` varchar(5) NOT NULL COMMENT '"e.g en-GB, de-CH"',
  `language` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `locale`, `language`) VALUES
(0000000001, 'all', 'Independent'),
(0000000002, 'de-CH', 'Deutsch (Schweiz)'),
(0000000003, 'en-GB', 'English (GB)');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(100) DEFAULT NULL COMMENT 'pipe seperated list of HTTP Methods (GET|POST)',
  `id_actions` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `id_navigation_section` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `parent` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `nav_position` int(11) DEFAULT NULL,
  `footer_position` int(11) DEFAULT NULL,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL,
  `user_input` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `nav_position`, `footer_position`, `id_type`, `user_input`) VALUES
(0000000001, 'login', '/login', 'GET|POST', 0000000001, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000002, 'home', '/', 'GET', 0000000001, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000003, 'profile-link', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000004, 'profile', '/profile', 'GET|POST', 0000000003, NULL, 0000000003, 10, NULL, 0000000002, 0),
(0000000005, 'logout', '/login', 'GET', NULL, NULL, 0000000003, 20, NULL, 0000000002, 0),
(0000000006, 'missing', NULL, NULL, 0000000003, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000007, 'no_access', NULL, NULL, 0000000003, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000008, 'no_access_guest', NULL, NULL, 0000000003, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000009, 'admin-link', NULL, NULL, NULL, NULL, NULL, 1000, NULL, 0000000001, 0),
(0000000010, 'cmsSelect', '/admin/cms/[i:pid]?/[i:sid]?/[i:ssid]?', 'GET', 0000000002, NULL, 0000000009, 10, NULL, 0000000001, 0),
(0000000011, 'cmsInsert', '/admin/cms_insert/[i:pid]?', 'GET|POST|PUT', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000012, 'cmsUpdate', '/admin/cms_update/[i:pid]?/[i:sid]?/[i:ssid]?/[update|insert|delete:mode]/[v:type]/[i:did]?', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000013, 'cmsDelete', '/admin/cms_delete/[i:pid]/[i:sid]?/[i:ssid]?', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000014, 'userSelect', '/admin/user/[i:uid]?', 'GET', 0000000002, NULL, 0000000009, 20, NULL, 0000000001, 0),
(0000000015, 'userInsert', '/admin/user_insert', 'GET|POST|PUT', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000016, 'userUpdate', '/admin/user_update/[i:uid]/[v:mode]/[i:did]?', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000017, 'userDelete', '/admin/user_delete/[i:uid]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000018, 'groupSelect', '/admin/group/[i:gid]?', 'GET', 0000000002, NULL, 0000000009, 30, NULL, 0000000001, 0),
(0000000019, 'groupInsert', '/admin/group_insert', 'GET|POST|PUT', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000020, 'groupUpdate', '/admin/group_update/[i:gid]', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000021, 'groupDelete', '/admin/group_delete/[i:gid]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000022, 'export', '/admin/export', 'GET', 0000000002, NULL, 0000000009, 40, NULL, 0000000001, 0),
(0000000023, 'exportData', '/admin/export/[user_input|user_activity:selector]', 'GET', 0000000001, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000024, 'assetSelect', '/admin/asset', 'GET', 0000000002, NULL, 0000000009, 15, NULL, 0000000001, 0),
(0000000025, 'assetInsert', '/admin/asset_insert', 'GET|POST|PUT', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000026, 'assetUpdate', '/admin/asset_update/[v:file]', 'GET|POST|PATCH', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000027, 'assetDelete', '/admin/asset_delete/[v:file]', 'GET|POST|DELETE', 0000000002, NULL, 0000000009, NULL, NULL, 0000000001, 0),
(0000000028, 'request', '/request/[a:request]', 'POST', 0000000001, NULL, NULL, NULL, NULL, 0000000001, 0),
(0000000029, 'contact', '/kontakt/[i:uid]?', 'GET|POST', 0000000003, NULL, NULL, 30, NULL, 0000000002, 0),
(0000000030, 'agb', '/agb', 'GET', 0000000003, NULL, NULL, NULL, 300, 0000000002, 0),
(0000000031, 'impressum', '/impressum', 'GET', 0000000003, NULL, NULL, NULL, 100, 0000000002, 0),
(0000000032, 'disclaimer', '/disclaimer', 'GET', 0000000003, NULL, NULL, NULL, 200, 0000000002, 0),
(0000000033, 'validate', '/validate/[i:uid]/[a:token]', 'GET|POST', 0000000003, NULL, NULL, NULL, NULL, 0000000002, 0),
(0000000034, 'user_input_success 	', NULL, NULL, 0000000003, NULL, NULL, NULL, NULL, 0000000002, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pages_fields_translation`
--

CREATE TABLE `pages_fields_translation` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages_fields_translation`
--

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
(0000000001, 0000000008, 0000000002, 'Login'),
(0000000001, 0000000008, 0000000003, 'Login'),
(0000000002, 0000000008, 0000000002, 'Projekt Name'),
(0000000002, 0000000008, 0000000003, 'Project Name'),
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
(0000000029, 0000000008, 0000000002, 'Kontakt'),
(0000000029, 0000000008, 0000000003, 'Contact'),
(0000000030, 0000000008, 0000000002, 'AGB'),
(0000000030, 0000000008, 0000000003, 'GTC'),
(0000000031, 0000000008, 0000000002, 'Impressum'),
(0000000031, 0000000008, 0000000003, 'Legal Notice'),
(0000000032, 0000000008, 0000000002, 'Disclaimer'),
(0000000032, 0000000008, 0000000003, 'Disclaimer'),
(0000000033, 0000000008, 0000000002, 'Benutzer Validierung'),
(0000000033, 0000000008, 0000000003, 'User Validation'),
(0000000034, 0000000008, 0000000002, 'Benutzer Daten'),
(0000000034, 0000000008, 0000000003, 'User Data');

-- --------------------------------------------------------

--
-- Table structure for table `pages_sections`
--

CREATE TABLE `pages_sections` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages_sections`
--

INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES
(0000000001, 0000000001, NULL),
(0000000002, 0000000019, 0),
(0000000004, 0000000002, NULL),
(0000000006, 0000000003, NULL),
(0000000007, 0000000009, 0),
(0000000008, 0000000012, 0),
(0000000029, 0000000017, 0),
(0000000030, 0000000016, 0),
(0000000031, 0000000020, 0),
(0000000032, 0000000018, 0),
(0000000033, 0000000026, NULL),
(0000000034, 0000000021, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pageType`
--

CREATE TABLE `pageType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pageType`
--

INSERT INTO `pageType` (`id`, `name`) VALUES
(0000000001, 'intern'),
(0000000002, 'core'),
(0000000003, 'experiment');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `owner` int(10) UNSIGNED ZEROFILL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(0000000021, 0000000003, 'user_input_success-container', NULL),
(0000000022, 0000000004, 'user_input_success-jumbotron', NULL),
(0000000023, 0000000005, 'user_input_success-heading', NULL),
(0000000024, 0000000006, 'user_input_success-markdown', NULL),
(0000000025, 0000000010, 'contact-chat', NULL),
(0000000026, 0000000009, 'validate-validate', NULL),
(0000000027, 0000000008, 'toLogin-button', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections_fields_translation`
--

CREATE TABLE `sections_fields_translation` (
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `id_genders` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections_fields_translation`
--

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(0000000001, 0000000001, 0000000002, 0000000001, 'Benutzername'),
(0000000001, 0000000001, 0000000003, 0000000001, 'Username'),
(0000000001, 0000000002, 0000000002, 0000000001, 'Passwort'),
(0000000001, 0000000002, 0000000003, 0000000001, 'Password'),
(0000000001, 0000000003, 0000000002, 0000000001, 'Anmelden'),
(0000000001, 0000000003, 0000000003, 0000000001, 'Login'),
(0000000001, 0000000004, 0000000002, 0000000001, 'Passwort vergessen?'),
(0000000001, 0000000004, 0000000003, 0000000001, 'Forgotten the Password?'),
(0000000001, 0000000005, 0000000002, 0000000001, 'Der Benutzername oder das Passwort ist nicht korrekt.'),
(0000000001, 0000000005, 0000000003, 0000000001, 'The username or the password is not correct.'),
(0000000001, 0000000007, 0000000002, 0000000001, 'Bitte einloggen'),
(0000000001, 0000000007, 0000000003, 0000000001, 'Please Login'),
(0000000002, 0000000001, 0000000002, 0000000001, 'Email Adresse'),
(0000000002, 0000000001, 0000000003, 0000000001, 'Email Address'),
(0000000002, 0000000002, 0000000002, 0000000001, 'Neues Passwort'),
(0000000002, 0000000002, 0000000003, 0000000001, 'New Password'),
(0000000002, 0000000009, 0000000002, 0000000001, 'Neues Passwort wiederholen'),
(0000000002, 0000000009, 0000000003, 0000000001, 'Repeat New Password'),
(0000000002, 0000000010, 0000000002, 0000000001, 'Ändern'),
(0000000002, 0000000010, 0000000003, 0000000001, 'Submit Change'),
(0000000002, 0000000011, 0000000002, 0000000001, 'Passwort ändern'),
(0000000002, 0000000011, 0000000003, 0000000001, 'Change the Password'),
(0000000002, 0000000012, 0000000002, 0000000001, 'Account löschen'),
(0000000002, 0000000012, 0000000003, 0000000001, 'Delete Account'),
(0000000002, 0000000013, 0000000002, 0000000001, 'Löschen'),
(0000000002, 0000000013, 0000000003, 0000000001, 'Delete'),
(0000000002, 0000000014, 0000000002, 0000000001, 'Alle Benutzerdaten werden gelöscht. Das Löschen des Accounts ist permanent und kann nicht rückganging gemacht werden!'),
(0000000002, 0000000014, 0000000003, 0000000001, 'All user data will be deleted. The deletion of the account is permanent and cannot be undone!'),
(0000000002, 0000000015, 0000000002, 0000000001, 'Löschen bestätigen'),
(0000000002, 0000000015, 0000000003, 0000000001, 'Confirm Deletion'),
(0000000002, 0000000016, 0000000002, 0000000001, 'Wollen sie ihren Account wirklich löschen? Bestätigen Sie dies indem Sie ihre email Adresse eingeben.'),
(0000000002, 0000000016, 0000000003, 0000000001, 'Are you sure you want to delete the account? Please confirm by entering your email address.'),
(0000000002, 0000000017, 0000000002, 0000000001, 'Das Passwort konnte nicht geändert werden.'),
(0000000002, 0000000017, 0000000003, 0000000001, 'Unable to change the password.'),
(0000000002, 0000000018, 0000000002, 0000000001, 'Das Passwort wurde erfolgreich geändert.'),
(0000000002, 0000000018, 0000000003, 0000000001, 'The password was successfully changed.'),
(0000000002, 0000000019, 0000000002, 0000000001, 'Die Benutzerdaten konnten nicht gelöscht werden.'),
(0000000002, 0000000019, 0000000003, 0000000001, 'Unable to delete the account.'),
(0000000002, 0000000020, 0000000002, 0000000001, 'Die Benutzerdaten wurden erfolgreich gelöscht.'),
(0000000002, 0000000020, 0000000003, 0000000001, 'Successfully deleted the account.'),
(0000000003, 0000000029, 0000000001, 0000000001, '1'),
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
(0000000009, 0000000029, 0000000001, 0000000001, '1'),
(0000000011, 0000000021, 0000000001, 0000000001, '1'),
(0000000011, 0000000022, 0000000002, 0000000001, 'Kein Zugriff'),
(0000000011, 0000000022, 0000000003, 0000000001, 'No Access'),
(0000000012, 0000000029, 0000000001, 0000000001, '1'),
(0000000014, 0000000025, 0000000002, 0000000001, 'Um diese Seite zu erreichen müssen Sie eingeloggt sein.'),
(0000000014, 0000000025, 0000000003, 0000000001, 'To reach this page you must be logged in.'),
(0000000015, 0000000025, 0000000002, 0000000001, 'Sie haben keine Zugriffsrechte für diese Seite.'),
(0000000015, 0000000025, 0000000003, 0000000001, 'You do not have access to this page.'),
(0000000016, 0000000029, 0000000001, 0000000001, '1'),
(0000000017, 0000000029, 0000000001, 0000000001, '0'),
(0000000018, 0000000029, 0000000001, 0000000001, '1'),
(0000000019, 0000000029, 0000000001, 0000000001, '1'),
(0000000020, 0000000029, 0000000001, 0000000001, '1'),
(0000000021, 0000000029, 0000000001, 0000000001, '1'),
(0000000023, 0000000021, 0000000001, 0000000001, '1'),
(0000000023, 0000000022, 0000000002, 0000000001, 'Die Daten wurden erfolgreich erfasst'),
(0000000023, 0000000022, 0000000003, 0000000001, 'The Data was Successfully Saved'),
(0000000024, 0000000025, 0000000002, 0000000001, 'Die Daten wurden erfolgreich in der Datenbank gespeichert.\r\nBesten Dank!'),
(0000000024, 0000000025, 0000000003, 0000000001, 'The data was successfully saved to the databases. Thanks a lot!'),
(0000000025, 0000000005, 0000000002, 0000000001, 'Es ist ein Fehler aufgetreten. Die Nachricht konnte nicht gesendet werden.'),
(0000000025, 0000000005, 0000000003, 0000000001, 'An error occurred. The message could not be sent.'),
(0000000025, 0000000008, 0000000002, 0000000001, 'Senden'),
(0000000025, 0000000008, 0000000003, 0000000001, 'Send'),
(0000000025, 0000000030, 0000000002, 0000000001, 'Bitte wählen Sie einen Probanden aus.'),
(0000000025, 0000000030, 0000000003, 0000000001, 'Please select a subject'),
(0000000025, 0000000031, 0000000002, 0000000001, 'Kommunikation mit'),
(0000000025, 0000000031, 0000000003, 0000000001, 'Communication with'),
(0000000025, 0000000032, 0000000002, 0000000001, 'ihrer Psychologin/ihrem Psychologe'),
(0000000025, 0000000032, 0000000003, 0000000001, 'your psychologist'),
(0000000025, 0000000033, 0000000002, 0000000001, 'Probanden'),
(0000000025, 0000000033, 0000000003, 0000000001, 'Subjects'),
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
(0000000026, 0000000036, 0000000002, 0000000001, 'Benuzername'),
(0000000026, 0000000036, 0000000003, 0000000001, 'Username'),
(0000000026, 0000000037, 0000000002, 0000000001, 'Bitte den Benutzernamen eingeben'),
(0000000026, 0000000037, 0000000003, 0000000001, 'Please enter a username'),
(0000000026, 0000000038, 0000000002, 0000000001, 'Ein Name mit dem Sie angesprochen werden wollen. Aus Gründen der Annonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.'),
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
(0000000026, 0000000044, 0000000002, 0000000001, 'Benutzer erforlgreich aktiviert'),
(0000000026, 0000000044, 0000000003, 0000000001, 'User was successfully Activated'),
(0000000027, 0000000008, 0000000002, 0000000001, 'Zum Login'),
(0000000027, 0000000008, 0000000003, 0000000001, 'To Login'),
(0000000027, 0000000027, 0000000001, 0000000001, '#login'),
(0000000027, 0000000028, 0000000001, 0000000001, 'primary');

-- --------------------------------------------------------

--
-- Table structure for table `sections_hierarchy`
--

CREATE TABLE `sections_hierarchy` (
  `parent` int(10) UNSIGNED ZEROFILL NOT NULL,
  `child` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections_hierarchy`
--

INSERT INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
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
(0000000021, 0000000022, 0),
(0000000022, 0000000007, 20),
(0000000022, 0000000008, 30),
(0000000022, 0000000023, 0),
(0000000022, 0000000024, 10);

-- --------------------------------------------------------

--
-- Table structure for table `sections_navigation`
--

CREATE TABLE `sections_navigation` (
  `parent` int(10) UNSIGNED ZEROFILL NOT NULL,
  `child` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `styles`
--

CREATE TABLE `styles` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `intern` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `name`, `id_type`, `intern`) VALUES
(0000000001, 'login', 0000000002, 1),
(0000000002, 'profile', 0000000002, 1),
(0000000003, 'container', 0000000001, 0),
(0000000004, 'jumbotron', 0000000001, 0),
(0000000005, 'heading', 0000000001, 0),
(0000000006, 'markdown', 0000000001, 0),
(0000000007, 'markdownInline', 0000000001, 0),
(0000000008, 'button', 0000000001, 0),
(0000000009, 'validate', 0000000002, 1),
(0000000010, 'chat', 0000000002, 1),
(0000000011, 'alert', 0000000001, 0),
(0000000012, 'card', 0000000001, 0),
(0000000013, 'figure', 0000000001, 0),
(0000000014, 'form', 0000000001, 0),
(0000000015, 'image', 0000000001, 0),
(0000000016, 'input', 0000000001, 0),
(0000000017, 'plaintext', 0000000001, 0),
(0000000018, 'link', 0000000001, 0),
(0000000019, 'progressBar', 0000000001, 0),
(0000000020, 'quiz', 0000000001, 0),
(0000000021, 'rawText', 0000000001, 0),
(0000000022, 'select', 0000000001, 0),
(0000000023, 'slider', 0000000001, 0),
(0000000024, 'tab', 0000000001, 0),
(0000000025, 'tabs', 0000000001, 0),
(0000000026, 'textarea', 0000000001, 0),
(0000000027, 'video', 0000000001, 0),
(0000000028, 'accordionList', 0000000002, 0),
(0000000030, 'navigationContainer', 0000000001, 0),
(0000000031, 'navigationAccordion', 0000000003, 0),
(0000000032, 'nestedList', 0000000002, 0),
(0000000033, 'navigationNested', 0000000003, 0),
(0000000034, 'sortableList', 0000000001, 0);

-- --------------------------------------------------------

--
-- Table structure for table `styles_fields`
--

CREATE TABLE `styles_fields` (
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styles_fields`
--

INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES
(0000000001, 0000000001),
(0000000001, 0000000002),
(0000000001, 0000000003),
(0000000001, 0000000004),
(0000000001, 0000000005),
(0000000001, 0000000006),
(0000000001, 0000000007),
(0000000003, 0000000006),
(0000000003, 0000000029),
(0000000004, 0000000006),
(0000000005, 0000000021),
(0000000005, 0000000022),
(0000000006, 0000000025),
(0000000007, 0000000026),
(0000000008, 0000000008),
(0000000008, 0000000027),
(0000000008, 0000000028),
(0000000009, 0000000002),
(0000000009, 0000000003),
(0000000009, 0000000005),
(0000000009, 0000000009),
(0000000009, 0000000022),
(0000000009, 0000000034),
(0000000009, 0000000035),
(0000000009, 0000000036),
(0000000009, 0000000037),
(0000000009, 0000000038),
(0000000009, 0000000039),
(0000000009, 0000000040),
(0000000009, 0000000041),
(0000000009, 0000000042),
(0000000009, 0000000043),
(0000000009, 0000000044),
(0000000010, 0000000005),
(0000000010, 0000000008),
(0000000010, 0000000030),
(0000000010, 0000000031),
(0000000010, 0000000032),
(0000000010, 0000000033),
(0000000011, 0000000006),
(0000000011, 0000000028),
(0000000011, 0000000045),
(0000000012, 0000000006),
(0000000012, 0000000022),
(0000000012, 0000000028),
(0000000012, 0000000046),
(0000000012, 0000000047),
(0000000012, 0000000048),
(0000000013, 0000000006),
(0000000013, 0000000049),
(0000000013, 0000000050),
(0000000014, 0000000006),
(0000000014, 0000000008),
(0000000014, 0000000027),
(0000000014, 0000000028),
(0000000014, 0000000051),
(0000000014, 0000000052),
(0000000015, 0000000022),
(0000000015, 0000000030),
(0000000015, 0000000053),
(0000000016, 0000000008),
(0000000016, 0000000054),
(0000000016, 0000000055),
(0000000016, 0000000056),
(0000000016, 0000000057),
(0000000016, 0000000058),
(0000000017, 0000000024),
(0000000017, 0000000059),
(0000000018, 0000000008),
(0000000018, 0000000027),
(0000000019, 0000000028),
(0000000019, 0000000060),
(0000000019, 0000000061),
(0000000020, 0000000028),
(0000000020, 0000000050),
(0000000020, 0000000062),
(0000000020, 0000000063),
(0000000020, 0000000064),
(0000000020, 0000000065),
(0000000021, 0000000024),
(0000000022, 0000000008),
(0000000022, 0000000030),
(0000000022, 0000000056),
(0000000022, 0000000057),
(0000000022, 0000000058),
(0000000022, 0000000066),
(0000000022, 0000000067),
(0000000023, 0000000008),
(0000000023, 0000000057),
(0000000023, 0000000058),
(0000000023, 0000000068),
(0000000023, 0000000069),
(0000000023, 0000000070),
(0000000024, 0000000006),
(0000000024, 0000000008),
(0000000024, 0000000028),
(0000000025, 0000000006),
(0000000026, 0000000008),
(0000000026, 0000000056),
(0000000026, 0000000057),
(0000000026, 0000000058),
(0000000027, 0000000030),
(0000000027, 0000000071),
(0000000028, 0000000031),
(0000000028, 0000000066),
(0000000028, 0000000072),
(0000000028, 0000000083),
(0000000028, 0000000084),
(0000000030, 0000000006),
(0000000030, 0000000022),
(0000000031, 0000000031),
(0000000031, 0000000072),
(0000000031, 0000000073),
(0000000031, 0000000074),
(0000000031, 0000000075),
(0000000032, 0000000046),
(0000000032, 0000000047),
(0000000032, 0000000066),
(0000000032, 0000000077),
(0000000032, 0000000083),
(0000000032, 0000000084),
(0000000033, 0000000046),
(0000000033, 0000000073),
(0000000033, 0000000074),
(0000000033, 0000000075),
(0000000033, 0000000077),
(0000000034, 0000000066),
(0000000034, 0000000078),
(0000000034, 0000000079),
(0000000034, 0000000080),
(0000000034, 0000000081),
(0000000034, 0000000082);

-- --------------------------------------------------------

--
-- Table structure for table `styleType`
--

CREATE TABLE `styleType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styleType`
--

INSERT INTO `styleType` (`id`, `name`) VALUES
(0000000001, 'view'),
(0000000002, 'component'),
(0000000003, 'navigation');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_genders` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `intern` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `password`, `id_genders`, `blocked`, `intern`, `token`) VALUES
(0000000001, 'guest', '', NULL, NULL, 0, 1, NULL),
(0000000002, 'admin', 'admin', '$2y$10$lqb/Eieowq8lWTUxVrb1MOHrZ1ZDvbnU4RNvWxqP5pa8/QOdwFB8e', NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE `users_groups` (
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_groups` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`id_users`, `id_groups`) VALUES
(0000000002, 0000000001),
(0000000002, 0000000002);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `url` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_input`
--

CREATE TABLE `user_input` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `value` longtext NOT NULL,
  `edit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl_groups`
--
ALTER TABLE `acl_groups`
  ADD PRIMARY KEY (`id_groups`,`id_pages`),
  ADD KEY `id_pages` (`id_pages`) USING BTREE,
  ADD KEY `id_groups` (`id_groups`) USING BTREE;

--
-- Indexes for table `acl_users`
--
ALTER TABLE `acl_users`
  ADD PRIMARY KEY (`id_users`,`id_pages`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_pages` (`id_pages`);

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_snd` (`id_snd`) USING BTREE,
  ADD KEY `id_rcv` (`id_rcv`) USING BTREE;

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type` (`id_type`);

--
-- Indexes for table `fieldType`
--
ALTER TABLE `fieldType`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `genders`
--
ALTER TABLE `genders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keyword` (`keyword`),
  ADD KEY `parent` (`parent`),
  ADD KEY `id_actions` (`id_actions`),
  ADD KEY `id_navigation_section` (`id_navigation_section`),
  ADD KEY `id_type` (`id_type`);

--
-- Indexes for table `pages_fields_translation`
--
ALTER TABLE `pages_fields_translation`
  ADD PRIMARY KEY (`id_pages`,`id_fields`,`id_languages`),
  ADD KEY `id_pages` (`id_pages`),
  ADD KEY `id_fields` (`id_fields`),
  ADD KEY `id_languages` (`id_languages`);

--
-- Indexes for table `pages_sections`
--
ALTER TABLE `pages_sections`
  ADD PRIMARY KEY (`id_pages`,`id_sections`),
  ADD KEY `id_pages` (`id_pages`),
  ADD KEY `id_sections` (`id_sections`);

--
-- Indexes for table `pageType`
--
ALTER TABLE `pageType`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_styles` (`id_styles`),
  ADD KEY `owner` (`owner`);

--
-- Indexes for table `sections_fields_translation`
--
ALTER TABLE `sections_fields_translation`
  ADD PRIMARY KEY (`id_sections`,`id_fields`,`id_languages`,`id_genders`),
  ADD KEY `id_sections` (`id_sections`),
  ADD KEY `id_fields` (`id_fields`),
  ADD KEY `id_languages` (`id_languages`),
  ADD KEY `id_genders` (`id_genders`);

--
-- Indexes for table `sections_hierarchy`
--
ALTER TABLE `sections_hierarchy`
  ADD PRIMARY KEY (`parent`,`child`),
  ADD KEY `parent` (`parent`),
  ADD KEY `child` (`child`);

--
-- Indexes for table `sections_navigation`
--
ALTER TABLE `sections_navigation`
  ADD PRIMARY KEY (`parent`,`child`),
  ADD KEY `child` (`child`),
  ADD KEY `parent` (`parent`),
  ADD KEY `id_pages` (`id_pages`);

--
-- Indexes for table `styles`
--
ALTER TABLE `styles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type` (`id_type`);

--
-- Indexes for table `styles_fields`
--
ALTER TABLE `styles_fields`
  ADD PRIMARY KEY (`id_styles`,`id_fields`),
  ADD KEY `id_styles` (`id_styles`),
  ADD KEY `id_fields` (`id_fields`);

--
-- Indexes for table `styleType`
--
ALTER TABLE `styleType`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_genders` (`id_genders`);

--
-- Indexes for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD PRIMARY KEY (`id_users`,`id_groups`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_groups` (`id_groups`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_users` (`id_users`);

--
-- Indexes for table `user_input`
--
ALTER TABLE `user_input`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_sections` (`id_sections`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;
--
-- AUTO_INCREMENT for table `fieldType`
--
ALTER TABLE `fieldType`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `genders`
--
ALTER TABLE `genders`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT for table `pageType`
--
ALTER TABLE `pageType`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `styles`
--
ALTER TABLE `styles`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT for table `styleType`
--
ALTER TABLE `styleType`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_input`
--
ALTER TABLE `user_input`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
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
  ADD CONSTRAINT `fk_chat_id_rcv_user` FOREIGN KEY (`id_rcv`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_id_send` FOREIGN KEY (`id_snd`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fields`
--
ALTER TABLE `fields`
  ADD CONSTRAINT `fields_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `fieldType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_fk_id_actions` FOREIGN KEY (`id_actions`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fk_id_navigation_section` FOREIGN KEY (`id_navigation_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `pageType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_fk_parent` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `styles_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `styleType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `styles_fields`
--
ALTER TABLE `styles_fields`
  ADD CONSTRAINT `styles_fields_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_fields_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_user_activity_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_input`
--
ALTER TABLE `user_input`
  ADD CONSTRAINT `user_input_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_input_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
