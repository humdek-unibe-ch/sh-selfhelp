-- MySQL dump 10.13  Distrib 5.7.23, for Win32 (AMD64)
--
-- Host: localhost    Database: selfhelp
-- ------------------------------------------------------
-- Server version	5.7.28-0ubuntu0.18.04.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_groups`
--

LOCK TABLES `acl_groups` WRITE;
/*!40000 ALTER TABLE `acl_groups` DISABLE KEYS */;
INSERT INTO `acl_groups` VALUES (0000000001,0000000001,1,1,1,1),(0000000001,0000000002,1,1,1,1),(0000000001,0000000003,1,0,1,0),(0000000001,0000000004,1,1,1,1),(0000000001,0000000005,1,0,1,0),(0000000001,0000000006,1,1,1,1),(0000000001,0000000007,1,1,1,1),(0000000001,0000000008,1,1,1,1),(0000000001,0000000009,1,0,0,0),(0000000001,0000000010,1,0,0,0),(0000000001,0000000011,1,1,0,0),(0000000001,0000000012,1,0,1,0),(0000000001,0000000013,1,0,0,1),(0000000001,0000000014,1,0,0,0),(0000000001,0000000015,1,1,0,0),(0000000001,0000000016,1,0,1,0),(0000000001,0000000017,1,0,0,1),(0000000001,0000000018,1,0,0,0),(0000000001,0000000019,1,1,0,0),(0000000001,0000000020,1,0,1,0),(0000000001,0000000021,1,0,0,1),(0000000001,0000000022,1,0,0,0),(0000000001,0000000023,1,0,0,0),(0000000001,0000000024,1,0,0,0),(0000000001,0000000025,1,1,0,0),(0000000001,0000000026,1,0,1,0),(0000000001,0000000027,1,0,0,1),(0000000001,0000000028,1,0,0,0),(0000000001,0000000029,1,1,1,1),(0000000001,0000000030,1,1,1,1),(0000000001,0000000031,1,1,1,1),(0000000001,0000000032,1,1,1,1),(0000000001,0000000033,1,1,1,1),(0000000001,0000000035,1,1,1,1),(0000000001,0000000036,1,1,0,0),(0000000001,0000000037,1,0,1,0),(0000000001,0000000038,1,0,0,0),(0000000001,0000000039,1,1,0,0),(0000000001,0000000040,1,0,0,1),(0000000001,0000000041,1,0,1,0),(0000000001,0000000042,1,0,0,1),(0000000002,0000000001,1,0,0,0),(0000000002,0000000002,1,0,0,0),(0000000002,0000000003,1,0,0,0),(0000000002,0000000004,1,0,0,0),(0000000002,0000000005,1,0,0,0),(0000000002,0000000006,1,0,0,0),(0000000002,0000000007,1,0,0,0),(0000000002,0000000008,1,0,0,0),(0000000002,0000000009,1,0,0,0),(0000000002,0000000010,0,0,0,0),(0000000002,0000000011,0,0,0,0),(0000000002,0000000012,0,0,0,0),(0000000002,0000000013,0,0,0,0),(0000000002,0000000014,1,0,0,0),(0000000002,0000000015,1,1,0,0),(0000000002,0000000016,1,0,1,0),(0000000002,0000000017,0,0,0,0),(0000000002,0000000018,1,0,0,0),(0000000002,0000000019,1,1,0,0),(0000000002,0000000020,1,0,1,0),(0000000002,0000000021,0,0,0,0),(0000000002,0000000022,0,0,0,0),(0000000002,0000000023,0,0,0,0),(0000000002,0000000024,0,0,0,0),(0000000002,0000000025,0,0,0,0),(0000000002,0000000026,0,0,0,0),(0000000002,0000000027,0,0,0,0),(0000000002,0000000028,1,0,0,0),(0000000002,0000000029,1,0,0,0),(0000000002,0000000030,1,0,0,0),(0000000002,0000000031,1,0,0,0),(0000000002,0000000032,1,0,0,0),(0000000002,0000000033,1,0,0,0),(0000000002,0000000035,1,0,0,0),(0000000002,0000000036,1,1,0,0),(0000000002,0000000037,0,0,0,0),(0000000003,0000000001,1,0,0,0),(0000000003,0000000002,1,0,0,0),(0000000003,0000000003,1,0,0,0),(0000000003,0000000004,1,0,0,0),(0000000003,0000000005,1,0,0,0),(0000000003,0000000006,1,0,0,0),(0000000003,0000000007,1,0,0,0),(0000000003,0000000008,1,0,0,0),(0000000003,0000000009,0,0,0,0),(0000000003,0000000010,0,0,0,0),(0000000003,0000000011,0,0,0,0),(0000000003,0000000012,0,0,0,0),(0000000003,0000000013,0,0,0,0),(0000000003,0000000014,0,0,0,0),(0000000003,0000000015,0,0,0,0),(0000000003,0000000016,0,0,0,0),(0000000003,0000000017,0,0,0,0),(0000000003,0000000018,0,0,0,0),(0000000003,0000000019,0,0,0,0),(0000000003,0000000020,0,0,0,0),(0000000003,0000000021,0,0,0,0),(0000000003,0000000022,0,0,0,0),(0000000003,0000000023,0,0,0,0),(0000000003,0000000024,0,0,0,0),(0000000003,0000000025,0,0,0,0),(0000000003,0000000026,0,0,0,0),(0000000003,0000000027,0,0,0,0),(0000000003,0000000028,1,0,0,0),(0000000003,0000000029,1,0,0,0),(0000000003,0000000030,1,0,0,0),(0000000003,0000000031,1,0,0,0),(0000000003,0000000032,1,0,0,0),(0000000003,0000000033,1,0,0,0),(0000000003,0000000035,1,0,0,0),(0000000003,0000000036,0,0,0,0);
/*!40000 ALTER TABLE `acl_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_users`
--

DROP TABLE IF EXISTS `acl_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_users`
--

LOCK TABLES `acl_users` WRITE;
/*!40000 ALTER TABLE `acl_users` DISABLE KEYS */;
INSERT INTO `acl_users` VALUES (0000000001,0000000001,1,0,0,0),(0000000001,0000000033,1,0,0,0),(0000000001,0000000035,1,0,0,0);
/*!40000 ALTER TABLE `acl_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actions` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actions`
--

