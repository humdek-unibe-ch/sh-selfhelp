-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sh
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acl_groups`
--

DROP TABLE IF EXISTS `acl_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_groups` (
  `id_groups` int(10) unsigned zerofill NOT NULL,
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_groups`,`id_pages`),
  KEY `id_pages` (`id_pages`) USING BTREE,
  KEY `id_groups` (`id_groups`) USING BTREE,
  CONSTRAINT `fk_acl_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_acl_groups_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acl_users`
--

DROP TABLE IF EXISTS `acl_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_users` (
  `id_users` int(10) unsigned zerofill NOT NULL,
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_users`,`id_pages`),
  KEY `id_users` (`id_users`),
  KEY `id_pages` (`id_pages`),
  CONSTRAINT `acl_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `acl_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_routes`
--

DROP TABLE IF EXISTS `api_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_routes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'v1',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `controller` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `methods` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requirements` json DEFAULT NULL,
  `params` json DEFAULT NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`,`version`),
  UNIQUE KEY `uniq_version_path` (`version`,`path`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apiRequestLogs`
--

DROP TABLE IF EXISTS `apiRequestLogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `apiRequestLogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_code` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_time` datetime NOT NULL,
  `response_time` datetime NOT NULL,
  `duration_ms` int NOT NULL,
  `request_params` text COLLATE utf8mb4_unicode_ci,
  `request_headers` text COLLATE utf8mb4_unicode_ci,
  `response_data` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_assetTypes` int(10) unsigned zerofill NOT NULL,
  `folder` varchar(100) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `file_path` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_name` (`file_name`),
  KEY `assets_fk_id_assetTypes` (`id_assetTypes`),
  CONSTRAINT `assets_fk_id_assetTypes` FOREIGN KEY (`id_assetTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `callbackLogs`
--

DROP TABLE IF EXISTS `callbackLogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `callbackLogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `callback_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `remote_addr` varchar(200) DEFAULT NULL,
  `redirect_url` varchar(1000) DEFAULT NULL,
  `callback_params` longtext,
  `status` varchar(200) DEFAULT NULL,
  `callback_output` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cmsPreferences`
--

DROP TABLE IF EXISTS `cmsPreferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmsPreferences` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `callback_api_key` varchar(500) DEFAULT NULL,
  `default_language_id` int(10) unsigned zerofill DEFAULT NULL,
  `anonymous_users` int DEFAULT '0',
  `firebase_config` varchar(10000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cmsPreferences_language` (`default_language_id`),
  CONSTRAINT `fk_cmsPreferences_language` FOREIGN KEY (`default_language_id`) REFERENCES `languages` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `codes_groups`
--

DROP TABLE IF EXISTS `codes_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `codes_groups` (
  `code` varchar(16) NOT NULL,
  `id_groups` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`code`,`id_groups`),
  KEY `fk_id_groups` (`id_groups`),
  CONSTRAINT `fk_codes` FOREIGN KEY (`code`) REFERENCES `validation_codes` (`code`) ON DELETE CASCADE,
  CONSTRAINT `fk_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataCells`
--

DROP TABLE IF EXISTS `dataCells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataCells` (
  `id_dataRows` int(10) unsigned zerofill NOT NULL,
  `id_dataCols` int(10) unsigned zerofill NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id_dataRows`,`id_dataCols`),
  KEY `id_uploadRows` (`id_dataRows`),
  KEY `id_uploadCols` (`id_dataCols`),
  KEY `idx_uploadCells_value` (`value`(255)),
  CONSTRAINT `uploadCells_fk_id_uploadCols` FOREIGN KEY (`id_dataCols`) REFERENCES `dataCols` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uploadCells_fk_id_uploadRows` FOREIGN KEY (`id_dataRows`) REFERENCES `dataRows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataCols`
--

DROP TABLE IF EXISTS `dataCols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataCols` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `id_dataTables` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_id_dataTables` (`name`,`id_dataTables`),
  KEY `id_uploadTables` (`id_dataTables`),
  CONSTRAINT `uploadCols_fk_id_uploadTables` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataRows`
--

DROP TABLE IF EXISTS `dataRows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataRows` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_dataTables` int(10) unsigned zerofill DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_users` int(10) unsigned zerofill DEFAULT NULL,
  `id_actionTriggerTypes` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_uploadTables` (`id_dataTables`),
  KEY `uploadRows_fk_id_users` (`id_users`),
  KEY `idx_uploadRows_timestamp` (`timestamp`),
  KEY `uploadRows_fk_id_actionTriggerTypes` (`id_actionTriggerTypes`),
  CONSTRAINT `uploadRows_fk_id_actionTriggerTypes` FOREIGN KEY (`id_actionTriggerTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uploadRows_fk_id_uploadTables` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `uploadRows_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataTables`
--

DROP TABLE IF EXISTS `dataTables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataTables` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `displayName` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uploadTables_name` (`name`),
  KEY `idx_uploadTables_name_timestamp` (`name`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fields` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) unsigned zerofill NOT NULL DEFAULT '0000000002',
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fields_name` (`name`),
  KEY `id_type` (`id_type`),
  CONSTRAINT `fields_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `fieldType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fieldType`
--

DROP TABLE IF EXISTS `fieldType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fieldType` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fieldType_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formActions`
--

DROP TABLE IF EXISTS `formActions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formActions` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `id_formProjectActionTriggerTypes` int(10) unsigned zerofill NOT NULL,
  `config` text,
  `id_dataTables` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formActions_id_dataTables` (`id_dataTables`),
  CONSTRAINT `formActions_id_dataTables` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genders`
--

DROP TABLE IF EXISTS `genders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genders` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `id_group_types` int(10) unsigned zerofill DEFAULT NULL,
  `requires_2fa` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `groups_fk_id_group_types` (`id_group_types`),
  CONSTRAINT `groups_fk_id_group_types` FOREIGN KEY (`id_group_types`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hooks`
--

DROP TABLE IF EXISTS `hooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hooks` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_hookTypes` int(10) unsigned zerofill NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `class` varchar(100) NOT NULL,
  `function` varchar(100) NOT NULL,
  `exec_class` varchar(100) NOT NULL,
  `exec_function` varchar(100) NOT NULL,
  `priority` int DEFAULT '10',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `hooks_fk_id_hookTypes` (`id_hookTypes`),
  CONSTRAINT `hooks_fk_id_hookTypes` FOREIGN KEY (`id_hookTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL COMMENT '"e.g en-GB, de-CH"',
  `language` varchar(100) NOT NULL,
  `csv_separator` varchar(1) NOT NULL DEFAULT ',',
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`),
  UNIQUE KEY `language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `libraries`
--

DROP TABLE IF EXISTS `libraries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `libraries` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `version` varchar(500) DEFAULT NULL,
  `license` varchar(1000) DEFAULT NULL,
  `comments` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logPerformance`
--

DROP TABLE IF EXISTS `logPerformance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logPerformance` (
  `id_user_activity` int unsigned NOT NULL,
  `log` longtext,
  PRIMARY KEY (`id_user_activity`),
  CONSTRAINT `logperformance_ibfk_1` FOREIGN KEY (`id_user_activity`) REFERENCES `user_activity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lookups`
--

DROP TABLE IF EXISTS `lookups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lookups` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `type_code` varchar(100) NOT NULL,
  `lookup_code` varchar(100) DEFAULT NULL,
  `lookup_value` varchar(200) DEFAULT NULL,
  `lookup_description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_lookups_type_code_lookup_code` (`type_code`,`lookup_code`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailAttachments`
--

DROP TABLE IF EXISTS `mailAttachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailAttachments` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_mailQueue` int(10) unsigned zerofill NOT NULL,
  `attachment_name` varchar(1000) DEFAULT NULL,
  `attachment_path` varchar(1000) NOT NULL,
  `attachment_url` varchar(1000) NOT NULL,
  `template_path` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mailAttachments_fk_id_mailQueue` (`id_mailQueue`),
  CONSTRAINT `mailAttachments_fk_id_mailQueue` FOREIGN KEY (`id_mailQueue`) REFERENCES `mailQueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailQueue`
--

DROP TABLE IF EXISTS `mailQueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailQueue` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `from_email` varchar(100) NOT NULL,
  `from_name` varchar(100) NOT NULL,
  `reply_to` varchar(100) NOT NULL,
  `recipient_emails` text NOT NULL,
  `cc_emails` varchar(1000) DEFAULT NULL,
  `bcc_emails` varchar(1000) DEFAULT NULL,
  `subject` varchar(1000) NOT NULL,
  `body` longtext NOT NULL,
  `is_html` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `subject` varchar(1000) NOT NULL,
  `body` longtext NOT NULL,
  `url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(100) DEFAULT NULL COMMENT 'pipe seperated list of HTTP Methods (GET|POST)',
  `id_actions` int(10) unsigned zerofill DEFAULT NULL,
  `id_navigation_section` int(10) unsigned zerofill DEFAULT NULL,
  `parent` int(10) unsigned zerofill DEFAULT NULL,
  `is_headless` tinyint(1) NOT NULL DEFAULT '0',
  `nav_position` int DEFAULT NULL,
  `footer_position` int DEFAULT NULL,
  `id_type` int(10) unsigned zerofill NOT NULL,
  `id_pageAccessTypes` int(10) unsigned zerofill DEFAULT NULL,
  `is_open_access` tinyint DEFAULT '0',
  `is_system` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `parent` (`parent`),
  KEY `id_actions` (`id_actions`),
  KEY `id_navigation_section` (`id_navigation_section`),
  KEY `id_type` (`id_type`),
  KEY `pages_fk_id_pacgeAccessTypes` (`id_pageAccessTypes`),
  CONSTRAINT `pages_fk_id_actions` FOREIGN KEY (`id_actions`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fk_id_navigation_section` FOREIGN KEY (`id_navigation_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fk_id_pacgeAccessTypes` FOREIGN KEY (`id_pageAccessTypes`) REFERENCES `lookups` (`id`),
  CONSTRAINT `pages_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `pageType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fk_parent` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_fields`
--

DROP TABLE IF EXISTS `pages_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_fields` (
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_pages`,`id_fields`),
  KEY `id_pages` (`id_pages`),
  KEY `id_fields` (`id_fields`),
  CONSTRAINT `fk_page_fields_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_page_fields_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_fields_translation`
--

DROP TABLE IF EXISTS `pages_fields_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_fields_translation` (
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `id_languages` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL,
  PRIMARY KEY (`id_pages`,`id_fields`,`id_languages`),
  KEY `id_pages` (`id_pages`),
  KEY `id_fields` (`id_fields`),
  KEY `id_languages` (`id_languages`),
  CONSTRAINT `pages_fields_translation_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fields_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fields_translation_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_sections`
--

DROP TABLE IF EXISTS `pages_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_sections` (
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `id_sections` int(10) unsigned zerofill NOT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`id_pages`,`id_sections`),
  KEY `id_pages` (`id_pages`),
  KEY `id_sections` (`id_sections`),
  CONSTRAINT `pages_sections_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_sections_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageType`
--

DROP TABLE IF EXISTS `pageType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pageType` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pageType_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageType_fields`
--

DROP TABLE IF EXISTS `pageType_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pageType_fields` (
  `id_pageType` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_pageType`,`id_fields`),
  KEY `fk_pageType_fields_id_fields` (`id_fields`),
  CONSTRAINT `fk_pageType_fields_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pageType_fields_id_pageType` FOREIGN KEY (`id_pageType`) REFERENCES `pageType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `version` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugins_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `refreshTokens`
--

DROP TABLE IF EXISTS `refreshTokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refreshTokens` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_users` bigint NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb3_bin NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token_hash` (`token_hash`),
  KEY `idx_user_id` (`id_users`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs`
--

DROP TABLE IF EXISTS `scheduledJobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_jobTypes` int(10) unsigned zerofill NOT NULL,
  `id_jobStatus` int(10) unsigned zerofill NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `date_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_to_be_executed` datetime DEFAULT NULL,
  `date_executed` datetime DEFAULT NULL,
  `config` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduledJobs_fk_id_jobTypes` (`id_jobTypes`),
  KEY `scheduledJobs_fk_id_jobStatus` (`id_jobStatus`),
  CONSTRAINT `scheduledJobs_fk_id_jobStatus` FOREIGN KEY (`id_jobStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_fk_id_jobTypes` FOREIGN KEY (`id_jobTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_formActions`
--

DROP TABLE IF EXISTS `scheduledJobs_formActions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_formActions` (
  `id_scheduledJobs` int(10) unsigned zerofill NOT NULL,
  `id_formActions` int(10) unsigned zerofill NOT NULL,
  `id_dataRows` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_formActions`),
  KEY `scheduledJobs_formActions_fk_iid_formActions` (`id_formActions`),
  KEY `scheduledJobs_formActions_id_dataRows` (`id_dataRows`),
  CONSTRAINT `scheduledJobs_formActions_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_formActions_fk_iid_formActions` FOREIGN KEY (`id_formActions`) REFERENCES `formActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_formActions_id_dataRows` FOREIGN KEY (`id_dataRows`) REFERENCES `dataRows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_mailQueue`
--

DROP TABLE IF EXISTS `scheduledJobs_mailQueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_mailQueue` (
  `id_scheduledJobs` int(10) unsigned zerofill NOT NULL,
  `id_mailQueue` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_mailQueue`),
  KEY `scheduledJobs_mailQueue_fk_id_mailQueue` (`id_mailQueue`),
  CONSTRAINT `scheduledJobs_mailQueue_fk_id_mailQueue` FOREIGN KEY (`id_mailQueue`) REFERENCES `mailQueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_mailQueue_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_notifications`
--

DROP TABLE IF EXISTS `scheduledJobs_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_notifications` (
  `id_scheduledJobs` int(10) unsigned zerofill NOT NULL,
  `id_notifications` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_notifications`),
  KEY `scheduledJobs_notifications_fk_id_notifications` (`id_notifications`),
  CONSTRAINT `scheduledJobs_notifications_fk_id_notifications` FOREIGN KEY (`id_notifications`) REFERENCES `notifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_notifications_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_reminders`
--

DROP TABLE IF EXISTS `scheduledJobs_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_reminders` (
  `id_scheduledJobs` int(10) unsigned zerofill NOT NULL,
  `id_dataTables` int(10) unsigned zerofill NOT NULL,
  `session_start_date` datetime DEFAULT NULL,
  `session_end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_dataTables`),
  KEY `scheduledJobs_reminders_id_dataTables` (`id_dataTables`),
  CONSTRAINT `scheduledJobs_reminders_id_dataTables` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_reminders_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_tasks`
--

DROP TABLE IF EXISTS `scheduledJobs_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_tasks` (
  `id_scheduledJobs` int(10) unsigned zerofill NOT NULL,
  `id_tasks` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_tasks`),
  KEY `scheduledJobs_tasks_fk_id_tasks` (`id_tasks`),
  CONSTRAINT `scheduledJobs_tasks_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_tasks_fk_id_tasks` FOREIGN KEY (`id_tasks`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_users`
--

DROP TABLE IF EXISTS `scheduledJobs_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_users` (
  `id_users` int(10) unsigned zerofill NOT NULL,
  `id_scheduledJobs` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_users`,`id_scheduledJobs`),
  KEY `scheduledJobs_users_fk_scheduledJobs` (`id_scheduledJobs`),
  CONSTRAINT `scheduledJobs_users_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduledJobs_users_fk_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_styles` int(10) unsigned zerofill NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id_styles` (`id_styles`),
  CONSTRAINT `sections_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections_fields_translation`
--

DROP TABLE IF EXISTS `sections_fields_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections_fields_translation` (
  `id_sections` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `id_languages` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `id_genders` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL,
  `meta` varchar(10000) DEFAULT NULL,
  PRIMARY KEY (`id_sections`,`id_fields`,`id_languages`,`id_genders`),
  KEY `id_sections` (`id_sections`),
  KEY `id_fields` (`id_fields`),
  KEY `id_languages` (`id_languages`),
  KEY `id_genders` (`id_genders`),
  CONSTRAINT `sections_fields_translation_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fields_translation_fk_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fields_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fields_translation_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections_hierarchy`
--

DROP TABLE IF EXISTS `sections_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections_hierarchy` (
  `parent` int(10) unsigned zerofill NOT NULL,
  `child` int(10) unsigned zerofill NOT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `parent` (`parent`),
  KEY `child` (`child`),
  CONSTRAINT `sections_hierarchy_fk_child` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_hierarchy_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections_navigation`
--

DROP TABLE IF EXISTS `sections_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections_navigation` (
  `parent` int(10) unsigned zerofill NOT NULL,
  `child` int(10) unsigned zerofill NOT NULL,
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  KEY `parent` (`parent`),
  KEY `id_pages` (`id_pages`),
  CONSTRAINT `sections_navigation_fk_child` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_navigation_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_navigation_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styleGroup`
--

DROP TABLE IF EXISTS `styleGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `styleGroup` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `styleGroup_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styles`
--

DROP TABLE IF EXISTS `styles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `styles` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `id_group` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `description` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `styles_name` (`name`),
  KEY `id_type` (`id_type`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `styles_fk_id_group` FOREIGN KEY (`id_group`) REFERENCES `styleGroup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `styles_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styles_fields`
--

DROP TABLE IF EXISTS `styles_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `styles_fields` (
  `id_styles` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` int DEFAULT '0',
  PRIMARY KEY (`id_styles`,`id_fields`),
  KEY `id_styles` (`id_styles`),
  KEY `id_fields` (`id_fields`),
  CONSTRAINT `styles_fields_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `styles_fields_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `config` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `transaction_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_transactionTypes` int unsigned DEFAULT NULL,
  `id_transactionBy` int unsigned DEFAULT NULL,
  `id_users` int unsigned DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `id_table_name` int unsigned DEFAULT NULL,
  `transaction_log` mediumtext,
  PRIMARY KEY (`id`),
  KEY `transactions_fk_id_transactionTypes` (`id_transactionTypes`),
  KEY `transactions_fk_id_transactionBy` (`id_transactionBy`),
  KEY `transactions_fk_id_users` (`id_users`),
  KEY `idx_transactions_table_name` (`table_name`),
  CONSTRAINT `transactions_fk_id_transactionBy` FOREIGN KEY (`id_transactionBy`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactions_fk_id_transactionTypes` FOREIGN KEY (`id_transactionTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactions_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activity` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned zerofill NOT NULL,
  `url` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_type` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `exec_time` decimal(10,8) DEFAULT NULL,
  `keyword` varchar(100) DEFAULT NULL,
  `params` varchar(1000) DEFAULT NULL,
  `mobile` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_type` (`id_type`),
  CONSTRAINT `fk_user_activity_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_activity_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_genders` int(10) unsigned zerofill DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `id_status` int(10) unsigned zerofill DEFAULT '0000000001',
  `intern` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(32) DEFAULT NULL,
  `id_languages` int(10) unsigned zerofill DEFAULT NULL,
  `is_reminded` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` date DEFAULT NULL,
  `last_url` varchar(100) DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `device_token` varchar(200) DEFAULT NULL,
  `security_questions` varchar(1000) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `id_userTypes` int(10) unsigned zerofill NOT NULL DEFAULT '0000000072',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `id_genders` (`id_genders`),
  KEY `id_languages` (`id_languages`),
  KEY `id_status` (`id_status`),
  CONSTRAINT `fk_users_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_id_status` FOREIGN KEY (`id_status`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_2fa_codes`
--

DROP TABLE IF EXISTS `users_2fa_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_2fa_codes` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned zerofill NOT NULL,
  `code` varchar(6) COLLATE utf8mb3_bin NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_groups` (
  `id_users` int(10) unsigned zerofill NOT NULL,
  `id_groups` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_users`,`id_groups`),
  KEY `id_users` (`id_users`),
  KEY `id_groups` (`id_groups`),
  CONSTRAINT `fk_users_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_groups_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `validation_codes`
--

DROP TABLE IF EXISTS `validation_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `validation_codes` (
  `code` varchar(16) NOT NULL,
  `id_users` int(10) unsigned zerofill DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `consumed` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `id_users` (`id_users`),
  CONSTRAINT `validation_codes_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `version` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `version` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-19 13:52:40
