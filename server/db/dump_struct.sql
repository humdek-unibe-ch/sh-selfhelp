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

-- --------------------------------------------------------

--
-- Table structure for table `acl`
--

CREATE TABLE `acl` (
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `locale` varchar(5) NOT NULL COMMENT '"e.g en-GB, de-CH"',
  `language` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `parent` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `nav_position` int(11) DEFAULT NULL,
  `footer_position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

-- --------------------------------------------------------

--
-- Table structure for table `pages_sections`
--

CREATE TABLE `pages_sections` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

-- --------------------------------------------------------

--
-- Table structure for table `sections_fields_translation`
--

CREATE TABLE `sections_fields_translation` (
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sections_hierarchy`
--

CREATE TABLE `sections_hierarchy` (
  `parent` int(10) UNSIGNED ZEROFILL NOT NULL,
  `child` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sections_navigation`
--

CREATE TABLE `sections_navigation` (
  `parent` int(10) UNSIGNED ZEROFILL NOT NULL,
  `child` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `styles`
--

CREATE TABLE `styles` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `styles_content`
--

CREATE TABLE `styles_content` (
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `content` int(10) UNSIGNED ZEROFILL NOT NULL COMMENT 'The style that can be assigned to the content of a style.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Defines which styles can be used in the content of a style';

-- --------------------------------------------------------

--
-- Table structure for table `styles_fields`
--

CREATE TABLE `styles_fields` (
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `styles_fields_translation`
--

CREATE TABLE `styles_fields_translation` (
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001',
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`id_users`,`id_pages`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_pages` (`id_pages`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
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
  ADD KEY `parent` (`parent`);

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
  ADD PRIMARY KEY (`id_sections`,`id_fields`,`id_languages`),
  ADD KEY `id_sections` (`id_sections`),
  ADD KEY `id_fields` (`id_fields`),
  ADD KEY `id_languages` (`id_languages`);

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
  ADD KEY `parent` (`parent`);

--
-- Indexes for table `styles`
--
ALTER TABLE `styles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `styles_content`
--
ALTER TABLE `styles_content`
  ADD PRIMARY KEY (`id_styles`,`content`),
  ADD KEY `id_styles` (`id_styles`),
  ADD KEY `content` (`content`);

--
-- Indexes for table `styles_fields`
--
ALTER TABLE `styles_fields`
  ADD PRIMARY KEY (`id_styles`,`id_fields`),
  ADD KEY `id_styles` (`id_styles`),
  ADD KEY `id_fields` (`id_fields`);

--
-- Indexes for table `styles_fields_translation`
--
ALTER TABLE `styles_fields_translation`
  ADD PRIMARY KEY (`id_styles`,`id_fields`,`id_languages`),
  ADD KEY `id_styles` (`id_styles`),
  ADD KEY `id_fields` (`id_fields`),
  ADD KEY `id_languages` (`id_languages`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
--
-- AUTO_INCREMENT for table `styles`
--
ALTER TABLE `styles`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `acl`
--
ALTER TABLE `acl`
  ADD CONSTRAINT `acl_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `acl_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_fk_parent` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `sections_navigation_fk_parent` FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `styles_content`
--
ALTER TABLE `styles_content`
  ADD CONSTRAINT `styles_content_fk_content` FOREIGN KEY (`content`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_content_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `styles_fields`
--
ALTER TABLE `styles_fields`
  ADD CONSTRAINT `styles_fields_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_fields_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `styles_fields_translation`
--
ALTER TABLE `styles_fields_translation`
  ADD CONSTRAINT `styles_fields_translation_fk_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_fields_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `styles_fields_translation_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
