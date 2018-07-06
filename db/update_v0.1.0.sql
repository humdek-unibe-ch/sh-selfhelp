--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `pages_translation`
--

CREATE TABLE `pages_translation` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `title` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `pages_translation`
--
ALTER TABLE `pages_translation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pages` (`id_pages`),
  ADD KEY `id_languages` (`id_languages`);

--
-- AUTO_INCREMENT for table `pages_translation`
--
ALTER TABLE `pages_translation`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

ALTER TABLE `pages_translation` CHANGE `id_languages` `id_languages` INT(10) UNSIGNED ZEROFILL NULL;

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `locale` varchar(5) NOT NULL COMMENT '"e.g en-GB, de-CH"',
  `language` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_styles` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_styles` (`id_styles`);

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `sections_translation`
--

CREATE TABLE `sections_translation` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_languages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `sections_translation`
--
ALTER TABLE `sections_translation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sections` (`id_sections`),
  ADD KEY `id_languages` (`id_languages`);

--
-- AUTO_INCREMENT for table `sections_translation`
--
ALTER TABLE `sections_translation`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

ALTER TABLE `sections_translation` CHANGE `id_languages` `id_languages` INT(10) UNSIGNED ZEROFILL NULL;

--
-- Table structure for table `pages_sections`
--

CREATE TABLE `pages_sections` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_sections` int(10) UNSIGNED ZEROFILL NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `pages_sections`
--
ALTER TABLE `pages_sections`
  ADD PRIMARY KEY (`id_pages`,`id_sections`),
  ADD KEY `id_pages` (`id_pages`),
  ADD KEY `id_sections` (`id_sections`);

--
-- Table structure for table `styles`
--

CREATE TABLE `styles` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `style` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `styles`
--
ALTER TABLE `styles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `styles`
--
ALTER TABLE `styles`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

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

--
-- Indexes for table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`id_users`,`id_pages`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_pages` (`id_pages`);

--
-- Add foreign key constraints for table `pages_translation`
--
ALTER TABLE `pages_translation`
  ADD CONSTRAINT `pages_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_translation_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Add foreign key constraints for table `sections_translation`
--
ALTER TABLE `sections_translation`
  ADD CONSTRAINT `sections_translation_fk_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sections_translation_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Add foreign key constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_fk_id_styles` FOREIGN KEY (`id_styles`) REFERENCES `styles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Add foreign key constraints for table `pages_sections`
--
ALTER TABLE `pages_sections`
  ADD CONSTRAINT `pages_sections_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pages_sections_fk_id_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Add foreign key constraints for table `acl`
--
ALTER TABLE `acl`
  ADD CONSTRAINT `acl_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `acl_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
