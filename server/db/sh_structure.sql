-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sym
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
  `id_groups` int NOT NULL,
  `id_pages` int NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_groups`,`id_pages`),
  KEY `IDX_AB370E20D65A8C9D` (`id_groups`),
  KEY `IDX_AB370E20CEF1A445` (`id_pages`),
  CONSTRAINT `FK_AB370E20CEF1A445` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AB370E20D65A8C9D` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acl_users`
--

DROP TABLE IF EXISTS `acl_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_users` (
  `id_users` int NOT NULL,
  `id_pages` int NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_users`,`id_pages`),
  KEY `IDX_901AE856FA06E4D9` (`id_users`),
  KEY `IDX_901AE856CEF1A445` (`id_pages`),
  CONSTRAINT `FK_901AE856CEF1A445` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_901AE856FA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `request_params` longtext COLLATE utf8mb4_unicode_ci,
  `request_headers` longtext COLLATE utf8mb4_unicode_ci,
  `response_data` longtext COLLATE utf8mb4_unicode_ci,
  `error_message` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_assetTypes` int NOT NULL,
  `folder` varchar(100) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `file_path` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_79D17D8ED7DF1668` (`file_name`)
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
  `id` int NOT NULL AUTO_INCREMENT,
  `callback_api_key` varchar(500) DEFAULT NULL,
  `default_language_id` int DEFAULT NULL,
  `anonymous_users` int NOT NULL DEFAULT '0',
  `firebase_config` varchar(10000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3F26A2DF5602A942` (`default_language_id`),
  CONSTRAINT `FK_3F26A2DF5602A942` FOREIGN KEY (`default_language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE
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
  `id_groups` int NOT NULL,
  PRIMARY KEY (`code`,`id_groups`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataCells`
--

DROP TABLE IF EXISTS `dataCells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataCells` (
  `id_dataRows` int NOT NULL,
  `id_dataCols` int NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id_dataRows`,`id_dataCols`),
  KEY `IDX_726A5F25F3854F45` (`id_dataRows`),
  KEY `IDX_726A5F25B216B425` (`id_dataCols`),
  CONSTRAINT `FK_726A5F25B216B425` FOREIGN KEY (`id_dataCols`) REFERENCES `dataCols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_726A5F25F3854F45` FOREIGN KEY (`id_dataRows`) REFERENCES `dataRows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataCols`
--

DROP TABLE IF EXISTS `dataCols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataCols` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `id_dataTables` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E2CD58B0E2E6A7C3` (`id_dataTables`),
  CONSTRAINT `FK_E2CD58B0E2E6A7C3` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataRows`
--

DROP TABLE IF EXISTS `dataRows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataRows` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_dataTables` int DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `id_users` int DEFAULT NULL,
  `id_actionTriggerTypes` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A35EA3D0E2E6A7C3` (`id_dataTables`),
  CONSTRAINT `FK_A35EA3D0E2E6A7C3` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dataTables`
--

DROP TABLE IF EXISTS `dataTables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dataTables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL,
  `displayName` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int NOT NULL,
  `display` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7EE5E3887FE4B2B` (`id_type`),
  CONSTRAINT `FK_7EE5E388FF2309B7` FOREIGN KEY (`id_type`) REFERENCES `fieldType` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fieldType`
--

DROP TABLE IF EXISTS `fieldType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fieldType` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formActions`
--

DROP TABLE IF EXISTS `formActions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formActions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `id_formProjectActionTriggerTypes` int NOT NULL,
  `config` longtext,
  `id_dataTables` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3128FB5E8A8FCE9D` (`id_formProjectActionTriggerTypes`),
  KEY `IDX_3128FB5EE2E6A7C3` (`id_dataTables`),
  CONSTRAINT `FK_3128FB5E8A8FCE9D` FOREIGN KEY (`id_formProjectActionTriggerTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3128FB5EE2E6A7C3` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genders`
--

DROP TABLE IF EXISTS `genders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genders` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `id_group_types` int DEFAULT NULL,
  `requires_2fa` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hooks`
--

DROP TABLE IF EXISTS `hooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hooks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_hookTypes` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `class` varchar(100) NOT NULL,
  `function` varchar(100) NOT NULL,
  `exec_class` varchar(100) NOT NULL,
  `exec_function` varchar(100) NOT NULL,
  `priority` int NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `language` varchar(100) NOT NULL,
  `csv_separator` varchar(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `libraries`
--

DROP TABLE IF EXISTS `libraries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `libraries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `version` varchar(500) DEFAULT NULL,
  `license` varchar(1000) DEFAULT NULL,
  `comments` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logPerformance`
--

DROP TABLE IF EXISTS `logPerformance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logPerformance` (
  `id_user_activity` int NOT NULL,
  `log` longtext,
  PRIMARY KEY (`id_user_activity`),
  CONSTRAINT `FK_6D164595F2D13C3F` FOREIGN KEY (`id_user_activity`) REFERENCES `user_activity` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lookups`
--

DROP TABLE IF EXISTS `lookups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lookups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_code` varchar(100) NOT NULL,
  `lookup_code` varchar(100) DEFAULT NULL,
  `lookup_value` varchar(200) DEFAULT NULL,
  `lookup_description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_type_lookup` (`type_code`,`lookup_code`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailAttachments`
--

DROP TABLE IF EXISTS `mailAttachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailAttachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mailQueue` int NOT NULL,
  `attachment_name` varchar(1000) DEFAULT NULL,
  `attachment_path` varchar(1000) NOT NULL,
  `attachment_url` varchar(1000) NOT NULL,
  `template_path` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailQueue`
--

DROP TABLE IF EXISTS `mailQueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailQueue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `from_email` varchar(100) NOT NULL,
  `from_name` varchar(100) NOT NULL,
  `reply_to` varchar(100) NOT NULL,
  `recipient_emails` longtext NOT NULL,
  `cc_emails` varchar(1000) DEFAULT NULL,
  `bcc_emails` varchar(1000) DEFAULT NULL,
  `subject` varchar(1000) NOT NULL,
  `body` longtext NOT NULL,
  `is_html` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `id` int NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(100) DEFAULT NULL COMMENT 'pipe separated list of HTTP Methods (GET|POST)',
  `id_actions` int DEFAULT NULL,
  `id_navigation_section` int DEFAULT NULL,
  `parent` int DEFAULT NULL,
  `is_headless` tinyint(1) NOT NULL DEFAULT '0',
  `nav_position` int DEFAULT NULL,
  `footer_position` int DEFAULT NULL,
  `id_type` int NOT NULL,
  `id_pageAccessTypes` int DEFAULT NULL,
  `is_open_access` tinyint DEFAULT '0',
  `is_system` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2074E5755A93713B` (`keyword`),
  KEY `IDX_2074E575DBD5589F` (`id_actions`),
  KEY `IDX_2074E575E8D3C633` (`id_navigation_section`),
  KEY `IDX_2074E5753D8E604F` (`parent`),
  KEY `IDX_2074E5757FE4B2B` (`id_type`),
  KEY `IDX_2074E57534643D90` (`id_pageAccessTypes`),
  CONSTRAINT `FK_2074E57534643D90` FOREIGN KEY (`id_pageAccessTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2074E5753D8E604F` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2074E5757FE4B2B` FOREIGN KEY (`id_type`) REFERENCES `pageType` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2074E575DBD5589F` FOREIGN KEY (`id_actions`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2074E575E8D3C633` FOREIGN KEY (`id_navigation_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_fields`
--

DROP TABLE IF EXISTS `pages_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_fields` (
  `id_pages` int NOT NULL,
  `id_fields` int NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_pages`,`id_fields`),
  KEY `IDX_D36F9887CEF1A445` (`id_pages`),
  KEY `IDX_D36F988758D25665` (`id_fields`),
  CONSTRAINT `FK_D36F988758D25665` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D36F9887CEF1A445` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_fields_translation`
--

DROP TABLE IF EXISTS `pages_fields_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_fields_translation` (
  `id_pages` int NOT NULL,
  `id_fields` int NOT NULL,
  `id_languages` int NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`id_pages`,`id_fields`,`id_languages`),
  KEY `IDX_903943EECEF1A445` (`id_pages`),
  KEY `IDX_903943EE58D25665` (`id_fields`),
  KEY `IDX_903943EE20E4EF5E` (`id_languages`),
  CONSTRAINT `FK_903943EE20E4EF5E` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_903943EE58D25665` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_903943EECEF1A445` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_sections`
--

DROP TABLE IF EXISTS `pages_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages_sections` (
  `id_pages` int NOT NULL,
  `id_sections` int NOT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`id_pages`,`id_sections`),
  KEY `IDX_6BD95A69CEF1A445` (`id_pages`),
  KEY `IDX_6BD95A697B4DAF0D` (`id_sections`),
  CONSTRAINT `FK_6BD95A697B4DAF0D` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6BD95A69CEF1A445` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageType`
--

DROP TABLE IF EXISTS `pageType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pageType` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AD38E97C5E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageType_fields`
--

DROP TABLE IF EXISTS `pageType_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pageType_fields` (
  `id_pageType` int NOT NULL,
  `id_fields` int NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_pageType`,`id_fields`),
  KEY `IDX_B305C68158D25665` (`id_fields`),
  CONSTRAINT `FK_B305C68158D25665` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B305C681FDE305E9` FOREIGN KEY (`id_pageType`) REFERENCES `pageType` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `version` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `refreshtokens`
--

DROP TABLE IF EXISTS `refreshtokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refreshtokens` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_users` int NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb3_bin NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_BFB6788AFA06E4D9` (`id_users`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs`
--

DROP TABLE IF EXISTS `scheduledJobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_jobTypes` int NOT NULL,
  `id_jobStatus` int NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `date_create` datetime NOT NULL,
  `date_to_be_executed` datetime DEFAULT NULL,
  `date_executed` datetime DEFAULT NULL,
  `config` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_formActions`
--

DROP TABLE IF EXISTS `scheduledJobs_formActions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_formActions` (
  `id_scheduledJobs` int NOT NULL,
  `id_formActions` int NOT NULL,
  `id_dataRows` int DEFAULT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_formActions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_mailQueue`
--

DROP TABLE IF EXISTS `scheduledJobs_mailQueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_mailQueue` (
  `id_scheduledJobs` int NOT NULL,
  `id_mailQueue` int NOT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_mailQueue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_notifications`
--

DROP TABLE IF EXISTS `scheduledJobs_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_notifications` (
  `id_scheduledJobs` int NOT NULL,
  `id_notifications` int NOT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_notifications`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_reminders`
--

DROP TABLE IF EXISTS `scheduledJobs_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_reminders` (
  `id_scheduledJobs` int NOT NULL,
  `id_dataTables` int NOT NULL,
  `session_start_date` datetime DEFAULT NULL,
  `session_end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_dataTables`),
  KEY `IDX_23156A60E2E6A7C3` (`id_dataTables`),
  CONSTRAINT `FK_23156A608030BA52` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_23156A60E2E6A7C3` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_tasks`
--

DROP TABLE IF EXISTS `scheduledJobs_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_tasks` (
  `id_scheduledJobs` int NOT NULL,
  `id_tasks` int NOT NULL,
  PRIMARY KEY (`id_scheduledJobs`,`id_tasks`),
  KEY `IDX_96A54FA8BEDD24A7` (`id_tasks`),
  CONSTRAINT `FK_96A54FA88030BA52` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_96A54FA8BEDD24A7` FOREIGN KEY (`id_tasks`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scheduledJobs_users`
--

DROP TABLE IF EXISTS `scheduledJobs_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduledJobs_users` (
  `id_users` int NOT NULL,
  `id_scheduledJobs` int NOT NULL,
  PRIMARY KEY (`id_users`,`id_scheduledJobs`),
  KEY `IDX_D27E8FD68030BA52` (`id_scheduledJobs`),
  CONSTRAINT `FK_D27E8FD68030BA52` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D27E8FD6FA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_styles` int NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2B964398906D4F18` (`id_styles`),
  CONSTRAINT `FK_2B964398906D4F18` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections_fields_translation`
--

DROP TABLE IF EXISTS `sections_fields_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections_fields_translation` (
  `id_sections` int NOT NULL,
  `id_fields` int NOT NULL,
  `id_languages` int NOT NULL,
  `id_genders` int NOT NULL,
  `content` longtext NOT NULL,
  `meta` varchar(10000) DEFAULT NULL,
  PRIMARY KEY (`id_sections`,`id_fields`,`id_languages`,`id_genders`),
  KEY `IDX_EC5054157B4DAF0D` (`id_sections`),
  KEY `IDX_EC50541558D25665` (`id_fields`),
  KEY `IDX_EC50541520E4EF5E` (`id_languages`),
  KEY `IDX_EC5054155D8601CD` (`id_genders`),
  CONSTRAINT `FK_EC50541520E4EF5E` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC50541558D25665` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC5054155D8601CD` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC5054157B4DAF0D` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections_hierarchy`
--

DROP TABLE IF EXISTS `sections_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections_hierarchy` (
  `parent` int NOT NULL,
  `child` int NOT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `IDX_A6D0AE7C3D8E604F` (`parent`),
  KEY `IDX_A6D0AE7C22B35429` (`child`),
  CONSTRAINT `FK_A6D0AE7C22B35429` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A6D0AE7C3D8E604F` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sections_navigation`
--

DROP TABLE IF EXISTS `sections_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections_navigation` (
  `parent` int NOT NULL,
  `child` int NOT NULL,
  `id_pages` int NOT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `IDX_21BBDC413D8E604F` (`parent`),
  KEY `IDX_21BBDC4122B35429` (`child`),
  KEY `IDX_21BBDC41CEF1A445` (`id_pages`),
  CONSTRAINT `FK_21BBDC4122B35429` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_21BBDC413D8E604F` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_21BBDC41CEF1A445` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styleGroup`
--

DROP TABLE IF EXISTS `styleGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `styleGroup` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int NOT NULL,
  `id_group` int NOT NULL,
  `description` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B65AFAF55E237E06` (`name`),
  KEY `IDX_B65AFAF57FE4B2B` (`id_type`),
  KEY `IDX_B65AFAF5834505F5` (`id_group`),
  CONSTRAINT `FK_B65AFAF57FE4B2B` FOREIGN KEY (`id_type`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B65AFAF5834505F5` FOREIGN KEY (`id_group`) REFERENCES `styleGroup` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styles_fields`
--

DROP TABLE IF EXISTS `styles_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `styles_fields` (
  `id_styles` int NOT NULL,
  `id_fields` int NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  `disabled` tinyint(1) NOT NULL,
  `hidden` int DEFAULT NULL,
  PRIMARY KEY (`id_styles`,`id_fields`),
  KEY `IDX_4F23ED26906D4F18` (`id_styles`),
  KEY `IDX_4F23ED2658D25665` (`id_fields`),
  CONSTRAINT `FK_4F23ED261DF44B12` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4F23ED26D54B526F` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `id` int NOT NULL AUTO_INCREMENT,
  `transaction_time` datetime NOT NULL,
  `id_transactionTypes` int DEFAULT NULL,
  `id_transactionBy` int DEFAULT NULL,
  `id_users` int DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `id_table_name` int DEFAULT NULL,
  `transaction_log` longtext,
  PRIMARY KEY (`id`),
  KEY `IDX_EAA81A4CC41DBD5F` (`id_transactionTypes`),
  KEY `IDX_EAA81A4CFC2E5563` (`id_transactionBy`),
  KEY `IDX_EAA81A4CFA06E4D9` (`id_users`),
  CONSTRAINT `FK_EAA81A4CC41DBD5F` FOREIGN KEY (`id_transactionTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EAA81A4CFA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EAA81A4CFC2E5563` FOREIGN KEY (`id_transactionBy`) REFERENCES `lookups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activity` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_users` int NOT NULL,
  `url` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL,
  `id_type` int NOT NULL,
  `exec_time` decimal(10,8) DEFAULT NULL,
  `keyword` varchar(100) DEFAULT NULL,
  `params` varchar(1000) DEFAULT NULL,
  `mobile` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4CF9ED5AFA06E4D9` (`id_users`),
  KEY `IDX_4CF9ED5A7FE4B2B` (`id_type`),
  CONSTRAINT `FK_4CF9ED5A7FE4B2B` FOREIGN KEY (`id_type`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4CF9ED5AFA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_genders` int DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `id_status` int DEFAULT '1',
  `intern` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(32) DEFAULT NULL,
  `id_languages` int DEFAULT NULL,
  `is_reminded` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` date DEFAULT NULL,
  `last_url` varchar(100) DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `device_token` varchar(200) DEFAULT NULL,
  `security_questions` varchar(1000) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `id_userTypes` int NOT NULL DEFAULT '72',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`),
  UNIQUE KEY `UNIQ_1483A5E924A232CF` (`user_name`),
  KEY `IDX_1483A5E93F6026C1` (`id_userTypes`),
  CONSTRAINT `FK_1483A5E93F6026C1` FOREIGN KEY (`id_userTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_2fa_codes`
--

DROP TABLE IF EXISTS `users_2fa_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_2fa_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_users` int NOT NULL,
  `code` varchar(6) COLLATE utf8mb3_bin NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_65A1E404FA06E4D9` (`id_users`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_groups` (
  `id_users` int NOT NULL,
  `id_groups` int NOT NULL,
  PRIMARY KEY (`id_users`,`id_groups`),
  KEY `IDX_FF8AB7E0FA06E4D9` (`id_users`),
  KEY `IDX_FF8AB7E0D65A8C9D` (`id_groups`),
  CONSTRAINT `FK_FF8AB7E0D65A8C9D` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FF8AB7E0FA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
  `id_users` int DEFAULT NULL,
  `created` datetime NOT NULL,
  `consumed` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `id_groups` int DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `IDX_DBEC45EFA06E4D9` (`id_users`),
  KEY `IDX_DBEC45ED65A8C9D` (`id_groups`),
  CONSTRAINT `FK_DBEC45ED65A8C9D` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DBEC45EFA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `view_acl_groups_pages`
--

DROP TABLE IF EXISTS `view_acl_groups_pages`;
/*!50001 DROP VIEW IF EXISTS `view_acl_groups_pages`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_acl_groups_pages` AS SELECT 
 1 AS `id_groups`,
 1 AS `id_pages`,
 1 AS `acl_select`,
 1 AS `acl_insert`,
 1 AS `acl_update`,
 1 AS `acl_delete`,
 1 AS `keyword`,
 1 AS `url`,
 1 AS `protocol`,
 1 AS `id_actions`,
 1 AS `id_navigation_section`,
 1 AS `parent`,
 1 AS `is_headless`,
 1 AS `nav_position`,
 1 AS `footer_position`,
 1 AS `id_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_acl_users_in_groups_pages`
--

DROP TABLE IF EXISTS `view_acl_users_in_groups_pages`;
/*!50001 DROP VIEW IF EXISTS `view_acl_users_in_groups_pages`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_acl_users_in_groups_pages` AS SELECT 
 1 AS `id_users`,
 1 AS `id_pages`,
 1 AS `acl_select`,
 1 AS `acl_insert`,
 1 AS `acl_update`,
 1 AS `acl_delete`,
 1 AS `keyword`,
 1 AS `url`,
 1 AS `protocol`,
 1 AS `id_actions`,
 1 AS `id_navigation_section`,
 1 AS `parent`,
 1 AS `is_headless`,
 1 AS `nav_position`,
 1 AS `footer_position`,
 1 AS `id_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_acl_users_pages`
--

DROP TABLE IF EXISTS `view_acl_users_pages`;
/*!50001 DROP VIEW IF EXISTS `view_acl_users_pages`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_acl_users_pages` AS SELECT 
 1 AS `id_users`,
 1 AS `id_pages`,
 1 AS `acl_select`,
 1 AS `acl_insert`,
 1 AS `acl_update`,
 1 AS `acl_delete`,
 1 AS `keyword`,
 1 AS `url`,
 1 AS `protocol`,
 1 AS `id_actions`,
 1 AS `id_navigation_section`,
 1 AS `parent`,
 1 AS `is_headless`,
 1 AS `nav_position`,
 1 AS `footer_position`,
 1 AS `id_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_acl_users_union`
--

DROP TABLE IF EXISTS `view_acl_users_union`;
/*!50001 DROP VIEW IF EXISTS `view_acl_users_union`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_acl_users_union` AS SELECT 
 1 AS `id_users`,
 1 AS `id_pages`,
 1 AS `acl_select`,
 1 AS `acl_insert`,
 1 AS `acl_update`,
 1 AS `acl_delete`,
 1 AS `keyword`,
 1 AS `url`,
 1 AS `protocol`,
 1 AS `id_actions`,
 1 AS `id_navigation_section`,
 1 AS `parent`,
 1 AS `is_headless`,
 1 AS `nav_position`,
 1 AS `footer_position`,
 1 AS `id_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_cmspreferences`
--

DROP TABLE IF EXISTS `view_cmspreferences`;
/*!50001 DROP VIEW IF EXISTS `view_cmspreferences`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_cmspreferences` AS SELECT 
 1 AS `callback_api_key`,
 1 AS `default_language_id`,
 1 AS `default_language`,
 1 AS `locale`,
 1 AS `firebase_config`,
 1 AS `anonymous_users`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_datatables`
--

DROP TABLE IF EXISTS `view_datatables`;
/*!50001 DROP VIEW IF EXISTS `view_datatables`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_datatables` AS SELECT 
 1 AS `id`,
 1 AS `name_id`,
 1 AS `name`,
 1 AS `timestamp`,
 1 AS `value`,
 1 AS `text`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_datatables_data`
--

DROP TABLE IF EXISTS `view_datatables_data`;
/*!50001 DROP VIEW IF EXISTS `view_datatables_data`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_datatables_data` AS SELECT 
 1 AS `table_id`,
 1 AS `row_id`,
 1 AS `entry_date`,
 1 AS `col_id`,
 1 AS `table_name`,
 1 AS `col_name`,
 1 AS `value`,
 1 AS `timestamp`,
 1 AS `id_users`,
 1 AS `displayName`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_fields`
--

DROP TABLE IF EXISTS `view_fields`;
/*!50001 DROP VIEW IF EXISTS `view_fields`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_fields` AS SELECT 
 1 AS `field_id`,
 1 AS `field_name`,
 1 AS `display`,
 1 AS `field_type_id`,
 1 AS `field_type`,
 1 AS `position`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_formactions`
--

DROP TABLE IF EXISTS `view_formactions`;
/*!50001 DROP VIEW IF EXISTS `view_formactions`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_formactions` AS SELECT 
 1 AS `id`,
 1 AS `action_name`,
 1 AS `dataTable_name`,
 1 AS `id_formProjectActionTriggerTypes`,
 1 AS `trigger_type`,
 1 AS `trigger_type_code`,
 1 AS `config`,
 1 AS `id_dataTables`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_mailqueue`
--

DROP TABLE IF EXISTS `view_mailqueue`;
/*!50001 DROP VIEW IF EXISTS `view_mailqueue`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_mailqueue` AS SELECT 
 1 AS `id`,
 1 AS `from_email`,
 1 AS `from_name`,
 1 AS `status_code`,
 1 AS `status`,
 1 AS `type_code`,
 1 AS `type`,
 1 AS `date_create`,
 1 AS `date_to_be_executed`,
 1 AS `date_executed`,
 1 AS `reply_to`,
 1 AS `recipient_emails`,
 1 AS `cc_emails`,
 1 AS `bcc_emails`,
 1 AS `subject`,
 1 AS `body`,
 1 AS `is_html`,
 1 AS `id_mailQueue`,
 1 AS `id_jobTypes`,
 1 AS `id_jobStatus`,
 1 AS `config`,
 1 AS `id_dataRows`,
 1 AS `dataTables_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_notifications`
--

DROP TABLE IF EXISTS `view_notifications`;
/*!50001 DROP VIEW IF EXISTS `view_notifications`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_notifications` AS SELECT 
 1 AS `id`,
 1 AS `status_code`,
 1 AS `status`,
 1 AS `type_code`,
 1 AS `type`,
 1 AS `date_create`,
 1 AS `date_to_be_executed`,
 1 AS `date_executed`,
 1 AS `recipient`,
 1 AS `subject`,
 1 AS `body`,
 1 AS `url`,
 1 AS `id_notifications`,
 1 AS `id_jobTypes`,
 1 AS `id_jobStatus`,
 1 AS `config`,
 1 AS `id_dataRows`,
 1 AS `dataTables_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_scheduledjobs`
--

DROP TABLE IF EXISTS `view_scheduledjobs`;
/*!50001 DROP VIEW IF EXISTS `view_scheduledjobs`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_scheduledjobs` AS SELECT 
 1 AS `id`,
 1 AS `status_code`,
 1 AS `status`,
 1 AS `type_code`,
 1 AS `type`,
 1 AS `config`,
 1 AS `date_create`,
 1 AS `date_to_be_executed`,
 1 AS `date_executed`,
 1 AS `description`,
 1 AS `recipient`,
 1 AS `title`,
 1 AS `message`,
 1 AS `id_mailQueue`,
 1 AS `id_jobTypes`,
 1 AS `id_jobStatus`,
 1 AS `id_formActions`,
 1 AS `id_dataRows`,
 1 AS `dataTables_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_scheduledjobs_reminders`
--

DROP TABLE IF EXISTS `view_scheduledjobs_reminders`;
/*!50001 DROP VIEW IF EXISTS `view_scheduledjobs_reminders`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_scheduledjobs_reminders` AS SELECT 
 1 AS `id_scheduledJobs`,
 1 AS `id_dataTables`,
 1 AS `session_start_date`,
 1 AS `session_end_date`,
 1 AS `id_users`,
 1 AS `job_status_code`,
 1 AS `job_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_scheduledjobs_transactions`
--

DROP TABLE IF EXISTS `view_scheduledjobs_transactions`;
/*!50001 DROP VIEW IF EXISTS `view_scheduledjobs_transactions`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_scheduledjobs_transactions` AS SELECT 
 1 AS `id`,
 1 AS `date_create`,
 1 AS `date_to_be_executed`,
 1 AS `date_executed`,
 1 AS `transaction_id`,
 1 AS `transaction_time`,
 1 AS `transaction_type`,
 1 AS `transaction_by`,
 1 AS `user_name`,
 1 AS `transaction_verbal_log`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_sections_fields`
--

DROP TABLE IF EXISTS `view_sections_fields`;
/*!50001 DROP VIEW IF EXISTS `view_sections_fields`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_sections_fields` AS SELECT 
 1 AS `id_sections`,
 1 AS `section_name`,
 1 AS `content`,
 1 AS `meta`,
 1 AS `id_styles`,
 1 AS `style_name`,
 1 AS `id_fields`,
 1 AS `field_name`,
 1 AS `locale`,
 1 AS `gender`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_style_fields`
--

DROP TABLE IF EXISTS `view_style_fields`;
/*!50001 DROP VIEW IF EXISTS `view_style_fields`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_style_fields` AS SELECT 
 1 AS `style_id`,
 1 AS `style_name`,
 1 AS `style_type`,
 1 AS `style_group`,
 1 AS `field_id`,
 1 AS `field_name`,
 1 AS `field_type`,
 1 AS `display`,
 1 AS `position`,
 1 AS `default_value`,
 1 AS `help`,
 1 AS `disabled`,
 1 AS `hidden`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_styles`
--

DROP TABLE IF EXISTS `view_styles`;
/*!50001 DROP VIEW IF EXISTS `view_styles`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_styles` AS SELECT 
 1 AS `style_id`,
 1 AS `style_name`,
 1 AS `style_description`,
 1 AS `style_type_id`,
 1 AS `style_type`,
 1 AS `style_group_id`,
 1 AS `style_group`,
 1 AS `style_group_description`,
 1 AS `style_group_position`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_tasks`
--

DROP TABLE IF EXISTS `view_tasks`;
/*!50001 DROP VIEW IF EXISTS `view_tasks`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_tasks` AS SELECT 
 1 AS `id`,
 1 AS `status_code`,
 1 AS `status`,
 1 AS `type_code`,
 1 AS `type`,
 1 AS `date_create`,
 1 AS `date_to_be_executed`,
 1 AS `date_executed`,
 1 AS `recipient`,
 1 AS `config`,
 1 AS `id_tasks`,
 1 AS `id_jobTypes`,
 1 AS `id_jobStatus`,
 1 AS `description`,
 1 AS `id_dataRows`,
 1 AS `dataTables_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_transactions`
--

DROP TABLE IF EXISTS `view_transactions`;
/*!50001 DROP VIEW IF EXISTS `view_transactions`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_transactions` AS SELECT 
 1 AS `id`,
 1 AS `transaction_time`,
 1 AS `id_transactionTypes`,
 1 AS `transaction_type`,
 1 AS `id_transactionBy`,
 1 AS `transaction_by`,
 1 AS `id_users`,
 1 AS `user_name`,
 1 AS `table_name`,
 1 AS `id_table_name`,
 1 AS `transaction_verbal_log`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_user_codes`
--

DROP TABLE IF EXISTS `view_user_codes`;
/*!50001 DROP VIEW IF EXISTS `view_user_codes`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_user_codes` AS SELECT 
 1 AS `id`,
 1 AS `email`,
 1 AS `name`,
 1 AS `blocked`,
 1 AS `code`,
 1 AS `intern`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_users`
--

DROP TABLE IF EXISTS `view_users`;
/*!50001 DROP VIEW IF EXISTS `view_users`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_users` AS SELECT 
 1 AS `id`,
 1 AS `email`,
 1 AS `name`,
 1 AS `last_login`,
 1 AS `status`,
 1 AS `description`,
 1 AS `blocked`,
 1 AS `code`,
 1 AS `groups`,
 1 AS `user_activity`,
 1 AS `ac`,
 1 AS `intern`,
 1 AS `id_userTypes`,
 1 AS `user_type_code`,
 1 AS `user_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'sym'
--
/*!50003 DROP FUNCTION IF EXISTS `get_field_id` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_field_id`(field varchar(100)) RETURNS int
BEGIN 
	DECLARE field_id INT;    
	SELECT id INTO field_id
	FROM fields
	WHERE name = field COLLATE utf8_unicode_ci;
    RETURN field_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_field_type_id` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_field_type_id`(field_type varchar(100)) RETURNS int
BEGIN 
	DECLARE field_type_id INT;    
	SELECT id INTO field_type_id
	FROM fieldType
	WHERE name = field_type COLLATE utf8_unicode_ci;
    RETURN field_type_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_form_fields_helper` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_form_fields_helper`(form_id_param INT) RETURNS text CHARSET utf8mb3 COLLATE utf8mb3_bin
    READS SQL DATA
    DETERMINISTIC
BEGIN 
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when sft_in.content = "',
		  sft_in.content,
		  '" then value end) as `',
		  replace(sft_in.content, ' ', ''), '`'
		)
	  ) INTO @sql
	from user_input ui
	left join users u on (ui.id_users = u.id)
	left join validation_codes vc on (ui.id_users = vc.id_users)
	left join sections field on (ui.id_sections = field.id)	
	left join user_input_record record  on (ui.id_user_input_record = record.id)
    LEFT JOIN sections form ON (record.id_sections = form.id)
	LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
	LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
    WHERE form.id = form_id_param;
	
    RETURN @sql;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_page_fields_helper` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_page_fields_helper`(page_id INT, language_id INT, default_language_id INT) RETURNS text CHARSET utf8mb3 COLLATE utf8mb3_bin
    READS SQL DATA
    DETERMINISTIC
BEGIN 
    SET @@group_concat_max_len = 32000000;
    SET @sql = NULL;
    SELECT
      GROUP_CONCAT(DISTINCT
        CONCAT(
          'MAX(CASE WHEN f.`name` = "',
          f.`name`,
          '" THEN COALESCE((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' AND content <> "" LIMIT 1), COALESCE((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = (CASE WHEN f.display = 0 THEN 1 ELSE ',default_language_id,' END) LIMIT 1), "")) END) AS `',
          REPLACE(f.`name`, ' ', ''), '`'
        )
      ) INTO @sql
    FROM  pages AS p
    LEFT JOIN pageType_fields AS ptf ON ptf.id_pageType = p.id_type 
    LEFT JOIN fields AS f ON f.id = ptf.id_fields
    WHERE p.id = page_id OR page_id = -1;
    
    RETURN @sql;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_sections_fields_helper` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_sections_fields_helper`(section_id INT, language_id INT, gender_id INT) RETURNS text CHARSET utf8mb3 COLLATE utf8mb3_bin
    READS SQL DATA
    DETERMINISTIC
BEGIN 
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when f.`name` = "',
		  f.`name`,
		  '" then sft.content end) as `',
		  replace(f.`name`, ' ', ''), '`'
		)
	  ) INTO @sql
	from  sections AS s
	LEFT JOIN sections_fields_translation AS sft ON sft.id_sections = s.id AND (language_id = sft.id_languages OR sft.id_languages = 1) AND (sft.id_genders = gender_id)
	LEFT JOIN fields AS f ON f.id = sft.id_fields
    WHERE s.id = section_id OR section_id = -1;
	
    RETURN @sql;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_style_group_id` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_style_group_id`(style_group varchar(100)) RETURNS int
BEGIN 
	DECLARE style_group_id INT;    
	SELECT id INTO style_group_id
	FROM styleGroup
	WHERE name = style_group COLLATE utf8_unicode_ci;
    RETURN style_group_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_style_id` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_style_id`(style varchar(100)) RETURNS int
BEGIN 
	DECLARE style_id INT;    
	SELECT id INTO style_id
	FROM styles
	WHERE name = style COLLATE utf8_unicode_ci;
    RETURN style_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_foreign_key` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_foreign_key`(param_table VARCHAR(100), fk_name VARCHAR(100), fk_column VARCHAR(100), fk_references VARCHAR(200))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) > 0,
        "SELECT 'The foreign key already exists in the table'",
        CONCAT('ALTER TABLE ', param_table, ' ADD CONSTRAINT ', fk_name, ' FOREIGN KEY (', fk_column, ') REFERENCES ', fk_references, ' ON DELETE CASCADE;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_index` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_index`(
    param_table VARCHAR(100), 
    param_index_name VARCHAR(100), 
    param_index_column VARCHAR(1000),
    param_is_unique BOOLEAN
)
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,
        "SELECT 'The index already exists in the table'",
        CONCAT(
            'CREATE ', 
            IF(param_is_unique, 'UNIQUE ', ''),
            'INDEX ', 
            param_index_name, 
            ' ON `', 
            param_table, 
            '` (`', 
            param_index_column, 
            '`);'
        )
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_primary_key` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_primary_key`(
  IN `param_table`   VARCHAR(100),
  IN `param_columns` VARCHAR(500)  -- e.g. 'col1, col2'
)
BEGIN
  DECLARE cnt INT DEFAULT 0;

  -- Check if a PRIMARY KEY already exists on the table
  SELECT COUNT(*) INTO cnt
    FROM information_schema.TABLE_CONSTRAINTS
   WHERE table_schema    = DATABASE()
     AND table_name      = param_table
     AND constraint_type = 'PRIMARY KEY';

  -- Build the appropriate statement
  IF cnt = 0 THEN
    SET @sqlstmt = CONCAT(
      'ALTER TABLE `', param_table,
      '` ADD PRIMARY KEY (', param_columns, ');'
    );
  ELSE
    SET @sqlstmt = "SELECT 'Primary key already exists on table.'";
  END IF;

  -- Execute it
  PREPARE st FROM @sqlstmt;
  EXECUTE st;
  DEALLOCATE PREPARE st;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_table_column` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_table_column`(
    IN param_table VARCHAR(100), 
    IN param_column VARCHAR(100), 
    IN param_column_type VARCHAR(500)
)
BEGIN
    SET @sqlstmt = (
        SELECT IF(
            (
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS
                WHERE `table_schema` = DATABASE()
                AND `table_name` = param_table
                AND `COLUMN_NAME` = param_column 
            ) > 0,
            "SELECT 'Column already exists in the table'",
            CONCAT('ALTER TABLE `', param_table, '` ADD COLUMN `', param_column, '` ', param_column_type, ';')
        )
    );

    PREPARE st FROM @sqlstmt;
    EXECUTE st;
    DEALLOCATE PREPARE st;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_unique_key` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_unique_key`(param_table VARCHAR(100), param_index VARCHAR(100), param_column VARCHAR(100))
BEGIN
    IF NOT EXISTS 
	(
		SELECT NULL 
		FROM information_schema.STATISTICS
		WHERE `table_schema` = DATABASE()
		AND `table_name` = param_table
		AND `index_name` = param_index 
	) THEN    
		SET @sqlstmt = CONCAT('ALTER TABLE ', param_table, ' ADD UNIQUE KEY ', param_index, ' (', param_column, ');');
		PREPARE st FROM @sqlstmt;
        EXECUTE st;
        DEALLOCATE PREPARE st;	
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `drop_foreign_key` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `drop_foreign_key`(param_table VARCHAR(100), fk_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) = 0,
        "SELECT 'Foreign key does not exist'",
        CONCAT('ALTER TABLE `', param_table, '` DROP FOREIGN KEY ', fk_name, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `drop_index` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `drop_index`(param_table VARCHAR(100), param_index_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,        
        CONCAT('ALTER TABLE `', param_table, '` DROP INDEX ', param_index_name),
        "SELECT 'The index does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `drop_table_column` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `drop_table_column`(param_table VARCHAR(100), param_column VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*) 
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_column 
		) = 0,
        "SELECT 'Column does not exist'",
        CONCAT('ALTER TABLE `', param_table, '` DROP COLUMN `', param_column, '` ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_dataTable_with_filter` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_dataTable_with_filter`( 
	IN table_id_param INT, 
	IN user_id_param INT, 
	IN filter_param VARCHAR(1000),
	IN exclude_deleted_param BOOLEAN -- If true it will exclude the deleted records and it will not return them
)
    READS SQL DATA
    DETERMINISTIC
BEGIN
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	GROUP_CONCAT(DISTINCT
		CONCAT(
			'MAX(CASE WHEN col.`name` = "',
				col.name,
				'" THEN `value` END) AS `',
			replace(col.name, ' ', ''), '`'
		)
	) INTO @sql
	FROM  dataTables t
	INNER JOIN dataCols col on (t.id = col.id_dataTables)
	WHERE t.id = table_id_param AND col.`name` NOT IN ('id_users','record_id','user_name','id_actionTriggerTypes','triggerType', 'entry_date', 'user_code');

	IF (@sql is null) THEN
		SELECT `name` from view_dataTables where 1=2;
	ELSE
		BEGIN
			SET @user_filter = '';
			IF user_id_param > 0 THEN
				SET @user_filter = CONCAT(' AND r.id_users = ', user_id_param);
			END IF;	
			
			SET @time_period_filter = '';
			CASE 
				WHEN filter_param LIKE '%LAST_HOUR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 HOUR';
				WHEN filter_param LIKE '%LAST_DAY%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 DAY';
				WHEN filter_param LIKE '%LAST_WEEK%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 WEEK';
				WHEN filter_param LIKE '%LAST_MONTH%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 MONTH';
				WHEN filter_param LIKE '%LAST_YEAR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 YEAR';
				ELSE
					SET @time_period_filter = '';					
			END CASE;
			
			SET @exclude_deleted_filter = '';
			CASE 
				WHEN exclude_deleted_param = TRUE THEN
					SET @exclude_deleted_filter = CONCAT(' AND IFNULL(r.id_actionTriggerTypes, 0) <> ', (SELECT id FROM lookups WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'deleted' LIMIT 0,1));				
				ELSE
					SET @exclude_deleted_filter = '';					
			END CASE;
			
			SET @sql = CONCAT('SELECT * FROM (SELECT r.id AS record_id, 
					r.`timestamp` AS entry_date, r.id_users, u.`name` AS user_name, vc.code AS user_code, r.id_actionTriggerTypes, l.lookup_code AS triggerType,', @sql, 
					' FROM dataTables t
					INNER JOIN dataRows r ON (t.id = r.id_dataTables)
					INNER JOIN dataCells cell ON (cell.id_dataRows = r.id)
					INNER JOIN dataCols col ON (col.id = cell.id_dataCols)
					LEFT JOIN users u ON (r.id_users = u.id)
					LEFT JOIN validation_codes vc ON (u.id = vc.id_users)
					LEFT JOIN lookups l ON (l.id = r.id_actionTriggerTypes)
					WHERE t.id = ', table_id_param, @user_filter, @time_period_filter, @exclude_deleted_filter, 
					' GROUP BY r.id ) AS r WHERE 1=1  ', filter_param);
			-- select @sql;
			PREPARE stmt FROM @sql;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		END;
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_group_acl` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_group_acl`( param_group_id INT, param_page_id INT )
BEGIN

    SELECT acl.id_groups, acl.id_pages, 
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type
	FROM acl_groups acl
	INNER JOIN pages p ON (acl.id_pages = p.id or (p.id_type = 4 and acl.id_pages = null)) -- add all open pages although that there is no specific ACL
    WHERE acl.id_groups = param_group_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_navigation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_navigation`( param_locale VARCHAR(10) )
BEGIN

    SELECT Json_arrayagg(Json_object(keyword, (SELECT 
						 Json_object('id_navigation_section' 
						 , 
						 p.id_navigation_section, 'title', 
						 pft.content, 'children', (SELECT 
						 Json_arrayagg( 
						 Json_object(keyword, (SELECT 
												 Json_object('id_navigation_section' 
												 , 
												 p2.id_navigation_section, 'title', 
												 pft2.content, 'children', NULL)))) 
						 AS items 
												 FROM   pages AS p2 
												 LEFT JOIN pages_fields_translation 
														   AS pft2 
												 ON pft2.id_pages = p2.id 
												 LEFT JOIN languages AS l2 
												 ON l2.id = pft2.id_languages 
												 LEFT JOIN fields AS f2 
												 ON f2.id = pft2.id_fields 
												 WHERE  p2.parent = p.id 
												 AND ( l.locale = param_locale 
												 OR l.locale = 'all' ) 
												 AND f2.`name` = 'label' 
												 AND p2.nav_position IS NOT NULL 
												 ORDER  BY p2.nav_position ASC))))) AS 
		   pages 
	FROM   pages AS p 
		   LEFT JOIN pages_fields_translation AS pft 
				  ON pft.id_pages = p.id 
		   LEFT JOIN languages AS l 
				  ON l.id = pft.id_languages 
		   LEFT JOIN fields AS f 
				  ON f.id = pft.id_fields 
	WHERE  p.nav_position IS NOT NULL 
		   AND ( l.locale = param_locale 
				  OR l.locale = 'all' ) 
		   AND f.`name` = 'label' 
		   AND p.parent IS NULL 
ORDER  BY p.nav_position DESC;
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_page_fields` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_page_fields`(
    IN page_id INT,
    IN language_id INT,
    IN default_language_id INT,
    IN filter_param VARCHAR(1000),
    IN order_param VARCHAR(1000)
)
    READS SQL DATA
    DETERMINISTIC
BEGIN  
    -- page_id = -1 returns all pages
    SET @@group_concat_max_len = 32000000;

    SELECT get_page_fields_helper(page_id, language_id, default_language_id) 
      INTO @sql;    
    
    IF @sql IS NULL THEN    
        SELECT * 
          FROM pages 
         WHERE 1=2;
    ELSE 
        BEGIN
            SET @sql = CONCAT(
                'SELECT 
                    p.id,
                    p.keyword,
                    p.url,
                    p.protocol,
                    p.id_actions,
                    "select" AS access_level,
                    p.id_navigation_section,
                    p.parent,
                    p.is_headless,
                    p.nav_position,
                    p.footer_position,
                    p.id_type,
                    p.id_pageAccessTypes,
                    a.lookup_code AS `action`, ',
                 @sql, '
                 FROM pages p
                 LEFT JOIN lookups AS a 
                   ON a.id = p.id_actions 
                  AND a.type_code = "pageActions"
                 LEFT JOIN pageType_fields AS ptf 
                   ON ptf.id_pageType = p.id_type 
                 LEFT JOIN fields AS f 
                   ON f.id = ptf.id_fields
                 WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ')
                 GROUP BY 
                   p.id, p.keyword, p.url, p.protocol, p.id_actions,
                   p.id_navigation_section, p.parent, p.is_headless,
                   p.nav_position, p.footer_position, p.id_type,
                   p.id_pageAccessTypes, a.lookup_code
                 HAVING 1 ', filter_param
            );
            
            IF order_param <> '' THEN             
                SET @sql = CONCAT(
                    'SELECT * FROM (',
                    @sql,
                    ') AS t ', order_param
                );
            END IF;

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_page_sections_hierarchical` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_page_sections_hierarchical`(IN page_id INT)
BEGIN
    WITH RECURSIVE section_hierarchy AS (
        -- Base case: get top-level sections for the page
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            ps.`position`,
            0 AS `level`,
            CAST(s.id AS CHAR(200)) AS `path`
        FROM pages_sections ps
        JOIN sections s ON ps.id_sections = s.id
        JOIN styles st ON s.id_styles = st.id
        LEFT JOIN sections_hierarchy sh ON s.id = sh.child
        WHERE ps.id_pages = page_id
        AND sh.parent IS NULL
        
        UNION ALL
        
        -- Recursive case: get children of sections
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            sh.position,
            h.`level` + 1,
            CONCAT(h.`path`, ',', s.id) AS `path`
        FROM section_hierarchy h
        JOIN sections_hierarchy sh ON h.id = sh.parent
        JOIN sections s ON sh.child = s.id
        JOIN styles st ON s.id_styles = st.id
    )
    
    -- Select the result
    SELECT 
        id,
        `name`,
        id_styles,
        style_name,
        position,
        `level`,
        `path`
    FROM section_hierarchy
    ORDER BY `path`, `position`;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_sections_fields` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_sections_fields`( section_id INT, language_id INT, gender_id INT, filter_param VARCHAR(1000), order_param VARCHAR(1000))
    READS SQL DATA
    DETERMINISTIC
BEGIN  
	-- section_id -1 returns all sections
    SET @@group_concat_max_len = 32000000;
	SELECT get_sections_fields_helper(section_id, language_id, gender_id) INTO @sql;	
	
    IF (@sql is null) THEN	
        SELECT * FROM sections WHERE 1=2;
    ELSE 
		BEGIN
		SET @sql = CONCAT(
			'SELECT s.id AS section_id, s.name AS section_name, st.id AS style_id, st.name AS style_name, ', 
			@sql, 
			'FROM sections s
            INNER JOIN styles st ON (s.id_styles = st.id)
			LEFT JOIN sections_fields_translation AS sft ON sft.id_sections = s.id   
			LEFT JOIN fields AS f ON sft.id_fields = f.id
			WHERE (s.id = ', section_id, ' OR -1 = ', section_id, ') AND ( IFNULL(id_languages, 1) = 1 OR id_languages=', language_id, ') 
            GROUP BY s.id, s.name, st.id, st.name HAVING 1 ', filter_param
        );
        
        IF (order_param <> '') THEN	        
			SET @sql = concat(
				'SELECT * FROM (',
				@sql,
				') AS t ', order_param
			);
		END IF;

		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_user_acl` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_user_acl`(
    IN param_user_id INT,
    IN param_page_id INT  -- -1 means “all pages”
)
BEGIN

    SELECT
        param_user_id  AS id_users,
        id_pages,
        MAX(acl_select) AS acl_select,
        MAX(acl_insert) AS acl_insert,
        MAX(acl_update) AS acl_update,
        MAX(acl_delete) AS acl_delete,
        keyword,
        url,
        protocol,
        id_actions,
        id_navigation_section,
        parent,
        is_headless,
        nav_position,
        footer_position,
        id_type,
        id_pageAccessTypes
    FROM
    (
        -- 1) Group‐based ACL
        SELECT
            ug.id_users,
            acl.id_pages,
            acl.acl_select,
            acl.acl_insert,
            acl.acl_update,
            acl.acl_delete,
            p.keyword,
            p.url,
            p.protocol,
            p.id_actions,
            p.id_navigation_section,
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,
            p.id_type,
            p.id_pageAccessTypes
        FROM users_groups ug
        JOIN users u             ON ug.id_users   = u.id
        JOIN acl_groups acl      ON acl.id_groups = ug.id_groups
        JOIN pages p             ON p.id           = acl.id_pages
        WHERE ug.id_users = param_user_id
          AND (param_page_id = -1 OR acl.id_pages = param_page_id)

        UNION ALL

        -- 2) User‐specific ACL
        SELECT
            acl.id_users,
            acl.id_pages,
            acl.acl_select,
            acl.acl_insert,
            acl.acl_update,
            acl.acl_delete,
            p.keyword,
            p.url,
            p.protocol,
            p.id_actions,
            p.id_navigation_section,
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,
            p.id_type,
            p.id_pageAccessTypes
        FROM acl_users acl
        JOIN pages p ON p.id = acl.id_pages
        WHERE acl.id_users = param_user_id
          AND (param_page_id = -1 OR acl.id_pages = param_page_id)

        UNION ALL

        -- 3) Open-access pages (only all if param_page_id = -1, or just that page if it’s open)
        SELECT
            param_user_id       AS id_users,
            p.id                AS id_pages,
            1                   AS acl_select,
            0                   AS acl_insert,
            0                   AS acl_update,
            0                   AS acl_delete,
            p.keyword,
            p.url,
            p.protocol,
            p.id_actions,
            p.id_navigation_section,
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,
            p.id_type,
            p.id_pageAccessTypes
        FROM pages p
        WHERE p.is_open_access = 1
          AND (param_page_id = -1 OR p.id = param_page_id)

    ) AS combined_acl
    GROUP BY
        id_pages,
        keyword,
        url,
        protocol,
        id_actions,
        id_navigation_section,
        parent,
        is_headless,
        nav_position,
        footer_position,
        id_type,
        id_pageAccessTypes;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `rename_table` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `rename_table`(param_old_table_name VARCHAR(100), param_new_table_name VARCHAR(100))
BEGIN	
	DECLARE tableExists INT;
	SELECT COUNT(*) 
			INTO tableExists
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_old_table_name; 
	SET @sqlstmt = (SELECT IF(
		tableExists > 0,        
		CONCAT('RENAME TABLE ', param_old_table_name, ' TO ', param_new_table_name),
		"SELECT 'Table does not exists in the table'"
	));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `rename_table_column` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `rename_table_column`(param_table VARCHAR(100), param_old_column_name VARCHAR(100), param_new_column_name VARCHAR(100))
BEGIN	
	DECLARE columnExists INT;
	DECLARE columnType VARCHAR(255);
	SELECT COUNT(*), COLUMN_TYPE 
			INTO columnExists, columnType
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_old_column_name; 
	SET @sqlstmt = (SELECT IF(
		columnExists > 0,        
		CONCAT('ALTER TABLE ', param_table, ' CHANGE COLUMN ', param_old_column_name, ' ', param_new_column_name, ' ', columnType, ';'),
		"SELECT 'Column does not exists in the table'"
	));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_formId_reminders` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_formId_reminders`()
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE record_id INT;
	DECLARE json_data JSON;
	DECLARE block_index INT DEFAULT 0;
	DECLARE job_index INT DEFAULT 0;
	DECLARE reminder_form_id VARCHAR(255);
	DECLARE new_id INT;

	-- Declare cursor for iterating over records
	DECLARE cur CURSOR FOR
		SELECT id, config FROM formActions FOR UPDATE;

	-- Declare handler to handle end of data
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	-- Open the cursor
	OPEN cur;

	-- Loop through the records
	read_loop: LOOP
		FETCH cur INTO record_id, json_data;
		IF done THEN
			LEAVE read_loop;
		END IF;

		-- Loop through the blocks and jobs to update the reminder_form_id
		SET block_index = 0;
		WHILE JSON_LENGTH(json_data, '$.blocks') > block_index DO
			SET job_index = 0;
			WHILE JSON_LENGTH(json_data, CONCAT('$.blocks[', block_index, '].jobs')) > job_index DO
				SET reminder_form_id = JSON_UNQUOTE(JSON_EXTRACT(json_data, CONCAT('$.blocks[', block_index, '].jobs[', job_index, '].reminder_form_id')));
				
				IF reminder_form_id LIKE '%-INTERNAL' THEN
					-- Handle -INTERNAL case
					SET reminder_form_id = SUBSTRING_INDEX(reminder_form_id, '-', 1);
					SELECT LPAD(id, 10, '0') INTO new_id FROM dataTables WHERE CAST(name AS UNSIGNED) = CAST(reminder_form_id AS UNSIGNED) LIMIT 1;
					SET json_data = JSON_SET(json_data, CONCAT('$.blocks[', block_index, '].jobs[', job_index, '].reminder_form_id'), CAST(new_id AS CHAR));
					
				ELSEIF reminder_form_id LIKE '%-EXTERNAL' THEN
					-- Handle -EXTERNAL case
					SET reminder_form_id = SUBSTRING_INDEX(reminder_form_id, '-', 1);
					SET new_id = LPAD(reminder_form_id, 10, '0');
					SET json_data = JSON_SET(json_data, CONCAT('$.blocks[', block_index, '].jobs[', job_index, '].reminder_form_id'), new_id);
				END IF;
				
				SET job_index = job_index + 1;
			END WHILE;
			SET block_index = block_index + 1;
		END WHILE;

		-- Update the JSON back to the table
		UPDATE formActions SET config = json_data WHERE id = record_id;
	END LOOP;

	-- Close the cursor
	CLOSE cur;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `view_acl_groups_pages`
--

/*!50001 DROP VIEW IF EXISTS `view_acl_groups_pages`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_acl_groups_pages` AS select `acl`.`id_groups` AS `id_groups`,`acl`.`id_pages` AS `id_pages`,(case when (`p`.`id_type` = 4) then 1 else `acl`.`acl_select` end) AS `acl_select`,`acl`.`acl_insert` AS `acl_insert`,`acl`.`acl_update` AS `acl_update`,`acl`.`acl_delete` AS `acl_delete`,`p`.`keyword` AS `keyword`,`p`.`url` AS `url`,`p`.`protocol` AS `protocol`,`p`.`id_actions` AS `id_actions`,`p`.`id_navigation_section` AS `id_navigation_section`,`p`.`parent` AS `parent`,`p`.`is_headless` AS `is_headless`,`p`.`nav_position` AS `nav_position`,`p`.`footer_position` AS `footer_position`,`p`.`id_type` AS `id_type` from (`acl_groups` `acl` join `pages` `p` on(((`acl`.`id_pages` = `p`.`id`) or ((`p`.`id_type` = 4) and (`acl`.`id_pages` = NULL))))) group by `acl`.`id_groups`,`acl`.`id_pages`,`acl`.`acl_select`,`acl`.`acl_insert`,`acl`.`acl_update`,`acl`.`acl_delete`,`p`.`keyword`,`p`.`url`,`p`.`protocol`,`p`.`id_actions`,`p`.`id_navigation_section`,`p`.`parent`,`p`.`is_headless`,`p`.`nav_position`,`p`.`footer_position`,`p`.`id_type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_acl_users_in_groups_pages`
--

/*!50001 DROP VIEW IF EXISTS `view_acl_users_in_groups_pages`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_acl_users_in_groups_pages` AS select `ug`.`id_users` AS `id_users`,`acl`.`id_pages` AS `id_pages`,max(ifnull(`acl`.`acl_select`,0)) AS `acl_select`,max(ifnull(`acl`.`acl_insert`,0)) AS `acl_insert`,max(ifnull(`acl`.`acl_update`,0)) AS `acl_update`,max(ifnull(`acl`.`acl_delete`,0)) AS `acl_delete`,`p`.`keyword` AS `keyword`,`p`.`url` AS `url`,`p`.`protocol` AS `protocol`,`p`.`id_actions` AS `id_actions`,`p`.`id_navigation_section` AS `id_navigation_section`,`p`.`parent` AS `parent`,`p`.`is_headless` AS `is_headless`,`p`.`nav_position` AS `nav_position`,`p`.`footer_position` AS `footer_position`,`p`.`id_type` AS `id_type` from (((`users` `u` join `users_groups` `ug` on((`ug`.`id_users` = `u`.`id`))) join `acl_groups` `acl` on((`acl`.`id_groups` = `ug`.`id_groups`))) join `pages` `p` on((`acl`.`id_pages` = `p`.`id`))) group by `ug`.`id_users`,`acl`.`id_pages`,`p`.`keyword`,`p`.`url`,`p`.`protocol`,`p`.`id_actions`,`p`.`id_navigation_section`,`p`.`parent`,`p`.`is_headless`,`p`.`nav_position`,`p`.`footer_position`,`p`.`id_type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_acl_users_pages`
--

/*!50001 DROP VIEW IF EXISTS `view_acl_users_pages`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_acl_users_pages` AS select `acl`.`id_users` AS `id_users`,`acl`.`id_pages` AS `id_pages`,(case when (`p`.`id_type` = 4) then 1 else `acl`.`acl_select` end) AS `acl_select`,`acl`.`acl_insert` AS `acl_insert`,`acl`.`acl_update` AS `acl_update`,`acl`.`acl_delete` AS `acl_delete`,`p`.`keyword` AS `keyword`,`p`.`url` AS `url`,`p`.`protocol` AS `protocol`,`p`.`id_actions` AS `id_actions`,`p`.`id_navigation_section` AS `id_navigation_section`,`p`.`parent` AS `parent`,`p`.`is_headless` AS `is_headless`,`p`.`nav_position` AS `nav_position`,`p`.`footer_position` AS `footer_position`,`p`.`id_type` AS `id_type` from (`acl_users` `acl` join `pages` `p` on((`acl`.`id_pages` = `p`.`id`))) group by `acl`.`id_users`,`acl`.`id_pages`,`acl`.`acl_select`,`acl`.`acl_insert`,`acl`.`acl_update`,`acl`.`acl_delete`,`p`.`keyword`,`p`.`url`,`p`.`protocol`,`p`.`id_actions`,`p`.`id_navigation_section`,`p`.`parent`,`p`.`is_headless`,`p`.`nav_position`,`p`.`footer_position`,`p`.`id_type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_acl_users_union`
--

/*!50001 DROP VIEW IF EXISTS `view_acl_users_union`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_acl_users_union` AS select `view_acl_users_in_groups_pages`.`id_users` AS `id_users`,`view_acl_users_in_groups_pages`.`id_pages` AS `id_pages`,`view_acl_users_in_groups_pages`.`acl_select` AS `acl_select`,`view_acl_users_in_groups_pages`.`acl_insert` AS `acl_insert`,`view_acl_users_in_groups_pages`.`acl_update` AS `acl_update`,`view_acl_users_in_groups_pages`.`acl_delete` AS `acl_delete`,`view_acl_users_in_groups_pages`.`keyword` AS `keyword`,`view_acl_users_in_groups_pages`.`url` AS `url`,`view_acl_users_in_groups_pages`.`protocol` AS `protocol`,`view_acl_users_in_groups_pages`.`id_actions` AS `id_actions`,`view_acl_users_in_groups_pages`.`id_navigation_section` AS `id_navigation_section`,`view_acl_users_in_groups_pages`.`parent` AS `parent`,`view_acl_users_in_groups_pages`.`is_headless` AS `is_headless`,`view_acl_users_in_groups_pages`.`nav_position` AS `nav_position`,`view_acl_users_in_groups_pages`.`footer_position` AS `footer_position`,`view_acl_users_in_groups_pages`.`id_type` AS `id_type` from `view_acl_users_in_groups_pages` union select `view_acl_users_pages`.`id_users` AS `id_users`,`view_acl_users_pages`.`id_pages` AS `id_pages`,`view_acl_users_pages`.`acl_select` AS `acl_select`,`view_acl_users_pages`.`acl_insert` AS `acl_insert`,`view_acl_users_pages`.`acl_update` AS `acl_update`,`view_acl_users_pages`.`acl_delete` AS `acl_delete`,`view_acl_users_pages`.`keyword` AS `keyword`,`view_acl_users_pages`.`url` AS `url`,`view_acl_users_pages`.`protocol` AS `protocol`,`view_acl_users_pages`.`id_actions` AS `id_actions`,`view_acl_users_pages`.`id_navigation_section` AS `id_navigation_section`,`view_acl_users_pages`.`parent` AS `parent`,`view_acl_users_pages`.`is_headless` AS `is_headless`,`view_acl_users_pages`.`nav_position` AS `nav_position`,`view_acl_users_pages`.`footer_position` AS `footer_position`,`view_acl_users_pages`.`id_type` AS `id_type` from `view_acl_users_pages` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_cmspreferences`
--

/*!50001 DROP VIEW IF EXISTS `view_cmspreferences`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_cmspreferences` AS select `p`.`callback_api_key` AS `callback_api_key`,`p`.`default_language_id` AS `default_language_id`,`l`.`language` AS `default_language`,`l`.`locale` AS `locale`,`p`.`firebase_config` AS `firebase_config`,`p`.`anonymous_users` AS `anonymous_users` from (`cmspreferences` `p` left join `languages` `l` on((`l`.`id` = `p`.`default_language_id`))) where (`p`.`id` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_datatables`
--

/*!50001 DROP VIEW IF EXISTS `view_datatables`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_datatables` AS select `datatables`.`id` AS `id`,`datatables`.`name` AS `name_id`,(case when (ifnull(`datatables`.`displayName`,'') = '') then `datatables`.`name` else `datatables`.`displayName` end) AS `name`,`datatables`.`timestamp` AS `timestamp`,`datatables`.`id` AS `value`,(case when (ifnull(`datatables`.`displayName`,'') = '') then `datatables`.`name` else `datatables`.`displayName` end) AS `text` from `datatables` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_datatables_data`
--

/*!50001 DROP VIEW IF EXISTS `view_datatables_data`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_datatables_data` AS select `t`.`id` AS `table_id`,`r`.`id` AS `row_id`,`r`.`timestamp` AS `entry_date`,`col`.`id` AS `col_id`,`t`.`name` AS `table_name`,`col`.`name` AS `col_name`,`cell`.`value` AS `value`,`t`.`timestamp` AS `timestamp`,`r`.`id_users` AS `id_users`,`t`.`displayName` AS `displayName` from (((`datatables` `t` left join `datarows` `r` on((`t`.`id` = `r`.`id_dataTables`))) left join `datacells` `cell` on((`cell`.`id_dataRows` = `r`.`id`))) left join `datacols` `col` on((`col`.`id` = `cell`.`id_dataCols`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_fields`
--

/*!50001 DROP VIEW IF EXISTS `view_fields`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_fields` AS select cast(`f`.`id` as unsigned) AS `field_id`,`f`.`name` AS `field_name`,`f`.`display` AS `display`,cast(`ft`.`id` as unsigned) AS `field_type_id`,`ft`.`name` AS `field_type`,`ft`.`position` AS `position` from (`fields` `f` left join `fieldtype` `ft` on((`f`.`id_type` = `ft`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_formactions`
--

/*!50001 DROP VIEW IF EXISTS `view_formactions`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_formactions` AS select `fa`.`id` AS `id`,`fa`.`name` AS `action_name`,`dt`.`name` AS `dataTable_name`,`fa`.`id_formProjectActionTriggerTypes` AS `id_formProjectActionTriggerTypes`,`trig`.`lookup_value` AS `trigger_type`,`trig`.`lookup_code` AS `trigger_type_code`,`fa`.`config` AS `config`,`dt`.`id` AS `id_dataTables` from ((`formactions` `fa` join `lookups` `trig` on((`trig`.`id` = `fa`.`id_formProjectActionTriggerTypes`))) left join `view_datatables` `dt` on((`dt`.`id` = `fa`.`id_dataTables`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_mailqueue`
--

/*!50001 DROP VIEW IF EXISTS `view_mailqueue`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_mailqueue` AS select `sj`.`id` AS `id`,`mq`.`from_email` AS `from_email`,`mq`.`from_name` AS `from_name`,`sj`.`status_code` AS `status_code`,`sj`.`status` AS `status`,`sj`.`type_code` AS `type_code`,`sj`.`type` AS `type`,`sj`.`date_create` AS `date_create`,`sj`.`date_to_be_executed` AS `date_to_be_executed`,`sj`.`date_executed` AS `date_executed`,`mq`.`reply_to` AS `reply_to`,`mq`.`recipient_emails` AS `recipient_emails`,`mq`.`cc_emails` AS `cc_emails`,`mq`.`bcc_emails` AS `bcc_emails`,`mq`.`subject` AS `subject`,`mq`.`body` AS `body`,`mq`.`is_html` AS `is_html`,`mq`.`id` AS `id_mailQueue`,`sj`.`id_jobTypes` AS `id_jobTypes`,`sj`.`id_jobStatus` AS `id_jobStatus`,`sj`.`config` AS `config`,`sj`.`id_dataRows` AS `id_dataRows`,`sj`.`dataTables_name` AS `dataTables_name` from ((`mailqueue` `mq` join `scheduledjobs_mailqueue` `sj_mq` on((`sj_mq`.`id_mailQueue` = `mq`.`id`))) join `view_scheduledjobs` `sj` on((`sj`.`id` = `sj_mq`.`id_scheduledJobs`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_notifications`
--

/*!50001 DROP VIEW IF EXISTS `view_notifications`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_notifications` AS select `sj`.`id` AS `id`,`sj`.`status_code` AS `status_code`,`sj`.`status` AS `status`,`sj`.`type_code` AS `type_code`,`sj`.`type` AS `type`,`sj`.`date_create` AS `date_create`,`sj`.`date_to_be_executed` AS `date_to_be_executed`,`sj`.`date_executed` AS `date_executed`,`sj`.`recipient` AS `recipient`,`n`.`subject` AS `subject`,`n`.`body` AS `body`,`n`.`url` AS `url`,`sj_n`.`id_notifications` AS `id_notifications`,`sj`.`id_jobTypes` AS `id_jobTypes`,`sj`.`id_jobStatus` AS `id_jobStatus`,`sj`.`config` AS `config`,`sj`.`id_dataRows` AS `id_dataRows`,`sj`.`dataTables_name` AS `dataTables_name` from ((`notifications` `n` join `scheduledjobs_notifications` `sj_n` on((`sj_n`.`id_notifications` = `n`.`id`))) join `view_scheduledjobs` `sj` on((`sj`.`id` = `sj_n`.`id_scheduledJobs`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_scheduledjobs`
--

/*!50001 DROP VIEW IF EXISTS `view_scheduledjobs`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_scheduledjobs` AS select `sj`.`id` AS `id`,`l_status`.`lookup_code` AS `status_code`,`l_status`.`lookup_value` AS `status`,`l_types`.`lookup_code` AS `type_code`,`l_types`.`lookup_value` AS `type`,`sj`.`config` AS `config`,`sj`.`date_create` AS `date_create`,`sj`.`date_to_be_executed` AS `date_to_be_executed`,`sj`.`date_executed` AS `date_executed`,`sj`.`description` AS `description`,(case when (`l_types`.`lookup_code` = 'email') then `mq`.`recipient_emails` when (`l_types`.`lookup_code` = 'notification') then '' when (`l_types`.`lookup_code` = 'task') then '' else '' end) AS `recipient`,(case when (`l_types`.`lookup_code` = 'email') then `mq`.`subject` when (`l_types`.`lookup_code` = 'notification') then `n`.`subject` else '' end) AS `title`,(case when (`l_types`.`lookup_code` = 'email') then `mq`.`body` when (`l_types`.`lookup_code` = 'notification') then `n`.`body` else '' end) AS `message`,`sj_mq`.`id_mailQueue` AS `id_mailQueue`,`sj`.`id_jobTypes` AS `id_jobTypes`,`sj`.`id_jobStatus` AS `id_jobStatus`,`a`.`id_formActions` AS `id_formActions`,`a`.`id_dataRows` AS `id_dataRows`,`dt`.`name` AS `dataTables_name` from (((((((((`scheduledjobs` `sj` join `lookups` `l_status` on((`l_status`.`id` = `sj`.`id_jobStatus`))) join `lookups` `l_types` on((`l_types`.`id` = `sj`.`id_jobTypes`))) left join `scheduledjobs_mailqueue` `sj_mq` on((`sj_mq`.`id_scheduledJobs` = `sj`.`id`))) left join `mailqueue` `mq` on((`mq`.`id` = `sj_mq`.`id_mailQueue`))) left join `scheduledjobs_notifications` `sj_n` on((`sj_n`.`id_scheduledJobs` = `sj`.`id`))) left join `notifications` `n` on((`n`.`id` = `sj_n`.`id_notifications`))) left join `scheduledjobs_formactions` `a` on((`a`.`id_scheduledJobs` = `sj`.`id`))) left join `datarows` `r` on((`r`.`id` = `a`.`id_dataRows`))) left join `view_datatables` `dt` on((`r`.`id_dataTables` = `dt`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_scheduledjobs_reminders`
--

/*!50001 DROP VIEW IF EXISTS `view_scheduledjobs_reminders`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_scheduledjobs_reminders` AS select `r`.`id_scheduledJobs` AS `id_scheduledJobs`,`r`.`id_dataTables` AS `id_dataTables`,`r`.`session_start_date` AS `session_start_date`,`r`.`session_end_date` AS `session_end_date`,`sju`.`id_users` AS `id_users`,`l_status`.`lookup_code` AS `job_status_code`,`l_status`.`lookup_value` AS `job_status` from (((`scheduledjobs_reminders` `r` join `scheduledjobs` `sj` on((`sj`.`id` = `r`.`id_scheduledJobs`))) join `scheduledjobs_users` `sju` on((`sj`.`id` = `sju`.`id_scheduledJobs`))) join `lookups` `l_status` on((`l_status`.`id` = `sj`.`id_jobStatus`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_scheduledjobs_transactions`
--

/*!50001 DROP VIEW IF EXISTS `view_scheduledjobs_transactions`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_scheduledjobs_transactions` AS select `sj`.`id` AS `id`,`sj`.`date_create` AS `date_create`,`sj`.`date_to_be_executed` AS `date_to_be_executed`,`sj`.`date_executed` AS `date_executed`,`t`.`id` AS `transaction_id`,`t`.`transaction_time` AS `transaction_time`,`t`.`transaction_type` AS `transaction_type`,`t`.`transaction_by` AS `transaction_by`,`t`.`user_name` AS `user_name`,`t`.`transaction_verbal_log` AS `transaction_verbal_log` from (`scheduledjobs` `sj` join `view_transactions` `t` on(((`t`.`table_name` = 'scheduledJobs') and (`t`.`id_table_name` = `sj`.`id`)))) order by `sj`.`id`,`t`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_sections_fields`
--

/*!50001 DROP VIEW IF EXISTS `view_sections_fields`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_sections_fields` AS select `s`.`id` AS `id_sections`,`s`.`name` AS `section_name`,ifnull(`sft`.`content`,'') AS `content`,ifnull(`sft`.`meta`,'') AS `meta`,`s`.`id_styles` AS `id_styles`,`fields`.`style_name` AS `style_name`,`fields`.`field_id` AS `id_fields`,`fields`.`field_name` AS `field_name`,ifnull(`l`.`locale`,'') AS `locale`,ifnull(`g`.`name`,'') AS `gender` from ((((`sections` `s` left join `view_style_fields` `fields` on((`fields`.`style_id` = `s`.`id_styles`))) left join `sections_fields_translation` `sft` on(((`sft`.`id_sections` = `s`.`id`) and (`sft`.`id_fields` = `fields`.`field_id`)))) left join `languages` `l` on((`sft`.`id_languages` = `l`.`id`))) left join `genders` `g` on((`sft`.`id_genders` = `g`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_style_fields`
--

/*!50001 DROP VIEW IF EXISTS `view_style_fields`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_style_fields` AS select `s`.`style_id` AS `style_id`,`s`.`style_name` AS `style_name`,`s`.`style_type` AS `style_type`,`s`.`style_group` AS `style_group`,`f`.`field_id` AS `field_id`,`f`.`field_name` AS `field_name`,`f`.`field_type` AS `field_type`,`f`.`display` AS `display`,`f`.`position` AS `position`,`sf`.`default_value` AS `default_value`,`sf`.`help` AS `help`,`sf`.`disabled` AS `disabled`,`sf`.`hidden` AS `hidden` from ((`view_styles` `s` left join `styles_fields` `sf` on((`s`.`style_id` = `sf`.`id_styles`))) left join `view_fields` `f` on((`f`.`field_id` = `sf`.`id_fields`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_styles`
--

/*!50001 DROP VIEW IF EXISTS `view_styles`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_styles` AS select cast(`s`.`id` as unsigned) AS `style_id`,`s`.`name` AS `style_name`,`s`.`description` AS `style_description`,cast(`lst`.`id` as unsigned) AS `style_type_id`,`lst`.`lookup_value` AS `style_type`,cast(`sg`.`id` as unsigned) AS `style_group_id`,`sg`.`name` AS `style_group`,`sg`.`description` AS `style_group_description`,`sg`.`position` AS `style_group_position` from ((`styles` `s` left join `lookups` `lst` on(((`s`.`id_type` = `lst`.`id`) and (`lst`.`type_code` = 'styleType')))) left join `stylegroup` `sg` on((`s`.`id_group` = `sg`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_tasks`
--

/*!50001 DROP VIEW IF EXISTS `view_tasks`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_tasks` AS select `sj`.`id` AS `id`,`sj`.`status_code` AS `status_code`,`sj`.`status` AS `status`,`sj`.`type_code` AS `type_code`,`sj`.`type` AS `type`,`sj`.`date_create` AS `date_create`,`sj`.`date_to_be_executed` AS `date_to_be_executed`,`sj`.`date_executed` AS `date_executed`,`sj`.`recipient` AS `recipient`,`t`.`config` AS `config`,`sj_t`.`id_tasks` AS `id_tasks`,`sj`.`id_jobTypes` AS `id_jobTypes`,`sj`.`id_jobStatus` AS `id_jobStatus`,`sj`.`description` AS `description`,`sj`.`id_dataRows` AS `id_dataRows`,`sj`.`dataTables_name` AS `dataTables_name` from ((`tasks` `t` join `scheduledjobs_tasks` `sj_t` on((`sj_t`.`id_tasks` = `t`.`id`))) join `view_scheduledjobs` `sj` on((`sj`.`id` = `sj_t`.`id_scheduledJobs`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_transactions`
--

/*!50001 DROP VIEW IF EXISTS `view_transactions`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_transactions` AS select `t`.`id` AS `id`,`t`.`transaction_time` AS `transaction_time`,`t`.`id_transactionTypes` AS `id_transactionTypes`,`tran_type`.`lookup_value` AS `transaction_type`,`t`.`id_transactionBy` AS `id_transactionBy`,`tran_by`.`lookup_value` AS `transaction_by`,`t`.`id_users` AS `id_users`,`u`.`name` AS `user_name`,`t`.`table_name` AS `table_name`,`t`.`id_table_name` AS `id_table_name`,replace(json_extract(`t`.`transaction_log`,'$.verbal_log'),'"','') AS `transaction_verbal_log` from (((`transactions` `t` join `lookups` `tran_type` on((`tran_type`.`id` = `t`.`id_transactionTypes`))) join `lookups` `tran_by` on((`tran_by`.`id` = `t`.`id_transactionBy`))) left join `users` `u` on((`u`.`id` = `t`.`id_users`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user_codes`
--

/*!50001 DROP VIEW IF EXISTS `view_user_codes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_user_codes` AS select `u`.`id` AS `id`,`u`.`email` AS `email`,`u`.`name` AS `name`,`u`.`blocked` AS `blocked`,(case when (`u`.`name` = 'admin') then 'admin' when (`u`.`name` = 'tpf') then 'tpf' else ifnull(`vc`.`code`,'-') end) AS `code`,`u`.`intern` AS `intern` from (`users` `u` left join `validation_codes` `vc` on((`u`.`id` = `vc`.`id_users`))) where ((`u`.`intern` <> 1) and (`u`.`id_status` > 0)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_users`
--

/*!50001 DROP VIEW IF EXISTS `view_users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_users` AS select `u`.`id` AS `id`,`u`.`email` AS `email`,`u`.`name` AS `name`,ifnull(concat(`u`.`last_login`,' (',(to_days(now()) - to_days(`u`.`last_login`)),' days ago)'),'never') AS `last_login`,`usl`.`lookup_value` AS `status`,`usl`.`lookup_description` AS `description`,`u`.`blocked` AS `blocked`,(case when (`u`.`name` = 'admin') then 'admin' when (`u`.`name` = 'tpf') then 'tpf' else ifnull(`vc`.`code`,'-') end) AS `code`,group_concat(distinct `g`.`name` separator '; ') AS `groups`,`ua`.`activity_count` AS `user_activity`,`ua`.`distinct_url_count` AS `ac`,`u`.`intern` AS `intern`,`u`.`id_userTypes` AS `id_userTypes`,`lut`.`lookup_code` AS `user_type_code`,`lut`.`lookup_value` AS `user_type` from ((((((`users` `u` left join `lookups` `usl` on(((`usl`.`id` = `u`.`id_status`) and (`usl`.`type_code` = 'userStatus')))) left join `users_groups` `ug` on((`ug`.`id_users` = `u`.`id`))) left join `groups` `g` on((`g`.`id` = `ug`.`id_groups`))) left join `validation_codes` `vc` on((`u`.`id` = `vc`.`id_users`))) join `lookups` `lut` on((`lut`.`id` = `u`.`id_userTypes`))) left join (select `ua`.`id_users` AS `id_users`,count(0) AS `activity_count`,count(distinct (case when (`ua`.`id_type` = 1) then `ua`.`url` end)) AS `distinct_url_count` from `user_activity` `ua` group by `ua`.`id_users`) `ua` on((`ua`.`id_users` = `u`.`id`))) where ((`u`.`intern` <> 1) and (`u`.`id_status` > 0)) group by `u`.`id`,`u`.`email`,`u`.`name`,`u`.`last_login`,`usl`.`lookup_value`,`usl`.`lookup_description`,`u`.`blocked`,`vc`.`code`,`ua`.`activity_count`,`ua`.`distinct_url_count`,`u`.`intern`,`u`.`id_userTypes`,`lut`.`lookup_code`,`lut`.`lookup_value` order by `u`.`email` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-26 14:19:10