LOCK TABLES `actions` WRITE;
/*!40000 ALTER TABLE `actions` DISABLE KEYS */;
INSERT INTO `actions` VALUES (0000000001,'custom'),(0000000002,'component'),(0000000003,'sections');
/*!40000 ALTER TABLE `actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activityType`
--

DROP TABLE IF EXISTS `activityType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activityType` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activityType`
--

LOCK TABLES `activityType` WRITE;
/*!40000 ALTER TABLE `activityType` DISABLE KEYS */;
INSERT INTO `activityType` VALUES (0000000001,'experiment'),(0000000002,'export');
/*!40000 ALTER TABLE `activityType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_snd` int(10) unsigned zerofill NOT NULL,
  `id_rcv` int(10) unsigned zerofill DEFAULT NULL,
  `id_rcv_grp` int(10) unsigned zerofill DEFAULT NULL,
  `content` longtext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_snd` (`id_snd`) USING BTREE,
  KEY `id_rcv` (`id_rcv`) USING BTREE,
  KEY `id_rcv_grp` (`id_rcv_grp`),
  CONSTRAINT `fk_chat_id_rcv_grp` FOREIGN KEY (`id_rcv_grp`) REFERENCES `chatRoom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_id_rcv_user` FOREIGN KEY (`id_rcv`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_id_send` FOREIGN KEY (`id_snd`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatRecipiants`
--

DROP TABLE IF EXISTS `chatRecipiants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatRecipiants` (
  `id_users` int(10) unsigned zerofill NOT NULL,
  `id_chat` int(10) unsigned zerofill NOT NULL,
  `id_room_users` int(10) unsigned zerofill DEFAULT NULL,
  `is_new` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_users`,`id_chat`),
  KEY `id_users` (`id_users`),
  KEY `id_chat` (`id_chat`),
  KEY `id_room_users` (`id_room_users`),
  CONSTRAINT `chatRecipiants_fk_id_chat` FOREIGN KEY (`id_chat`) REFERENCES `chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chatRecipiants_fk_id_room_users` FOREIGN KEY (`id_room_users`) REFERENCES `chatRoom_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chatRecipiants_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatRecipiants`
--

LOCK TABLES `chatRecipiants` WRITE;
/*!40000 ALTER TABLE `chatRecipiants` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatRecipiants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatRoom`
--

DROP TABLE IF EXISTS `chatRoom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatRoom` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatRoom`
--

LOCK TABLES `chatRoom` WRITE;
/*!40000 ALTER TABLE `chatRoom` DISABLE KEYS */;
INSERT INTO `chatRoom` VALUES (0000000001,'root','The main room where every user is part of');
/*!40000 ALTER TABLE `chatRoom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatRoom_users`
--

DROP TABLE IF EXISTS `chatRoom_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatRoom_users` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_chatRoom` int(10) unsigned zerofill NOT NULL,
  `id_users` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_chatRoom_2` (`id_chatRoom`,`id_users`),
  KEY `id_chatRoom` (`id_chatRoom`),
  KEY `id_users` (`id_users`),
  CONSTRAINT `chatRoom_users_fk_id_chatRoom` FOREIGN KEY (`id_chatRoom`) REFERENCES `chatRoom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chatRoom_users_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatRoom_users`
--

LOCK TABLES `chatRoom_users` WRITE;
/*!40000 ALTER TABLE `chatRoom_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatRoom_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fieldType`
--

DROP TABLE IF EXISTS `fieldType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fieldType` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fieldType`
--

LOCK TABLES `fieldType` WRITE;
/*!40000 ALTER TABLE `fieldType` DISABLE KEYS */;
INSERT INTO `fieldType` VALUES (0000000001,'text',10),(0000000002,'textarea',30),(0000000003,'checkbox',60),(0000000004,'markdown',40),(0000000005,'number',50),(0000000006,'style-list',70),(0000000007,'markdown-inline',20),(0000000008,'json',45),(0000000009,'style-bootstrap',5),(0000000010,'type-input',4),(0000000011,'email',90),(0000000012,'code',42),(0000000013,'date',25),(0000000014,'time',24);
/*!40000 ALTER TABLE `fieldType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fields` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) unsigned zerofill NOT NULL DEFAULT '0000000002',
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_type` (`id_type`),
  CONSTRAINT `fields_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `fieldType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fields`
--

LOCK TABLES `fields` WRITE;
/*!40000 ALTER TABLE `fields` DISABLE KEYS */;
INSERT INTO `fields` VALUES (0000000001,'label_user',0000000001,1),(0000000002,'label_pw',0000000001,1),(0000000003,'label_login',0000000001,1),(0000000004,'label_pw_reset',0000000001,1),(0000000005,'alert_fail',0000000001,1),(0000000006,'children',0000000006,0),(0000000007,'login_title',0000000001,1),(0000000008,'label',0000000001,1),(0000000009,'label_pw_confirm',0000000001,1),(0000000010,'label_change',0000000001,1),(0000000011,'pw_change_title',0000000001,1),(0000000012,'delete_title',0000000001,1),(0000000013,'label_delete',0000000001,1),(0000000014,'delete_content',0000000002,1),(0000000015,'label_delete_confirm',0000000001,1),(0000000016,'delete_confirm_content',0000000002,1),(0000000017,'alert_pw_fail',0000000001,1),(0000000018,'alert_pw_success',0000000001,1),(0000000019,'alert_del_fail',0000000001,1),(0000000020,'alert_del_success',0000000001,1),(0000000021,'level',0000000005,0),(0000000022,'title',0000000007,1),(0000000023,'css',0000000001,0),(0000000024,'text',0000000002,1),(0000000025,'text_md',0000000004,1),(0000000026,'text_md_inline',0000000007,1),(0000000027,'url',0000000001,0),(0000000028,'type',0000000009,0),(0000000029,'is_fluid',0000000003,0),(0000000030,'alt',0000000001,1),(0000000031,'title_prefix',0000000001,1),(0000000032,'experimenter',0000000001,1),(0000000033,'subjects',0000000001,1),(0000000034,'subtitle',0000000001,1),(0000000035,'alert_success',0000000001,1),(0000000036,'label_name',0000000001,1),(0000000037,'name_placeholder',0000000001,1),(0000000038,'name_description',0000000007,1),(0000000039,'label_gender',0000000001,1),(0000000040,'gender_male',0000000001,1),(0000000041,'gender_female',0000000001,1),(0000000042,'label_activate',0000000001,1),(0000000043,'pw_placeholder',0000000001,1),(0000000044,'success',0000000001,1),(0000000045,'is_dismissable',0000000003,0),(0000000046,'is_expanded',0000000003,0),(0000000047,'is_collapsible',0000000003,0),(0000000048,'url_edit',0000000001,0),(0000000049,'caption_title',0000000001,1),(0000000050,'caption',0000000007,1),(0000000051,'label_cancel',0000000001,1),(0000000052,'url_cancel',0000000001,0),(0000000053,'source',0000000001,1),(0000000054,'type_input',0000000010,0),(0000000055,'placeholder',0000000001,1),(0000000056,'is_required',0000000003,0),(0000000057,'name',0000000001,0),(0000000058,'value',0000000002,0),(0000000059,'is_paragraph',0000000003,0),(0000000060,'count',0000000005,0),(0000000061,'count_max',0000000005,0),(0000000062,'label_right',0000000001,1),(0000000063,'label_wrong',0000000001,1),(0000000064,'right_content',0000000001,1),(0000000065,'wrong_content',0000000001,1),(0000000066,'items',0000000008,1),(0000000067,'is_multiple',0000000003,0),(0000000068,'labels',0000000008,1),(0000000069,'min',0000000005,0),(0000000070,'max',0000000005,0),(0000000071,'sources',0000000008,1),(0000000072,'label_root',0000000001,1),(0000000073,'label_back',0000000001,1),(0000000074,'label_next',0000000001,1),(0000000075,'has_navigation_buttons',0000000003,0),(0000000077,'search_text',0000000001,1),(0000000078,'is_sortable',0000000003,0),(0000000079,'is_editable',0000000003,0),(0000000080,'url_delete',0000000001,0),(0000000081,'label_add',0000000001,1),(0000000082,'url_add',0000000001,0),(0000000083,'id_prefix',0000000001,0),(0000000084,'id_active',0000000005,0),(0000000085,'is_inline',0000000003,0),(0000000086,'open_in_new_tab',0000000003,0),(0000000087,'is_log',0000000003,0),(0000000088,'label_date_time',0000000001,1),(0000000089,'css_nav',0000000001,0),(0000000090,'label_submit',0000000001,1),(0000000091,'condition',0000000008,0),(0000000092,'email_activate',0000000011,1),(0000000094,'email_reminder',0000000011,1),(0000000095,'label_lobby',0000000001,1),(0000000096,'label_new',0000000001,1),(0000000097,'debug',0000000003,0),(0000000099,'has_controls',0000000003,0),(0000000100,'has_indicators',0000000003,0),(0000000101,'is_striped',0000000003,0),(0000000102,'has_label',0000000003,0),(0000000103,'has_crossfade',0000000003,0),(0000000104,'has_navigation_menu',0000000003,0),(0000000105,'json',0000000008,1),(0000000106,'description',0000000002,1),(0000000107,'code',0000000012,1),(0000000108,'admins',0000000008,0),(0000000109,'email_admins',0000000011,1),(0000000110,'email_user',0000000011,1),(0000000111,'subject_user',0000000001,1),(0000000112,'attachments_user',0000000008,1),(0000000113,'do_store',0000000003,0),(0000000114,'is_html',0000000003,0),(0000000115,'maintenance',0000000004,1),(0000000116,'maintenance_date',0000000013,0),(0000000117,'maintenance_time',0000000014,0);
/*!40000 ALTER TABLE `fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genders`
--

DROP TABLE IF EXISTS `genders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genders` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genders`
--

LOCK TABLES `genders` WRITE;
/*!40000 ALTER TABLE `genders` DISABLE KEYS */;
INSERT INTO `genders` VALUES (0000000001,'male'),(0000000002,'female');
/*!40000 ALTER TABLE `genders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (0000000001,'admin','full access'),(0000000002,'therapist','access to home, legal, profile, experiment, manage experiment'),(0000000003,'subject','access to home, legal, profile, experiment');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL COMMENT '"e.g en-GB, de-CH"',
  `language` varchar(100) NOT NULL,
  `csv_separator` varchar(1) NOT NULL DEFAULT ',',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (0000000001,'all','Independent',','),(0000000002,'de-CH','Deutsch (Schweiz)',','),(0000000003,'en-GB','English (GB)',',');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pageType`
--

DROP TABLE IF EXISTS `pageType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pageType` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pageType`
--

LOCK TABLES `pageType` WRITE;
/*!40000 ALTER TABLE `pageType` DISABLE KEYS */;
INSERT INTO `pageType` VALUES (0000000001,'intern'),(0000000002,'core'),(0000000003,'experiment'),(0000000004,'open');
/*!40000 ALTER TABLE `pageType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(100) DEFAULT NULL COMMENT 'pipe seperated list of HTTP Methods (GET|POST)',
  `id_actions` int(10) unsigned zerofill DEFAULT NULL,
  `id_navigation_section` int(10) unsigned zerofill DEFAULT NULL,
  `parent` int(10) unsigned zerofill DEFAULT NULL,
  `is_headless` tinyint(1) NOT NULL DEFAULT '0',
  `nav_position` int(11) DEFAULT NULL,
  `footer_position` int(11) DEFAULT NULL,
  `id_type` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `parent` (`parent`),
  KEY `id_actions` (`id_actions`),
  KEY `id_navigation_section` (`id_navigation_section`),
  KEY `id_type` (`id_type`),
  CONSTRAINT `pages_fk_id_actions` FOREIGN KEY (`id_actions`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fk_id_navigation_section` FOREIGN KEY (`id_navigation_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `pageType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_fk_parent` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (0000000001,'login','/login','GET|POST',0000000003,NULL,NULL,1,NULL,NULL,0000000002),(0000000002,'home','/','GET|POST',0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000003,'profile-link',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,0000000002),(0000000004,'profile','/profile','GET|POST',0000000003,NULL,0000000003,0,10,NULL,0000000002),(0000000005,'logout','/login','GET',NULL,NULL,0000000003,0,20,NULL,0000000002),(0000000006,'missing',NULL,NULL,0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000007,'no_access',NULL,NULL,0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000008,'no_access_guest',NULL,NULL,0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000009,'admin-link',NULL,NULL,NULL,NULL,NULL,0,1000,NULL,0000000001),(0000000010,'cmsSelect','/admin/cms/[i:pid]?/[i:sid]?/[i:ssid]?','GET|POST',0000000002,NULL,0000000009,0,10,NULL,0000000001),(0000000011,'cmsInsert','/admin/cms_insert/[i:pid]?','GET|POST|PUT',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000012,'cmsUpdate','/admin/cms_update/[i:pid]?/[i:sid]?/[i:ssid]?/[update|insert|delete:mode]/[v:type]/[i:did]?','GET|POST|PATCH',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000013,'cmsDelete','/admin/cms_delete/[i:pid]/[i:sid]?/[i:ssid]?','GET|POST|DELETE',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000014,'userSelect','/admin/user/[i:uid]?','GET',0000000002,NULL,0000000009,0,20,NULL,0000000001),(0000000015,'userInsert','/admin/user_insert','GET|POST|PUT',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000016,'userUpdate','/admin/user_update/[i:uid]/[v:mode]/[i:did]?','GET|POST|PATCH',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000017,'userDelete','/admin/user_delete/[i:uid]','GET|POST|DELETE',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000018,'groupSelect','/admin/group/[i:gid]?','GET',0000000002,NULL,0000000009,0,30,NULL,0000000001),(0000000019,'groupInsert','/admin/group_insert','GET|POST|PUT',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000020,'groupUpdate','/admin/group_update/[i:gid]','GET|POST|PATCH',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000021,'groupDelete','/admin/group_delete/[i:gid]','GET|POST|DELETE',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000022,'export','/admin/export','GET|POST',0000000002,NULL,0000000009,0,40,NULL,0000000001),(0000000023,'exportData','/admin/export/[user_input|user_activity|validation_codes|user_input_form:selector]/[all|used|open:option]?/[i:id]?','GET',0000000001,NULL,0000000009,0,NULL,NULL,0000000001),(0000000024,'assetSelect','/admin/asset','GET',0000000002,NULL,0000000009,0,15,NULL,0000000001),(0000000025,'assetInsert','/admin/asset_insert/[css|asset:mode]','GET|POST|PUT',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000026,'assetUpdate','/admin/asset_update/[v:file]','GET|POST|PATCH',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000027,'assetDelete','/admin/asset_delete/[css|asset:mode]/[*:file]','GET|POST|DELETE',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000028,'request','/request/[v:class]/[v:method]?','GET|POST',0000000001,NULL,NULL,0,NULL,NULL,0000000001),(0000000029,'contact','/kontakt/[i:gid]?/[i:uid]?','GET|POST',0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000030,'agb','/agb','GET',0000000003,NULL,NULL,0,NULL,300,0000000002),(0000000031,'impressum','/impressum','GET',0000000003,NULL,NULL,0,NULL,100,0000000002),(0000000032,'disclaimer','/disclaimer','GET',0000000003,NULL,NULL,0,NULL,200,0000000002),(0000000033,'validate','/validate/[i:uid]/[a:token]','GET|POST',0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000035,'reset_password','/reset','GET|POST',0000000003,NULL,NULL,0,NULL,NULL,0000000002),(0000000036,'userGenCode','/admin/user_gen_code','GET|POST|PUT',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000037,'email','/admin/email/[i:id]?','GET|POST|PATCH',0000000002,NULL,0000000009,0,11,NULL,0000000001),(0000000038,'chatAdminSelect','/admin/chat/[i:rid]?','GET',0000000002,NULL,0000000009,0,35,NULL,0000000001),(0000000039,'chatAdminInsert','/admin/chat_insert/','GET|POST|PUT',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000040,'chatAdminDelete','/admin/chat_delete/[i:rid]','GET|POST|DELETE',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000041,'chatAdminUpdate','/admin/chat_update/[i:rid]/[add_user|rm_user:mode]/[i:did]?','GET|POST|PATCH',0000000002,NULL,0000000009,0,NULL,NULL,0000000001),(0000000042,'exportDelete','/admin/exportDelete/[user_activity|user_input:selector]','GET|POST|DELETE',0000000002,NULL,0000000009,0,NULL,NULL,0000000001);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_fields`
--

DROP TABLE IF EXISTS `pages_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_fields`
--

LOCK TABLES `pages_fields` WRITE;
/*!40000 ALTER TABLE `pages_fields` DISABLE KEYS */;
INSERT INTO `pages_fields` VALUES (0000000002,0000000106,NULL,'A short description of the research project. This field will be used as `meta:description` in the HTML header. Some services use this tag to provide the user with information on the webpage (e.g. automatic link-replacement in messaging tools on smartphones use this description.)'),(0000000002,0000000115,NULL,'This field defines the content of the alert message that is shown when a date is set in the field `maintenance_date`. Use markdown with the special keywords `@date` and `@time` which will be replaced by a human-readable form of the fields `maintenance_date` and `maintenance_time`.'),(0000000002,0000000116,NULL,'If set (together with the field `maintenance_time`), an alert message is shown at the top of the page displaying to content as defined in the field `maintenance` (where the key `@data` is replaced by this field).'),(0000000002,0000000117,NULL,'If set (together with the field `maintenance_date`), an alert message is shown at the top of the page displaying to content as defined in the field `maintenance` (where the key `@time` is replaced by this field).');
/*!40000 ALTER TABLE `pages_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_fields_translation`
--

DROP TABLE IF EXISTS `pages_fields_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_fields_translation`
--

LOCK TABLES `pages_fields_translation` WRITE;
/*!40000 ALTER TABLE `pages_fields_translation` DISABLE KEYS */;
INSERT INTO `pages_fields_translation` VALUES (0000000001,0000000008,0000000002,'Login'),(0000000001,0000000008,0000000003,'Login'),(0000000002,0000000008,0000000002,'Projekt Name'),(0000000002,0000000008,0000000003,'Project Name'),(0000000002,0000000115,0000000002,'Um eine Server-Wartung durchzuführen wird die Seite ab dem @date um @time für einen kurzen Moment nicht erreichbar sein. Wir bitten um Entschuldigung.'),(0000000002,0000000115,0000000003,'There will be a short service disruption on the @date at @time due to server maintenance. Please accept our apologies for the caused inconveniences.'),(0000000003,0000000008,0000000002,'Profil'),(0000000003,0000000008,0000000003,'Profile'),(0000000004,0000000008,0000000002,'Einstellungen'),(0000000004,0000000008,0000000003,'Settings'),(0000000005,0000000008,0000000002,'Logout'),(0000000005,0000000008,0000000003,'Logout'),(0000000006,0000000008,0000000002,'Seite nicht gefunden'),(0000000006,0000000008,0000000003,'Missing'),(0000000007,0000000008,0000000002,'Kein Zugriff'),(0000000007,0000000008,0000000003,'No Access'),(0000000008,0000000008,0000000002,'Kein Zugriff'),(0000000008,0000000008,0000000003,'No Access'),(0000000009,0000000008,0000000001,'Admin'),(0000000010,0000000008,0000000001,'CMS'),(0000000011,0000000008,0000000001,'Create Page'),(0000000012,0000000008,0000000001,'Update Content'),(0000000013,0000000008,0000000001,'Delete Page'),(0000000014,0000000008,0000000001,'Users'),(0000000015,0000000008,0000000001,'Create User'),(0000000016,0000000008,0000000001,'Modify User'),(0000000017,0000000008,0000000001,'Delete User'),(0000000018,0000000008,0000000001,'Groups'),(0000000019,0000000008,0000000001,'Create Group'),(0000000020,0000000008,0000000001,'Modify Group'),(0000000021,0000000008,0000000001,'Delete Group'),(0000000022,0000000008,0000000001,'Export'),(0000000023,0000000008,0000000001,'Export'),(0000000024,0000000008,0000000001,'Assets'),(0000000025,0000000008,0000000001,'Upload Asset'),(0000000026,0000000008,0000000001,'Rename Asset'),(0000000027,0000000008,0000000001,'Delete Asset'),(0000000029,0000000008,0000000002,'Kontakt'),(0000000029,0000000008,0000000003,'Contact'),(0000000030,0000000008,0000000002,'AGB'),(0000000030,0000000008,0000000003,'GTC'),(0000000031,0000000008,0000000002,'Impressum'),(0000000031,0000000008,0000000003,'Impressum'),(0000000032,0000000008,0000000002,'Disclaimer'),(0000000032,0000000008,0000000003,'Disclaimer'),(0000000033,0000000008,0000000002,'Benutzer Validierung'),(0000000033,0000000008,0000000003,'User Validation'),(0000000035,0000000008,0000000002,'Passwort zurücksetzen'),(0000000035,0000000008,0000000003,'Reset Password'),(0000000036,0000000008,0000000001,'Generate Validation Codes'),(0000000037,0000000008,0000000001,'Email CMS'),(0000000037,0000000092,0000000002,'Guten Tag\r\n\r\nUm Ihre Email Adresse zu verifizieren und Ihren @project Account zu aktivieren klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team'),(0000000037,0000000092,0000000003,'Hello\r\n\r\nTo verify you email address and to activate your @project account please click the link below.\r\n\r\n@link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team'),(0000000037,0000000094,0000000002,'Guten Tag\r\n\r\nSie waren für längere Zeit nicht mehr aktiv auf der @project Plattform.\r\nEs würde uns freuen wenn Sie wieder vorbeischauen würden.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),(0000000037,0000000094,0000000003,'Hello\r\n\r\nYou did not visit the @project platform for some time now.\r\nWe would be pleased if you would visit us again.\r\n\r\n@link\r\n\r\nSincerely, your @project team'),(0000000038,0000000008,0000000001,'Chat Rooms'),(0000000039,0000000008,0000000001,'Create Chat Room'),(0000000040,0000000008,0000000001,'Delete Chat Room'),(0000000041,0000000008,0000000001,'Administrate Chat Room'),(0000000042,0000000008,0000000002,'Userdaten Löschen'),(0000000042,0000000008,0000000003,'Remove User Data');
/*!40000 ALTER TABLE `pages_fields_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_sections`
--

DROP TABLE IF EXISTS `pages_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_sections` (
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `id_sections` int(10) unsigned zerofill NOT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pages`,`id_sections`),
  KEY `id_pages` (`id_pages`),
  KEY `id_sections` (`id_sections`),
  CONSTRAINT `pages_sections_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pages_sections_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_sections`
--

LOCK TABLES `pages_sections` WRITE;
/*!40000 ALTER TABLE `pages_sections` DISABLE KEYS */;
INSERT INTO `pages_sections` VALUES (0000000001,0000000036,NULL),(0000000002,0000000019,0),(0000000004,0000000002,NULL),(0000000006,0000000003,NULL),(0000000007,0000000009,0),(0000000008,0000000012,0),(0000000029,0000000017,0),(0000000030,0000000016,0),(0000000031,0000000020,0),(0000000032,0000000018,0),(0000000033,0000000026,NULL),(0000000035,0000000028,NULL);
/*!40000 ALTER TABLE `pages_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_styles` int(10) unsigned zerofill NOT NULL,
  `name` varchar(100) NOT NULL,
  `owner` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id_styles` (`id_styles`),
  KEY `owner` (`owner`),
  CONSTRAINT `sections_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fk_owner` FOREIGN KEY (`owner`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (0000000001,0000000001,'login-login',NULL),(0000000002,0000000002,'profile-profile',NULL),(0000000003,0000000003,'missing-container',NULL),(0000000004,0000000004,'missing-jumbotron',NULL),(0000000005,0000000005,'missing-heading',NULL),(0000000006,0000000006,'missing-markdown',NULL),(0000000007,0000000008,'goBack-button',NULL),(0000000008,0000000008,'goHome-button',NULL),(0000000009,0000000003,'no_access-container',NULL),(0000000010,0000000004,'no_access-jumbotron',NULL),(0000000011,0000000005,'no_access-heading',NULL),(0000000012,0000000003,'no_access_guest-container',NULL),(0000000013,0000000004,'no_access_guest-jumbotron',NULL),(0000000014,0000000006,'no_access_guest-markdown',NULL),(0000000015,0000000006,'no_access-markdown',NULL),(0000000016,0000000003,'agb-container',NULL),(0000000017,0000000003,'contact-container',NULL),(0000000018,0000000003,'disclaimer-container',NULL),(0000000019,0000000003,'home-container',NULL),(0000000020,0000000003,'impressum-container',NULL),(0000000025,0000000010,'contact-chat',NULL),(0000000026,0000000009,'validate-validate',NULL),(0000000027,0000000008,'toLogin-button',NULL),(0000000028,0000000035,'resetPassword-resetPassword',NULL),(0000000029,0000000004,'impressum-jumbotron',NULL),(0000000030,0000000005,'impressum-heading',NULL),(0000000031,0000000012,'impressum-card',NULL),(0000000032,0000000006,'impressum-markdown',NULL),(0000000033,0000000012,'impressum-ext-card',NULL),(0000000034,0000000006,'impressum-ext-markdown',NULL),(0000000035,0000000041,'register-register',NULL),(0000000036,0000000003,'login-container',NULL),(0000000037,0000000003,'profile-container',NULL),(0000000038,0000000040,'profile-row-div',NULL),(0000000039,0000000040,'profile-col1-div',NULL),(0000000040,0000000040,'profile-col2-div',NULL),(0000000041,0000000012,'profile-username-card',NULL),(0000000042,0000000012,'profile-password-card',NULL),(0000000043,0000000012,'profile-delete-card',NULL),(0000000044,0000000014,'profile-username-form',NULL),(0000000045,0000000016,'profile-username-input',NULL),(0000000046,0000000014,'profile-password-form',NULL),(0000000047,0000000016,'profile-password-input',NULL),(0000000048,0000000016,'profile-password-confirm-input',NULL),(0000000049,0000000006,'profile-delete-markdown',NULL),(0000000050,0000000014,'profile-delete-form',NULL),(0000000051,0000000016,'profile-delete-input',NULL),(0000000052,0000000006,'profile-username-markdown',NULL),(0000000053,0000000012,'profile-notification-card',NULL),(0000000054,0000000006,'profile-notification-markdown',NULL),(0000000055,0000000036,'profile-notification-formUserInput',NULL),(0000000056,0000000016,'profile-notification-chat-input',NULL),(0000000057,0000000016,'profile-notification-reminder-input',NULL),(0000000058,0000000016,'profile-notification-phone-input',NULL);
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections_fields_translation`
--

DROP TABLE IF EXISTS `sections_fields_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections_fields_translation` (
  `id_sections` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `id_languages` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `id_genders` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL,
  PRIMARY KEY (`id_sections`,`id_fields`,`id_languages`,`id_genders`),
  KEY `id_sections` (`id_sections`),
  KEY `id_fields` (`id_fields`),
  KEY `id_languages` (`id_languages`),
  KEY `id_genders` (`id_genders`),
  CONSTRAINT `sections_fields_translation_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fields_translation_fk_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fields_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_fields_translation_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections_fields_translation`
--

LOCK TABLES `sections_fields_translation` WRITE;
/*!40000 ALTER TABLE `sections_fields_translation` DISABLE KEYS */;
INSERT INTO `sections_fields_translation` VALUES (0000000001,0000000001,0000000002,0000000001,'Email'),(0000000001,0000000001,0000000003,0000000001,'Email'),(0000000001,0000000002,0000000002,0000000001,'Passwort'),(0000000001,0000000002,0000000003,0000000001,'Password'),(0000000001,0000000003,0000000002,0000000001,'Anmelden'),(0000000001,0000000003,0000000003,0000000001,'Login'),(0000000001,0000000004,0000000002,0000000001,'Passwort vergessen?'),(0000000001,0000000004,0000000003,0000000001,'Forgotten the Password?'),(0000000001,0000000005,0000000002,0000000001,'Die Email Adresse oder das Passwort ist nicht korrekt.'),(0000000001,0000000005,0000000003,0000000001,'The email address or the password is not correct.'),(0000000001,0000000007,0000000002,0000000001,'Bitte einloggen'),(0000000001,0000000007,0000000003,0000000001,'Please Login'),(0000000002,0000000005,0000000002,0000000001,'Die Benutzerdaten konnten nicht geändert werden.'),(0000000002,0000000005,0000000003,0000000001,'Unable to change the user data.'),(0000000002,0000000019,0000000002,0000000001,'Die Benutzerdaten konnten nicht gelöscht werden.'),(0000000002,0000000019,0000000003,0000000001,'Unable to delete the account.'),(0000000002,0000000020,0000000002,0000000001,'Die Benutzerdaten wurden erfolgreich gelöscht.'),(0000000002,0000000020,0000000003,0000000001,'Successfully deleted the account.'),(0000000002,0000000023,0000000001,0000000001,''),(0000000002,0000000035,0000000002,0000000001,'Die Benutzerdaten wurden erfolgreich geändert.'),(0000000002,0000000035,0000000003,0000000001,'The user data were successfully changed.'),(0000000003,0000000029,0000000001,0000000001,'0'),(0000000004,0000000023,0000000001,0000000001,'my-3'),(0000000005,0000000021,0000000001,0000000001,'1'),(0000000005,0000000022,0000000002,0000000001,'Seite nicht gefunden'),(0000000005,0000000022,0000000003,0000000001,'Page not Found'),(0000000006,0000000025,0000000002,0000000001,'Diese Seite konnte leider nicht gefunden werden.'),(0000000006,0000000025,0000000003,0000000001,'This page could not be found.'),(0000000007,0000000008,0000000002,0000000001,'Zurück'),(0000000007,0000000008,0000000003,0000000001,'Back'),(0000000007,0000000027,0000000001,0000000001,'#back'),(0000000007,0000000028,0000000001,0000000001,'primary'),(0000000008,0000000008,0000000002,0000000001,'Zur Startseite'),(0000000008,0000000008,0000000003,0000000001,'Home'),(0000000008,0000000027,0000000001,0000000001,'#home'),(0000000008,0000000028,0000000001,0000000001,'primary'),(0000000009,0000000029,0000000001,0000000001,'0'),(0000000010,0000000023,0000000001,0000000001,'my-3'),(0000000011,0000000021,0000000001,0000000001,'1'),(0000000011,0000000022,0000000002,0000000001,'Kein Zugriff'),(0000000011,0000000022,0000000003,0000000001,'No Access'),(0000000012,0000000029,0000000001,0000000001,'0'),(0000000013,0000000023,0000000001,0000000001,'my-3'),(0000000014,0000000025,0000000002,0000000001,'Um diese Seite zu erreichen müssen Sie eingeloggt sein.'),(0000000014,0000000025,0000000003,0000000001,'To reach this page you must be logged in.'),(0000000015,0000000025,0000000002,0000000001,'Sie haben keine Zugriffsrechte für diese Seite.'),(0000000015,0000000025,0000000003,0000000001,'You do not have access to this page.'),(0000000016,0000000023,0000000001,0000000001,'my-3'),(0000000016,0000000029,0000000001,0000000001,'0'),(0000000017,0000000029,0000000001,0000000001,'0'),(0000000018,0000000023,0000000001,0000000001,'my-3'),(0000000018,0000000029,0000000001,0000000001,'0'),(0000000019,0000000023,0000000001,0000000001,'my-3'),(0000000019,0000000029,0000000001,0000000001,'1'),(0000000020,0000000023,0000000001,0000000001,'my-3'),(0000000020,0000000029,0000000001,0000000001,'0'),(0000000025,0000000005,0000000002,0000000001,'Es ist ein Fehler aufgetreten. Die Nachricht konnte nicht gesendet werden.'),(0000000025,0000000005,0000000003,0000000001,'An error occurred. The message could not be sent.'),(0000000025,0000000030,0000000002,0000000001,'Bitte wählen Sie einen Probanden aus.'),(0000000025,0000000030,0000000003,0000000001,'Please select a subject'),(0000000025,0000000031,0000000002,0000000001,'Kommunikation mit'),(0000000025,0000000031,0000000003,0000000001,'Communication with'),(0000000025,0000000032,0000000002,0000000001,'ihrer Psychologin/ihrem Psychologe'),(0000000025,0000000032,0000000003,0000000001,'your psychologist'),(0000000025,0000000033,0000000002,0000000001,'Probanden'),(0000000025,0000000033,0000000003,0000000001,'Subjects'),(0000000025,0000000090,0000000002,0000000001,'Senden'),(0000000025,0000000090,0000000003,0000000001,'Send'),(0000000025,0000000095,0000000002,0000000001,'Lobby'),(0000000025,0000000095,0000000003,0000000001,'Lobby'),(0000000025,0000000096,0000000002,0000000001,'Neue Nachrichten'),(0000000025,0000000096,0000000003,0000000001,'New Messages'),(0000000025,0000000110,0000000002,0000000001,'Guten Tag\r\n\r\nSie haben eine neue Nachricht auf der @project Plattform erhalten.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),(0000000025,0000000110,0000000003,0000000001,'Hello\r\n\r\nYou received a new message on the @project Plattform.\r\n\r\n@link\r\n\r\nSincerely, your @project team'),(0000000025,0000000111,0000000002,0000000001,'@project Chat Benachrichtigung'),(0000000025,0000000111,0000000003,0000000001,'@project Chat Notification'),(0000000026,0000000002,0000000002,0000000001,'Passwort'),(0000000026,0000000002,0000000003,0000000001,'Password'),(0000000026,0000000003,0000000002,0000000001,'Zum Login'),(0000000026,0000000003,0000000003,0000000001,'To Login'),(0000000026,0000000005,0000000002,0000000001,'Das Aktivieren des Benutzers ist fehlgeschlagen.'),(0000000026,0000000005,0000000003,0000000001,'The activation of the user has failed.'),(0000000026,0000000009,0000000002,0000000001,'Bitte das Passwort bestätigen'),(0000000026,0000000009,0000000003,0000000001,'Please confirm the password'),(0000000026,0000000022,0000000002,0000000001,'Benutzer aktivieren'),(0000000026,0000000022,0000000003,0000000001,'Activate User'),(0000000026,0000000034,0000000002,0000000001,'Erforderliche Daten für die Aktivierung'),(0000000026,0000000034,0000000003,0000000001,'Required Data to Activate the User'),(0000000026,0000000035,0000000002,0000000001,'Sie können sich nun mit dem von Ihnen gewählten Passwort und Email einloggen und die Seite benutzen.'),(0000000026,0000000035,0000000003,0000000001,'You are now able to login in to the web page with the chosen password and email.'),(0000000026,0000000036,0000000002,0000000001,'Benutzername'),(0000000026,0000000036,0000000003,0000000001,'Username'),(0000000026,0000000037,0000000002,0000000001,'Bitte den Benutzernamen eingeben'),(0000000026,0000000037,0000000003,0000000001,'Please enter a username'),(0000000026,0000000038,0000000002,0000000001,'Ein Name mit dem Sie angesprochen werden wollen. Aus Gründen der Anonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.'),(0000000026,0000000038,0000000003,0000000001,'The name with which you would like to be addressed. For reasons of anonymity pleas do **not** use your real name.'),(0000000026,0000000039,0000000002,0000000001,'Geschlecht'),(0000000026,0000000039,0000000003,0000000001,'Gender'),(0000000026,0000000040,0000000002,0000000001,'männlich'),(0000000026,0000000040,0000000003,0000000001,'male'),(0000000026,0000000041,0000000002,0000000001,'weiblich'),(0000000026,0000000041,0000000003,0000000001,'female'),(0000000026,0000000042,0000000002,0000000001,'Benutzer aktivieren'),(0000000026,0000000042,0000000003,0000000001,'Activate User'),(0000000026,0000000043,0000000002,0000000001,'Bitte das Passwort eingeben'),(0000000026,0000000043,0000000003,0000000001,'Please enter a password'),(0000000026,0000000044,0000000002,0000000001,'Benutzer erfolgreich aktiviert'),(0000000026,0000000044,0000000003,0000000001,'User was successfully Activated'),(0000000027,0000000008,0000000002,0000000001,'Zum Login'),(0000000027,0000000008,0000000003,0000000001,'To Login'),(0000000027,0000000027,0000000001,0000000001,'#login'),(0000000027,0000000028,0000000001,0000000001,'primary'),(0000000028,0000000003,0000000002,0000000001,'Zum Login'),(0000000028,0000000003,0000000003,0000000001,'To Login'),(0000000028,0000000004,0000000002,0000000001,'Passwort zurücksetzen'),(0000000028,0000000004,0000000003,0000000001,'Reset Password'),(0000000028,0000000005,0000000002,0000000001,'Aktivierungs Email konnte nicht versendet werden.'),(0000000028,0000000005,0000000003,0000000001,'Activation email could not be sent.'),(0000000028,0000000025,0000000002,0000000001,'# Passwort Zurücksetzen\r\n\r\nHier können sie Ihr Passwort zurücksetzen.\r\nBitte geben sie Ihre Email Adresse ein mit welcher sie bei @project registriert sind.\r\nSie werden eine Email erhalten mit einem neuen Aktivierungslink um Ihr Passwort zurück zu setzen.'),(0000000028,0000000025,0000000003,0000000001,'# Reset Password\r\n\r\nThis page allows you to reset your password.\r\nPlease enter the email address with which you are registered on @project.\r\nYou will receive an email with a new activation link which will allow you to reset the password.'),(0000000028,0000000028,0000000001,0000000001,'primary'),(0000000028,0000000035,0000000002,0000000001,'Die Aktivierungs Email wurde versendet. Klicken sie auf den Aktivierungslink um Ihr Passwort zurück zu setzen.'),(0000000028,0000000035,0000000003,0000000001,'The activation mail was sent. Click the activation link to rest your password.'),(0000000028,0000000044,0000000002,0000000001,'Email versendet'),(0000000028,0000000044,0000000003,0000000001,'Email Sent'),(0000000028,0000000055,0000000002,0000000001,'Bitte Email eingeben'),(0000000028,0000000055,0000000003,0000000001,'Please Enter Email'),(0000000028,0000000110,0000000002,0000000001,'Guten Tag\r\n\r\nUm das Passwort von Ihrem @project Account zurück zu setzten klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team\r\n'),(0000000028,0000000110,0000000003,0000000001,'Hello\r\n\r\nTo reset password of your @project account please click the link below.\r\n\r\n@link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team.\r\n'),(0000000028,0000000111,0000000002,0000000001,'@project Passwort zurück setzen'),(0000000028,0000000111,0000000003,0000000001,'@project Password Reset'),(0000000028,0000000114,0000000001,0000000001,'0'),(0000000030,0000000021,0000000001,0000000001,'1'),(0000000030,0000000022,0000000002,0000000001,'Impressum'),(0000000030,0000000022,0000000003,0000000001,'Impressum'),(0000000031,0000000023,0000000001,0000000001,'mb-3'),(0000000031,0000000028,0000000001,0000000001,'light'),(0000000031,0000000046,0000000001,0000000001,'0'),(0000000031,0000000047,0000000001,0000000001,'0'),(0000000032,0000000025,0000000002,0000000001,'![Logo University of Bern](%logo/Unibe_Logo_16pt_RGB_201807.png|250x|float-left,border-0,mr-5 \"Logo University of Bern\")\r\n\r\n**Universität Bern**  \r\n**Philosophisch-humanwissenschaftliche Fakultät**\r\n\r\nFabrikstrasse 8  \r\n3012 Bern\r\n\r\nTelefon: +41 31 631 55 11\r\n\r\n**Entwicklung:** [Technologieplatform (TPF)](http://www.philhum.unibe.ch/forschung/tpf/index_ger.html)'),(0000000032,0000000025,0000000003,0000000001,'![Logo University of Bern](%logo/Unibe_Logo_16pt_RGB_201807.png|250x|float-left,border-0,mr-5 \"Logo University of Bern\")\r\n\r\n**University of Bern**  \r\n**Faculty of Human Sciences**\r\n\r\nFabrikstrasse 8  \r\n3012 Bern\r\n\r\nPhone: +41 31 631 55 11\r\n\r\n**Development:** [Technologieplatform (TPF)](http://www.philhum.unibe.ch/forschung/tpf/index_ger.html)'),(0000000033,0000000028,0000000001,0000000001,'light'),(0000000033,0000000046,0000000001,0000000001,'0'),(0000000033,0000000047,0000000001,0000000001,'0'),(0000000034,0000000025,0000000002,0000000001,'| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0   | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)          | 1.1.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.3/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.3/about/license/) |\r\n| [Datatables](https://datatables.net/)                      | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0   | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [mermaid](https://mermaidjs.github.io/)                    | 8.2.3   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [PHPMailer](https://github.com/PHPMailer/PHPMailer)        | 6.0.7   | [LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1)) | [License Details](https://github.com/PHPMailer/PHPMailer#license) |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0   | [MIT](https://tldrlegal.com/license/mit-license) | |'),(0000000034,0000000025,0000000003,0000000001,'| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0   | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)          | 1.1.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.3/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.3/about/license/) |\r\n| [Datatables](https://datatables.net/)                      | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0   | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [mermaid](https://mermaidjs.github.io/)                    | 8.2.3   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [PHPMailer](https://github.com/PHPMailer/PHPMailer)        | 6.0.7   | [LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1)) | [License Details](https://github.com/PHPMailer/PHPMailer#license) |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0   | [MIT](https://tldrlegal.com/license/mit-license) | |'),(0000000035,0000000001,0000000002,0000000001,'Email'),(0000000035,0000000001,0000000003,0000000001,'Email'),(0000000035,0000000002,0000000002,0000000001,'Validierungs-Code'),(0000000035,0000000002,0000000003,0000000001,'Validation Code'),(0000000035,0000000005,0000000002,0000000001,'Die Email Adresse oder der Aktivierungs-Code ist ungültig'),(0000000035,0000000005,0000000003,0000000001,'The email address or the activation code is invalid'),(0000000035,0000000022,0000000002,0000000001,'Registration'),(0000000035,0000000022,0000000003,0000000001,'Registration'),(0000000035,0000000023,0000000001,0000000001,'mt-3'),(0000000035,0000000035,0000000002,0000000001,'Der erste Schritt der Registrierung war erfolgreich. Sie werden in Kürze eine Email mit einem Aktivierunks-Link erhalten.\r\n\r\nBitte folgen Sie diesem Link um die Registrierung abzuschliessen.'),(0000000035,0000000035,0000000003,0000000001,'The first step of the registration was successful.\r\nShortly you will receive an email with an activation link.\r\n\r\nPlease follow this activation link to complete the registration.'),(0000000035,0000000044,0000000002,0000000001,'Registrierung erfolgreich'),(0000000035,0000000044,0000000003,0000000001,'Registration Successful'),(0000000035,0000000090,0000000002,0000000001,'Registrieren'),(0000000035,0000000090,0000000003,0000000001,'Register'),(0000000036,0000000023,0000000001,0000000001,'mt-3'),(0000000037,0000000023,0000000001,0000000001,'my-3'),(0000000037,0000000029,0000000001,0000000001,'0'),(0000000038,0000000023,0000000001,0000000001,'row'),(0000000039,0000000023,0000000001,0000000001,'col-12 col-lg'),(0000000040,0000000023,0000000001,0000000001,'col'),(0000000041,0000000022,0000000002,0000000001,'Benutzername ändern'),(0000000041,0000000022,0000000003,0000000001,'Change the Username'),(0000000041,0000000023,0000000001,0000000001,'mb-3'),(0000000041,0000000028,0000000001,0000000001,'light'),(0000000041,0000000046,0000000001,0000000001,'1'),(0000000041,0000000047,0000000001,0000000001,'0'),(0000000041,0000000048,0000000001,0000000001,''),(0000000042,0000000022,0000000002,0000000001,'Passwort ändern'),(0000000042,0000000022,0000000003,0000000001,'Change the Password'),(0000000042,0000000023,0000000001,0000000001,''),(0000000042,0000000028,0000000001,0000000001,'light'),(0000000042,0000000046,0000000001,0000000001,'1'),(0000000042,0000000047,0000000001,0000000001,'0'),(0000000042,0000000048,0000000001,0000000001,''),(0000000043,0000000022,0000000002,0000000001,'Account löschen'),(0000000043,0000000022,0000000003,0000000001,'Delete the Account'),(0000000043,0000000023,0000000001,0000000001,'mt-3'),(0000000043,0000000028,0000000001,0000000001,'danger'),(0000000043,0000000046,0000000001,0000000001,'0'),(0000000043,0000000047,0000000001,0000000001,'1'),(0000000043,0000000048,0000000001,0000000001,''),(0000000044,0000000008,0000000002,0000000001,'Ändern'),(0000000044,0000000008,0000000003,0000000001,'Change'),(0000000044,0000000023,0000000001,0000000001,''),(0000000044,0000000027,0000000001,0000000001,'#self'),(0000000044,0000000028,0000000001,0000000001,'primary'),(0000000044,0000000051,0000000002,0000000001,''),(0000000044,0000000051,0000000003,0000000001,''),(0000000044,0000000052,0000000001,0000000001,''),(0000000045,0000000008,0000000002,0000000001,''),(0000000045,0000000008,0000000003,0000000001,''),(0000000045,0000000023,0000000001,0000000001,'mb-3'),(0000000045,0000000054,0000000001,0000000001,'text'),(0000000045,0000000055,0000000002,0000000001,'Neuer Benutzername'),(0000000045,0000000055,0000000003,0000000001,'New Username'),(0000000045,0000000056,0000000001,0000000001,'1'),(0000000045,0000000057,0000000001,0000000001,'user_name'),(0000000045,0000000058,0000000001,0000000001,''),(0000000046,0000000008,0000000002,0000000001,'Ändern'),(0000000046,0000000008,0000000003,0000000001,'Change'),(0000000046,0000000023,0000000001,0000000001,''),(0000000046,0000000027,0000000001,0000000001,'#self'),(0000000046,0000000028,0000000001,0000000001,'primary'),(0000000046,0000000051,0000000002,0000000001,''),(0000000046,0000000051,0000000003,0000000001,''),(0000000046,0000000052,0000000001,0000000001,''),(0000000047,0000000008,0000000002,0000000001,''),(0000000047,0000000008,0000000003,0000000001,''),(0000000047,0000000023,0000000001,0000000001,'mb-3'),(0000000047,0000000054,0000000001,0000000001,'password'),(0000000047,0000000055,0000000002,0000000001,'Neues Passwort'),(0000000047,0000000055,0000000003,0000000001,'New Password'),(0000000047,0000000056,0000000001,0000000001,'1'),(0000000047,0000000057,0000000001,0000000001,'password'),(0000000047,0000000058,0000000001,0000000001,''),(0000000048,0000000008,0000000002,0000000001,''),(0000000048,0000000008,0000000003,0000000001,''),(0000000048,0000000023,0000000001,0000000001,'mb-3'),(0000000048,0000000054,0000000001,0000000001,'password'),(0000000048,0000000055,0000000002,0000000001,'Neues Passwort wiederholen'),(0000000048,0000000055,0000000003,0000000001,'Repeat New Password'),(0000000048,0000000056,0000000001,0000000001,'1'),(0000000048,0000000057,0000000001,0000000001,'verification'),(0000000048,0000000058,0000000001,0000000001,''),(0000000049,0000000023,0000000001,0000000001,''),(0000000049,0000000025,0000000002,0000000001,'Alle Benutzerdaten werden gelöscht. Das Löschen des Accounts ist permanent und kann **nicht** rückgängig gemacht werden!\r\n\r\nWenn sie ihren Account wirklich löschen wollen bestätigen Sie dies indem Sie ihre Email Adresse eingeben.'),(0000000049,0000000025,0000000003,0000000001,'All user data will be deleted. The deletion of the account is permanent and **cannot** be undone!\r\n\r\nIf you are sure you want to delete the account confirm this by entering your email address.'),(0000000050,0000000008,0000000002,0000000001,'Löschen'),(0000000050,0000000008,0000000003,0000000001,'Delete'),(0000000050,0000000023,0000000001,0000000001,''),(0000000050,0000000027,0000000001,0000000001,'#self'),(0000000050,0000000028,0000000001,0000000001,'danger'),(0000000050,0000000051,0000000002,0000000001,''),(0000000050,0000000051,0000000003,0000000001,''),(0000000050,0000000052,0000000001,0000000001,''),(0000000051,0000000008,0000000002,0000000001,''),(0000000051,0000000008,0000000003,0000000001,''),(0000000051,0000000023,0000000001,0000000001,'mb-3'),(0000000051,0000000054,0000000001,0000000001,'email'),(0000000051,0000000055,0000000002,0000000001,'Email Adresse'),(0000000051,0000000055,0000000003,0000000001,'Email Address'),(0000000051,0000000056,0000000001,0000000001,'1'),(0000000051,0000000057,0000000001,0000000001,'email'),(0000000051,0000000058,0000000001,0000000001,''),(0000000052,0000000023,0000000001,0000000001,''),(0000000052,0000000025,0000000002,0000000001,'Dies ist der Name mit dem Sie angesprochen werden wollen. Aus Gründen der Anonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.'),(0000000052,0000000025,0000000003,0000000001,'The name with which you would like to be addressed. For reasons of anonymity please do **not** use your real name.'),(0000000053,0000000022,0000000002,0000000001,'Benachrichtigungen'),(0000000053,0000000022,0000000003,0000000001,'Notifications'),(0000000053,0000000023,0000000001,0000000001,'mb-3 mb-lg-0'),(0000000053,0000000028,0000000001,0000000001,'light'),(0000000053,0000000046,0000000001,0000000001,'1'),(0000000053,0000000047,0000000001,0000000001,'0'),(0000000053,0000000048,0000000001,0000000001,''),(0000000054,0000000023,0000000001,0000000001,''),(0000000054,0000000025,0000000002,0000000001,'Hier können sie automatische Benachrichtigungen ein- und ausschalten. Asserdem können sie eine Telefonnummer hinterlegen um per SMS benachrichtigt zu werden. '),(0000000054,0000000025,0000000003,0000000001,'Here you can enable and disable automatic notifications.\r\nAlso, by entering a phone number you can choose to be notified by SMS.'),(0000000055,0000000008,0000000002,0000000001,'Ändern'),(0000000055,0000000008,0000000003,0000000001,'Change'),(0000000055,0000000023,0000000001,0000000001,''),(0000000055,0000000028,0000000001,0000000001,'primary'),(0000000055,0000000035,0000000002,0000000001,'Die Einstellungen für Benachrichtigungen wurden erfolgreich gespeichert'),(0000000055,0000000035,0000000003,0000000001,'The notification settings were successfully saved'),(0000000055,0000000057,0000000001,0000000001,'notification'),(0000000055,0000000087,0000000001,0000000001,'0'),(0000000056,0000000008,0000000002,0000000001,'Benachrichtigung bei neuer Nachricht im Chat'),(0000000056,0000000008,0000000003,0000000001,'Notification on new chat message'),(0000000056,0000000023,0000000001,0000000001,''),(0000000056,0000000054,0000000001,0000000001,'checkbox'),(0000000056,0000000055,0000000002,0000000001,'chat'),(0000000056,0000000055,0000000003,0000000001,'chat'),(0000000056,0000000056,0000000001,0000000001,'0'),(0000000056,0000000057,0000000001,0000000001,'chat'),(0000000056,0000000058,0000000001,0000000001,'chat'),(0000000057,0000000008,0000000002,0000000001,'Benachrichtung bei Inaktivität'),(0000000057,0000000008,0000000003,0000000001,'Notification by inactivity'),(0000000057,0000000023,0000000001,0000000001,''),(0000000057,0000000054,0000000001,0000000001,'checkbox'),(0000000057,0000000055,0000000002,0000000001,'reminder'),(0000000057,0000000055,0000000003,0000000001,'reminder'),(0000000057,0000000056,0000000001,0000000001,'0'),(0000000057,0000000057,0000000001,0000000001,'reminder'),(0000000057,0000000058,0000000001,0000000001,'reminder'),(0000000058,0000000008,0000000002,0000000001,'Telefonnummer für SMS Benachrichtigung'),(0000000058,0000000008,0000000003,0000000001,'Phone Number for receiving SMS notifications'),(0000000058,0000000023,0000000001,0000000001,''),(0000000058,0000000054,0000000001,0000000001,'text'),(0000000058,0000000055,0000000002,0000000001,'Bitte Telefonnummer eingeben'),(0000000058,0000000055,0000000003,0000000001,'Please enter a phone number'),(0000000058,0000000056,0000000001,0000000001,'0'),(0000000058,0000000057,0000000001,0000000001,'phone'),(0000000058,0000000058,0000000001,0000000001,'');
/*!40000 ALTER TABLE `sections_fields_translation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections_hierarchy`
--

DROP TABLE IF EXISTS `sections_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections_hierarchy` (
  `parent` int(10) unsigned zerofill NOT NULL,
  `child` int(10) unsigned zerofill NOT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `parent` (`parent`),
  KEY `child` (`child`),
  CONSTRAINT `sections_hierarchy_fk_child` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_hierarchy_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections_hierarchy`
--

LOCK TABLES `sections_hierarchy` WRITE;
/*!40000 ALTER TABLE `sections_hierarchy` DISABLE KEYS */;
INSERT INTO `sections_hierarchy` VALUES (0000000002,0000000037,0),(0000000003,0000000004,NULL),(0000000004,0000000005,0),(0000000004,0000000006,10),(0000000004,0000000007,20),(0000000004,0000000008,30),(0000000009,0000000010,0),(0000000010,0000000007,20),(0000000010,0000000008,30),(0000000010,0000000011,0),(0000000010,0000000015,10),(0000000012,0000000013,0),(0000000013,0000000007,20),(0000000013,0000000011,0),(0000000013,0000000014,10),(0000000013,0000000027,30),(0000000017,0000000025,10),(0000000020,0000000029,0),(0000000020,0000000031,10),(0000000020,0000000033,20),(0000000029,0000000030,0),(0000000031,0000000032,0),(0000000033,0000000034,0),(0000000036,0000000001,1),(0000000036,0000000035,2),(0000000037,0000000038,0),(0000000038,0000000039,0),(0000000038,0000000040,10),(0000000039,0000000041,0),(0000000039,0000000053,10),(0000000040,0000000042,0),(0000000040,0000000043,10),(0000000041,0000000044,0),(0000000042,0000000046,0),(0000000043,0000000049,0),(0000000043,0000000050,10),(0000000044,0000000045,10),(0000000044,0000000052,0),(0000000046,0000000047,0),(0000000046,0000000048,10),(0000000050,0000000051,0),(0000000053,0000000054,0),(0000000053,0000000055,10),(0000000055,0000000056,0),(0000000055,0000000057,10),(0000000055,0000000058,20);
/*!40000 ALTER TABLE `sections_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections_navigation`
--

DROP TABLE IF EXISTS `sections_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections_navigation` (
  `parent` int(10) unsigned zerofill NOT NULL,
  `child` int(10) unsigned zerofill NOT NULL,
  `id_pages` int(10) unsigned zerofill NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  KEY `parent` (`parent`),
  KEY `id_pages` (`id_pages`),
  CONSTRAINT `sections_navigation_fk_child` FOREIGN KEY (`child`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_navigation_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sections_navigation_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections_navigation`
--

LOCK TABLES `sections_navigation` WRITE;
/*!40000 ALTER TABLE `sections_navigation` DISABLE KEYS */;
/*!40000 ALTER TABLE `sections_navigation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `styleGroup`
--

DROP TABLE IF EXISTS `styleGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styleGroup` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `styleGroup`
--

LOCK TABLES `styleGroup` WRITE;
/*!40000 ALTER TABLE `styleGroup` DISABLE KEYS */;
INSERT INTO `styleGroup` VALUES (0000000001,'intern',NULL,NULL),(0000000002,'Form','A form is a wrapper for input fields. It allows to send content of the input fields to the server and store the data to the database. Several style are available:',60),(0000000003,'Input','An input field must be placed inside a form wrapper. An input field allows a user to enter data and submit these to the server. The chosen form wrapper decides what happens with the submitted data. The following input fields styles are available:',70),(0000000004,'Wrapper','A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:',10),(0000000005,'Text','Text styles allow to control how text is displayed. These styles are used to create the main content. The following styles are available:',20),(0000000006,'List','Lists are styles that allow to define more sophisticated lists than the markdown syntax allows. They come with attached javascript functionality. The following lists are available:',50),(0000000007,'Media','The media styles allow to display different media on a webpage. The following styles are available:',40),(0000000008,'Link','Link styles allow to render different types of links:',30),(0000000009,'Admin','The admin styles are for user registration and access handling.\r\nThe following styles are available:',80);
/*!40000 ALTER TABLE `styleGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `styleType`
--

DROP TABLE IF EXISTS `styleType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styleType` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `styleType`
--

LOCK TABLES `styleType` WRITE;
/*!40000 ALTER TABLE `styleType` DISABLE KEYS */;
INSERT INTO `styleType` VALUES (0000000001,'view'),(0000000002,'component'),(0000000003,'navigation');
/*!40000 ALTER TABLE `styleType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `styles`
--

DROP TABLE IF EXISTS `styles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styles` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_type` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `id_group` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  `description` longtext,
  PRIMARY KEY (`id`),
  KEY `id_type` (`id_type`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `styles_fk_id_group` FOREIGN KEY (`id_group`) REFERENCES `styleGroup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `styles_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `styleType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `styles`
--

LOCK TABLES `styles` WRITE;
/*!40000 ALTER TABLE `styles` DISABLE KEYS */;
INSERT INTO `styles` VALUES (0000000001,'login',0000000002,0000000009,'provides a small form where the user can enter his or her email and password to access the WebApp. It also includes a link to reset a password.'),(0000000002,'profile',0000000002,0000000001,''),(0000000003,'container',0000000001,0000000004,'is an **invisible** wrapper.'),(0000000004,'jumbotron',0000000001,0000000004,'is a **visible** wrapper that wraps its content in a grey box with large spacing.'),(0000000005,'heading',0000000001,0000000005,'is used to display the 6 levels of HTML headings.'),(0000000006,'markdown',0000000001,0000000005,'is the bread-and-butter style which allows to style content in a very flexible way. In addition to markdown syntax, pure HTML statements are allowed which makes this style very versatile. It is recommended to limit the usage of HTML to a minimum in order to keep the layout of the webpage consistent.'),(0000000007,'markdownInline',0000000001,0000000005,'is similar to the markdown style but is intended for one-line text where emphasis is required.'),(0000000008,'button',0000000001,0000000008,'renders a button-style link with several predefined colour schemes.'),(0000000009,'validate',0000000002,0000000001,''),(0000000010,'chat',0000000002,0000000001,''),(0000000011,'alert',0000000001,0000000004,'is a **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.'),(0000000012,'card',0000000001,0000000004,'is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.'),(0000000013,'figure',0000000001,0000000007,'allows to attach a caption to media elements. A figure expects a media style as its immediate child.'),(0000000014,'form',0000000001,0000000002,'provides only the client-side functionality and does not do anything with the submitted data. This is intended to be connected with a custom component (required PHP programming).'),(0000000015,'image',0000000001,0000000007,'allows to render an image on a page.'),(0000000016,'input',0000000002,0000000003,'is a one-line input field style that allows to enter different types of data (e.g. text, color, time, date, checkbox).'),(0000000017,'plaintext',0000000001,0000000005,'renders simple text. No special syntax is allowed here.'),(0000000018,'link',0000000001,0000000008,'renders a standard link but allows to open the target in a new tab.'),(0000000019,'progressBar',0000000001,0000000007,'allows to render a static progress bar.'),(0000000020,'quiz',0000000001,0000000004,'is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.'),(0000000021,'rawText',0000000001,0000000005,'renders text in a mono-space font which makes it useful to display code.'),(0000000022,'select',0000000002,0000000003,'is a input field style that provides a predefined set of choices which can be selected with a dropdown menu. In contrast to the radio style the select style has a different visual appearance and provides a list of options where also multiple options can be chosen.'),(0000000023,'slider',0000000002,0000000003,'is an extension of the style input of type range. It allows to provide a label for each position of the slider.'),(0000000024,'tab',0000000001,0000000004,'is a child element of the style `tabs`.'),(0000000025,'tabs',0000000001,0000000004,'is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.'),(0000000026,'textarea',0000000002,0000000003,'is a multi-line input field style that allows to enter multiple lines of text.'),(0000000027,'video',0000000001,0000000007,'allows to load and display a video on a page.'),(0000000028,'accordionList',0000000002,0000000006,'is a **hierarchical** list where the root level is rendered as an accordion with only one root item expanded at a time.'),(0000000030,'navigationContainer',0000000001,0000000004,'is an **invisible** wrapper and is used specifically for navigation pages.'),(0000000031,'navigationAccordion',0000000003,0000000001,''),(0000000032,'nestedList',0000000002,0000000006,'is a **hierarchical** list where each root item item can be collapsed and expanded by clicking on a chevron.'),(0000000033,'navigationNested',0000000003,0000000001,''),(0000000034,'sortableList',0000000001,0000000006,'is **non-hierarchical** but can be sorted, new items can be added as well as items can be deleted. Note that only the visual aspects of these functions are rendered. The implementation of the functions need to be defined separately with javascript (See <a href=\"https://github.com/RubaXa/Sortable\" target=\"_blank\">Sortable</a> for more details).'),(0000000035,'resetPassword',0000000002,0000000001,''),(0000000036,'formUserInput',0000000002,0000000002,'stores the data from all child input fields into the database and displays the latest set of data in the database as values in the child input field (if `is_log` is unchecked).'),(0000000038,'radio',0000000002,0000000003,'allows to predefine a set of options for the user to select. It provides a list of options where only one option can be chosen.'),(0000000039,'showUserInput',0000000002,0000000002,'allows to display user input data. Use the name of a form to display the corresponding data.'),(0000000040,'div',0000000001,0000000004,'allows to wrap its children in a simple HTML `<div>` tag. This allows to create more complex layouts with the help of bootstrap classes.'),(0000000041,'register',0000000002,0000000009,'provides a small form to allow a user to register for the WebApp. In order to register a user must provide a valid email and activation code. Activation codes can be generated in the admin section of the WebApp. The list of available codes can be exported.'),(0000000042,'conditionalContainer',0000000002,0000000004,'is an **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition resolves to true.'),(0000000043,'audio',0000000001,0000000007,'allows to load and replay an audio source on a page.'),(0000000044,'carousel',0000000001,0000000007,'allows to render multiple images as a slide-show.'),(0000000045,'json',0000000002,0000000004,'allows to describe styles with `json` Syntax'),(0000000046,'userProgress',0000000002,0000000009,'A progress bar to indicate the overall experiment progress of a user.'),(0000000047,'mermaidForm',0000000002,0000000002,'Style to create diagrams using markdown syntax. Use <a href=\"https://mermaidjs.github.io/demos.html\" target=\"_blank\">mermaid markdown</a> syntax here.'),(0000000048,'emailForm',0000000002,0000000002,'A form to accept an email address and automatically send two emails: An email to the address entered in the form and another email to admins, specified in the style.');
/*!40000 ALTER TABLE `styles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `styles_fields`
--

DROP TABLE IF EXISTS `styles_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styles_fields` (
  `id_styles` int(10) unsigned zerofill NOT NULL,
  `id_fields` int(10) unsigned zerofill NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext,
  PRIMARY KEY (`id_styles`,`id_fields`),
  KEY `id_styles` (`id_styles`),
  KEY `id_fields` (`id_fields`),
  CONSTRAINT `styles_fields_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `styles_fields_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `styles_fields`
--

LOCK TABLES `styles_fields` WRITE;
/*!40000 ALTER TABLE `styles_fields` DISABLE KEYS */;
INSERT INTO `styles_fields` VALUES (0000000001,0000000001,NULL,NULL),(0000000001,0000000002,NULL,NULL),(0000000001,0000000003,NULL,NULL),(0000000001,0000000004,NULL,NULL),(0000000001,0000000005,NULL,NULL),(0000000001,0000000007,NULL,NULL),(0000000001,0000000028,'dark',NULL),(0000000002,0000000005,NULL,NULL),(0000000002,0000000006,NULL,NULL),(0000000002,0000000019,NULL,NULL),(0000000002,0000000020,NULL,NULL),(0000000002,0000000035,NULL,NULL),(0000000003,0000000006,NULL,NULL),(0000000003,0000000029,'0','Select for a full width container, spanning the entire width of the viewport.'),(0000000004,0000000006,NULL,NULL),(0000000005,0000000021,'1','The HTML heading level (1-6)'),(0000000005,0000000022,NULL,NULL),(0000000006,0000000025,NULL,'Use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> syntax here.'),(0000000007,0000000026,NULL,'Only use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> elements that can be displayed inline (e.g. bold, italic, etc).'),(0000000008,0000000008,NULL,'The text to appear on the button.'),(0000000008,0000000027,NULL,'Use a full URL or any special characters as defined <a href=\"https://selfhelp.psy.unibe.ch/demo/style/440\" target=\"_blank\">here</a>.'),(0000000008,0000000028,'primary','The <a href=\"https://getbootstrap.com/docs/4.1/components/buttons/#examples\" target=\"_blank\">bootstrap type</a> of the button.'),(0000000009,0000000002,NULL,NULL),(0000000009,0000000003,NULL,NULL),(0000000009,0000000005,NULL,NULL),(0000000009,0000000006,NULL,NULL),(0000000009,0000000009,NULL,NULL),(0000000009,0000000022,NULL,NULL),(0000000009,0000000034,NULL,NULL),(0000000009,0000000035,NULL,NULL),(0000000009,0000000036,NULL,NULL),(0000000009,0000000037,NULL,NULL),(0000000009,0000000038,NULL,NULL),(0000000009,0000000039,NULL,NULL),(0000000009,0000000040,NULL,NULL),(0000000009,0000000041,NULL,NULL),(0000000009,0000000042,NULL,NULL),(0000000009,0000000043,NULL,NULL),(0000000009,0000000044,NULL,NULL),(0000000009,0000000057,NULL,NULL),(0000000010,0000000005,NULL,'The alert to be shown if the message could not be sent.'),(0000000010,0000000030,NULL,'This text is displayed when an experimenter has not yet chosen a subject to chat with.'),(0000000010,0000000031,NULL,'The prefix of the chat title which serves to indicate to the user with whom he/she is talking. The chat title is composed as follows:\n- if user is an experimenter the title is composed from the field `title_prefix` and the selected subject_name\n- if user is a subject the title is composed from the fields `title_prefix` and `experimenter`.'),(0000000010,0000000032,NULL,'The postfix of the chat title which serves to indicate to the subject with whom he/she is talking. Only a subject sees this. It should be a general description of experimenters. The chat title is composed as follows:\n- if user is an experimenter the title is composed from the field `title_prefix` and the selected subject_name\n- if user is a subject the title is composed from the fields `title_prefix` and `experimenter`'),(0000000010,0000000033,NULL,'The title of on the collapsed list of subjects (only on small screens).'),(0000000010,0000000090,NULL,'The label on the button to send a message.'),(0000000010,0000000095,'Lobby','The name of the default chat room.'),(0000000010,0000000096,'New Messages','The label to be displayed in the chat window that seperates new messges from old ones.'),(0000000010,0000000110,NULL,'The notification email to be sent to receiver of the chat message. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@link` will be replaced by the link to the chat page.'),(0000000010,0000000111,NULL,'The subject of the notification email to be sent to the receiver of the chat message. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.'),(0000000010,0000000114,'0','If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext'),(0000000011,0000000006,NULL,'The child elements to be added to the alert wrapper.'),(0000000011,0000000028,'primary','The bootstrap color styling of the alert wrapper.'),(0000000011,0000000045,'0','If *checked* the alert wrapper can be dismissed by clicking on a close symbol.\r\nIf *unchecked* the close symbol is not rendered.'),(0000000012,0000000006,NULL,'The child elements to be added to the card body.'),(0000000012,0000000022,NULL,'The content of the card header. If not set, the card will be rendered without a header section.'),(0000000012,0000000028,'light','A bootstrap-esque color styling of the card border and header.'),(0000000012,0000000046,'1','If the field `is_collapsible` is *checked* and the field `is_expanded` is *unchecked* the card is collapsed by default and only by clicking on the header will the body be shown. This field has no effect if `is_collapsible` is left *unchecked*.'),(0000000012,0000000047,'0','If *checked* the card body can be collapsed into the header by clicking on the header.\nIf left *unchecked* no such interaction is possible.'),(0000000012,0000000048,NULL,'The target url of the edit button. If set, an edit button will appear on right of the card header and link to the specified url. If not set no button will be shown.'),(0000000013,0000000006,NULL,NULL),(0000000013,0000000049,NULL,NULL),(0000000013,0000000050,NULL,NULL),(0000000014,0000000006,NULL,NULL),(0000000014,0000000008,NULL,NULL),(0000000014,0000000027,NULL,NULL),(0000000014,0000000028,NULL,NULL),(0000000014,0000000051,NULL,NULL),(0000000014,0000000052,NULL,NULL),(0000000015,0000000022,NULL,NULL),(0000000015,0000000029,'1',NULL),(0000000015,0000000030,NULL,NULL),(0000000015,0000000053,NULL,NULL),(0000000016,0000000008,NULL,NULL),(0000000016,0000000054,'text',NULL),(0000000016,0000000055,NULL,NULL),(0000000016,0000000056,'0',NULL),(0000000016,0000000057,NULL,NULL),(0000000016,0000000058,NULL,NULL),(0000000017,0000000024,NULL,NULL),(0000000017,0000000059,'0',NULL),(0000000018,0000000008,NULL,NULL),(0000000018,0000000027,NULL,NULL),(0000000018,0000000086,NULL,NULL),(0000000019,0000000028,'primary',NULL),(0000000019,0000000060,'0',NULL),(0000000019,0000000061,'1',NULL),(0000000019,0000000101,'1',NULL),(0000000019,0000000102,'1',NULL),(0000000020,0000000028,'light',NULL),(0000000020,0000000050,NULL,NULL),(0000000020,0000000062,NULL,NULL),(0000000020,0000000063,NULL,NULL),(0000000020,0000000064,NULL,NULL),(0000000020,0000000065,NULL,NULL),(0000000021,0000000024,NULL,NULL),(0000000022,0000000008,NULL,NULL),(0000000022,0000000030,NULL,NULL),(0000000022,0000000056,'0',NULL),(0000000022,0000000057,NULL,NULL),(0000000022,0000000058,NULL,NULL),(0000000022,0000000066,NULL,NULL),(0000000022,0000000067,'0',NULL),(0000000023,0000000008,NULL,NULL),(0000000023,0000000057,NULL,NULL),(0000000023,0000000058,NULL,NULL),(0000000023,0000000068,NULL,NULL),(0000000023,0000000069,'0',NULL),(0000000023,0000000070,'5',NULL),(0000000024,0000000006,NULL,NULL),(0000000024,0000000008,NULL,NULL),(0000000024,0000000028,'light',NULL),(0000000024,0000000046,'0',NULL),(0000000025,0000000006,NULL,NULL),(0000000026,0000000008,NULL,NULL),(0000000026,0000000055,NULL,NULL),(0000000026,0000000056,'0',NULL),(0000000026,0000000057,NULL,NULL),(0000000026,0000000058,NULL,NULL),(0000000027,0000000029,'1',NULL),(0000000027,0000000030,NULL,NULL),(0000000027,0000000071,NULL,NULL),(0000000028,0000000031,NULL,NULL),(0000000028,0000000066,NULL,NULL),(0000000028,0000000072,NULL,NULL),(0000000028,0000000083,NULL,NULL),(0000000028,0000000084,'0',NULL),(0000000030,0000000006,NULL,'Add sections here wich will be rendered below the content defined in field `text_md`.'),(0000000030,0000000022,NULL,'All navigation sections of a navigation page can be rendered as a list style. This field specifies the name of this navigation section to be used in such a list style.'),(0000000030,0000000025,'# @title','The content (markdown) of this field will be rendered at the top of the navigation section. Further sections added through the field `children` will be rendered below this. Note that here, the keyword `@title` can be used and will be replaced by the content of the field `title`.'),(0000000031,0000000029,'1',NULL),(0000000031,0000000031,NULL,NULL),(0000000031,0000000072,NULL,NULL),(0000000031,0000000073,NULL,NULL),(0000000031,0000000074,NULL,NULL),(0000000031,0000000075,NULL,NULL),(0000000031,0000000104,'1',NULL),(0000000032,0000000031,NULL,NULL),(0000000032,0000000046,'0',NULL),(0000000032,0000000047,'1',NULL),(0000000032,0000000066,NULL,NULL),(0000000032,0000000077,NULL,NULL),(0000000032,0000000083,NULL,NULL),(0000000032,0000000084,'0',NULL),(0000000033,0000000023,NULL,'Use this field to add custom CSS classes to the root navigation page container.'),(0000000033,0000000029,'1',NULL),(0000000033,0000000031,NULL,NULL),(0000000033,0000000046,'1',NULL),(0000000033,0000000047,'0',NULL),(0000000033,0000000073,NULL,NULL),(0000000033,0000000074,NULL,NULL),(0000000033,0000000075,'1',NULL),(0000000033,0000000077,NULL,NULL),(0000000033,0000000089,NULL,'Use this field to add custom CSS classes to the navigation menu of a navigation page.'),(0000000033,0000000104,'1',NULL),(0000000034,0000000066,NULL,NULL),(0000000034,0000000078,'0',NULL),(0000000034,0000000079,'0',NULL),(0000000034,0000000080,NULL,NULL),(0000000034,0000000081,NULL,NULL),(0000000034,0000000082,NULL,NULL),(0000000035,0000000004,NULL,'The label on the submit button.'),(0000000035,0000000025,NULL,'The description to be displayed on the page when a user wants to reset the password.'),(0000000035,0000000028,NULL,'The bootstrap color of the submit button.'),(0000000035,0000000035,NULL,'The success message to be shown when an email address was successfully stored in the database (if enabled) and the automatic emails were sent successfully.'),(0000000035,0000000055,NULL,'The placeholder in the email input field.'),(0000000035,0000000110,NULL,'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@link` will be replaced by the activation link the user needs to reset the password.'),(0000000035,0000000111,NULL,'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.'),(0000000035,0000000114,'0','If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext'),(0000000036,0000000006,NULL,NULL),(0000000036,0000000008,NULL,NULL),(0000000036,0000000028,'primary',NULL),(0000000036,0000000035,NULL,NULL),(0000000036,0000000057,NULL,NULL),(0000000036,0000000087,'0',NULL),(0000000038,0000000008,NULL,NULL),(0000000038,0000000056,'0',NULL),(0000000038,0000000057,NULL,NULL),(0000000038,0000000058,NULL,NULL),(0000000038,0000000066,NULL,NULL),(0000000038,0000000085,NULL,NULL),(0000000039,0000000012,NULL,'The title of the modal form that pops up when the delete button is clicked.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.'),(0000000039,0000000013,NULL,'The label of the remove button of the modal form.\n\nNote the following important points:\n- this field only has an effect if `is_log` is enabled.\n- if this field is not set, the remove button is not rendered.\n- entries that are removed with this button are only marked as removed but not deleted from the DB.'),(0000000039,0000000014,NULL,'The content of the modal form that pops up when the delete button is clicked.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.'),(0000000039,0000000053,NULL,'The name of the source form (i.e. the field `name` of the target form style).'),(0000000039,0000000087,'0','If *checked*, the style will render a table where each row represents all fields of the source form at the time instant of data submission.\n\nIf left *unchecked*, a table is rendered where each row represents one field of the source form.\n\nNote the following important points:\n- Check this only if the source form also has `is_log` checked.\n- The fields, `delete_title`, `label_date_time`, `label_delete`, and `delete_content` only have an effect if `is_log` is *checked*.'),(0000000039,0000000088,NULL,'The column title of the timestamp column.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.'),(0000000040,0000000006,NULL,NULL),(0000000041,0000000001,NULL,NULL),(0000000041,0000000002,NULL,NULL),(0000000041,0000000005,NULL,NULL),(0000000041,0000000022,NULL,NULL),(0000000041,0000000028,'success',NULL),(0000000041,0000000035,NULL,NULL),(0000000041,0000000044,NULL,NULL),(0000000041,0000000090,NULL,NULL),(0000000042,0000000006,NULL,'The children to be rendered if the condition defined by the field `condition` resolves to true.'),(0000000042,0000000091,NULL,'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `\"@__form_name__#__from_field_name__\"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.'),(0000000042,0000000097,'0','If *checked*, debug messages will be rendered to the screen. These might help to understand the result of a condition evaluation. **Make sure that this field is *unchecked* once the page is productive**.'),(0000000043,0000000030,NULL,NULL),(0000000043,0000000071,NULL,NULL),(0000000044,0000000071,NULL,NULL),(0000000044,0000000083,NULL,NULL),(0000000044,0000000099,'1',NULL),(0000000044,0000000100,'0',NULL),(0000000044,0000000103,'0',NULL),(0000000045,0000000105,NULL,'The JSON string to specify the (potentially) nested base styles.\r\n\r\nThere are a few things to note:\r\n - the key `_baseStyle` must be used to indicate that the assigned object is a *style object*\r\n - the *style object* must contain the key `_name` where the value must match a style name\r\n - the *style object* must contain the key `_fields` where the value is an object holding all required fields of the style (refer to the <a href=\"https://selfhelp.psy.unibe.ch/demo/styles\" target=\"_blank\">style documentation</a> for more information)'),(0000000046,0000000028,NULL,'.Use the type to change the appearance of individual progress bars'),(0000000046,0000000101,NULL,'iIf set apply a stripe via CSS gradient over the progress bar’s background color.'),(0000000046,0000000102,NULL,'If set display the progress in numbers ontop of the progress bar.'),(0000000047,0000000006,NULL,'Add only styles from type `input` for the edditable nodes. If they have input they could be eddited by the subject when they are clicked.'),(0000000047,0000000008,NULL,'Label of the form'),(0000000047,0000000028,NULL,'Type of the form'),(0000000047,0000000035,NULL,'The alert message for the succes'),(0000000047,0000000057,NULL,'Name of the form'),(0000000047,0000000107,NULL,'Use <a href=\"https://mermaidjs.github.io/demos.html\" target=\"_blank\">mermaid markdown</a> syntax here.'),(0000000048,0000000008,NULL,'The label on the submit button.'),(0000000048,0000000028,NULL,'The bootstrap color of the submit button.'),(0000000048,0000000035,NULL,'The success message to be shown when an email address was successfully stored in the database (if enabled) and the automatic emails were sent successfully.'),(0000000048,0000000055,NULL,'The placeholder in the email input field.'),(0000000048,0000000108,NULL,'A list of email addresses to be notified on submit with an email as defined in field `email_admins`. Use `json` syntax to specify the list of admins (e.g. `[\"__admin_1__\", ..., \"__admin_n__\"]`) where `__admin_*__` is the email address of an admin.'),(0000000048,0000000109,NULL,'The email to be sent to the the list of admins defined in the field `admins`. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@email` will be replaced by the email address entered in the form.'),(0000000048,0000000110,NULL,'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content.\n'),(0000000048,0000000111,NULL,'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.'),(0000000048,0000000112,NULL,'The list of attachments to the email to be sent to the the address that was entered into the form. Use `json` syntax to specify a list of assets (e.g. `[\"__asset_1__\", ..., \"__asset_n__\"]`) where `__asset_*__` is the name of an uploaded asset.'),(0000000048,0000000113,'0','If checked, the entered email address will be stored in the database.'),(0000000048,0000000114,'0','If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext');
/*!40000 ALTER TABLE `styles_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userStatus`
--

DROP TABLE IF EXISTS `userStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userStatus` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userStatus`
--

LOCK TABLES `userStatus` WRITE;
/*!40000 ALTER TABLE `userStatus` DISABLE KEYS */;
INSERT INTO `userStatus` VALUES (0000000001,'interested','This user has shown interest in the platform but has not yet met the preconditions to be invited.'),(0000000002,'invited','This user was invited to join the platform but has not yet validated the email address.'),(0000000003,'active','This user can log in and visit all accessible pages.');
/*!40000 ALTER TABLE `userStatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_activity` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned zerofill NOT NULL,
  `url` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_type` int(10) unsigned zerofill NOT NULL DEFAULT '0000000001',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_type` (`id_type`),
  CONSTRAINT `fk_user_activity_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `activityType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_activity_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity`
--

LOCK TABLES `user_activity` WRITE;
/*!40000 ALTER TABLE `user_activity` DISABLE KEYS */;
INSERT INTO `user_activity` VALUES (0000000001,0000000002,'/selfhelp/admin/export/user_input_form/all/55','2019-11-28 16:49:46',0000000002);
/*!40000 ALTER TABLE `user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_input`
--

DROP TABLE IF EXISTS `user_input`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_input` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_users` int(10) unsigned zerofill NOT NULL,
  `id_sections` int(10) unsigned zerofill NOT NULL,
  `id_section_form` int(10) unsigned zerofill NOT NULL,
  `value` longtext NOT NULL,
  `edit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `removed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_sections` (`id_sections`),
  KEY `id_section_form` (`id_section_form`),
  CONSTRAINT `user_input_fk_id_section_form` FOREIGN KEY (`id_section_form`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_input_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_input_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_input`
--

LOCK TABLES `user_input` WRITE;
/*!40000 ALTER TABLE `user_input` DISABLE KEYS */;
INSERT INTO `user_input` VALUES (0000000001,0000000003,0000000056,0000000055,'','2019-11-19 11:21:28',0),(0000000002,0000000003,0000000057,0000000055,'','2019-11-19 11:21:28',0),(0000000003,0000000004,0000000056,0000000055,'','2019-11-19 11:21:28',0),(0000000004,0000000004,0000000057,0000000055,'','2019-11-19 11:21:28',0);
/*!40000 ALTER TABLE `user_input` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_genders` (`id_genders`),
  KEY `id_languages` (`id_languages`),
  KEY `id_status` (`id_status`),
  CONSTRAINT `fk_users_id_genders` FOREIGN KEY (`id_genders`) REFERENCES `genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_id_status` FOREIGN KEY (`id_status`) REFERENCES `userStatus` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0000000001,'guest','',NULL,NULL,0,NULL,1,NULL,NULL,0,NULL,NULL),(0000000002,'admin','admin','$2y$10$lqb/Eieowq8lWTUxVrb1MOHrZ1ZDvbnU4RNvWxqP5pa8/QOdwFB8e',NULL,0,0000000003,0,NULL,NULL,1,NULL,NULL),(0000000003,'tpf','TPF','$2y$10$VxLANpP09THlDIDDfvL7PurilxKZ8vU8WzdGdfCYkdeBgy7hUkiUu',0000000001,0,0000000003,0,NULL,NULL,0,NULL,NULL),(0000000004,'sysadmin','sysadmin','$2y$10$H5MhmUF3cLLMNayuIQ4g.OXikV528bDOkConwtVBjdpj4rqrUtAXu',0000000001,0,0000000003,0,NULL,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_groups` (
  `id_users` int(10) unsigned zerofill NOT NULL,
  `id_groups` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_users`,`id_groups`),
  KEY `id_users` (`id_users`),
  KEY `id_groups` (`id_groups`),
  CONSTRAINT `fk_users_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_groups_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_groups`
--

LOCK TABLES `users_groups` WRITE;
/*!40000 ALTER TABLE `users_groups` DISABLE KEYS */;
INSERT INTO `users_groups` VALUES (0000000002,0000000001),(0000000003,0000000001),(0000000004,0000000001);
/*!40000 ALTER TABLE `users_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `validation_codes`
--

DROP TABLE IF EXISTS `validation_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `validation_codes` (
  `code` varchar(16) NOT NULL,
  `id_users` int(10) unsigned zerofill DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `consumed` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `id_users` (`id_users`),
  CONSTRAINT `validation_codes_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `validation_codes`
--

LOCK TABLES `validation_codes` WRITE;
/*!40000 ALTER TABLE `validation_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `validation_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `view_fields`
--

DROP TABLE IF EXISTS `view_fields`;
/*!50001 DROP VIEW IF EXISTS `view_fields`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `view_fields` AS SELECT 
 1 AS `field_id`,
 1 AS `field_name`,
 1 AS `display`,
 1 AS `field_type_id`,
 1 AS `field_type`,
 1 AS `position`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `view_style_fields`
--

DROP TABLE IF EXISTS `view_style_fields`;
/*!50001 DROP VIEW IF EXISTS `view_style_fields`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
 1 AS `help`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `view_styles`
--

DROP TABLE IF EXISTS `view_styles`;
/*!50001 DROP VIEW IF EXISTS `view_styles`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
-- Temporary table structure for view `view_user_input`
--

DROP TABLE IF EXISTS `view_user_input`;
/*!50001 DROP VIEW IF EXISTS `view_user_input`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `view_user_input` AS SELECT 
 1 AS `id`,
 1 AS `user_id`,
 1 AS `user_name`,
 1 AS `form_id`,
 1 AS `form_name`,
 1 AS `field_id`,
 1 AS `field_name`,
 1 AS `value`,
 1 AS `edit_time`,
 1 AS `removed`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'selfhelp'
--

--
-- Final view structure for view `view_fields`
--

/*!50001 DROP VIEW IF EXISTS `view_fields`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`bashev`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `view_fields` AS select cast(`f`.`id` as unsigned) AS `field_id`,`f`.`name` AS `field_name`,`f`.`display` AS `display`,cast(`ft`.`id` as unsigned) AS `field_type_id`,`ft`.`name` AS `field_type`,`ft`.`position` AS `position` from (`fields` `f` left join `fieldType` `ft` on((`f`.`id_type` = `ft`.`id`))) */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`bashev`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `view_style_fields` AS select `s`.`style_id` AS `style_id`,`s`.`style_name` AS `style_name`,`s`.`style_type` AS `style_type`,`s`.`style_group` AS `style_group`,`f`.`field_id` AS `field_id`,`f`.`field_name` AS `field_name`,`f`.`field_type` AS `field_type`,`f`.`display` AS `display`,`f`.`position` AS `position`,`sf`.`default_value` AS `default_value`,`sf`.`help` AS `help` from ((`view_styles` `s` left join `styles_fields` `sf` on((`s`.`style_id` = `sf`.`id_styles`))) left join `view_fields` `f` on((`f`.`field_id` = `sf`.`id_fields`))) */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`bashev`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `view_styles` AS select cast(`s`.`id` as unsigned) AS `style_id`,`s`.`name` AS `style_name`,`s`.`description` AS `style_description`,cast(`st`.`id` as unsigned) AS `style_type_id`,`st`.`name` AS `style_type`,cast(`sg`.`id` as unsigned) AS `style_group_id`,`sg`.`name` AS `style_group`,`sg`.`description` AS `style_group_description`,`sg`.`position` AS `style_group_position` from ((`styles` `s` left join `styleType` `st` on((`s`.`id_type` = `st`.`id`))) left join `styleGroup` `sg` on((`s`.`id_group` = `sg`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user_input`
--

/*!50001 DROP VIEW IF EXISTS `view_user_input`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`bashev`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `view_user_input` AS select cast(`ui`.`id` as unsigned) AS `id`,cast(`u`.`id` as unsigned) AS `user_id`,`u`.`name` AS `user_name`,cast(`form`.`id` as unsigned) AS `form_id`,`sft_if`.`content` AS `form_name`,cast(`field`.`id` as unsigned) AS `field_id`,`sft_in`.`content` AS `field_name`,`ui`.`value` AS `value`,`ui`.`edit_time` AS `edit_time`,`ui`.`removed` AS `removed` from (((((`user_input` `ui` left join `users` `u` on((`ui`.`id_users` = `u`.`id`))) left join `sections` `field` on((`ui`.`id_sections` = `field`.`id`))) left join `sections` `form` on((`ui`.`id_section_form` = `form`.`id`))) left join `sections_fields_translation` `sft_in` on(((`sft_in`.`id_sections` = `ui`.`id_sections`) and (`sft_in`.`id_fields` = 57)))) left join `sections_fields_translation` `sft_if` on(((`sft_if`.`id_sections` = `ui`.`id_section_form`) and (`sft_if`.`id_fields` = 57)))) */;
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

-- Dump completed on 2019-11-28 16:54:46
