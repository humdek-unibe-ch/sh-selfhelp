--
-- Table structure for table `pages_fields`
--

CREATE TABLE `pages_fields` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `pages_fields`
--
ALTER TABLE `pages_fields`
  ADD PRIMARY KEY (`id_pages`,`id_fields`),
  ADD KEY `id_pages` (`id_pages`),
  ADD KEY `id_fields` (`id_fields`);

--
-- Constraints for table `pages_fields`
--
ALTER TABLE `pages_fields`
  ADD CONSTRAINT `fk_page_fields_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_page_fields_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES('description', (SELECT id FROM `fieldType` WHERE `name` = 'textarea'), 1);
SET @id_field_description = LAST_INSERT_ID();
INSERT INTO `pages_fields` (`id_pages`, `id_fields`, `default_value`, `help`) VALUES((SELECT id FROM `pages` WHERE `keyword` = 'home'), @id_field_description, NULL, 'A short description of the research project. This field will be used as `meta:description` in the HTML header. Some services use this tag to provide the user with information on the webpage (e.g. automatic link-replacement in messaging tools on smartphones use this description.)');
