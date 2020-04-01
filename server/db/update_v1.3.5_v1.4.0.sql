-- Add internal style autocomplete
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'autocomplete', '0000000001', '0000000001', 'Provides a text input field which executes an AJAX request on typing.\r\nA AJAX request class and method must be defined for this to work.');
SET @id_style = LAST_INSERT_ID();
-- Add new fields used for style autocomplete
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'name_value_field', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'callback_class', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'callback_method', @id_field_type, '0');
-- Assign fields to style autocomplete
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name_value_field');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the hidden autocomplete value input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'placeholder');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The placeholder text to be displayed in the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'True if the field is required, false otherwise.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The label to be displayed above the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'callback_class');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the class to be instantiated in the AJAX request.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'callback_method');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the method to be called on the class instance as defined in `callback_class`.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'debug');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'If set to true, debug information is shown in an alert box.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The default value to be set in the hidden autocomplete value input field.');

-- Add new field type and field
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'data-source', '15');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'data-source');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'data-source', @id_field_type, '0');

-- Alter asset page url
SET @id_page = (SELECT `id` FROM `pages` WHERE `keyword` = 'assetInsert');
UPDATE `pages` SET `url` = '/admin/asset_insert/[css|asset|static:mode]' WHERE `pages`.`id` = @id_page;
SET @id_page = (SELECT `id` FROM `pages` WHERE `keyword` = 'assetDelete');
UPDATE `pages` SET `url` = '/admin/asset_delete/[css|asset|static:mode]/[*:file]' WHERE `pages`.`id` = @id_page;

--
-- Table structure for table `uploadCells`
--

CREATE TABLE `uploadCells` (
  `id_uploadRows` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadCols` int(10) UNSIGNED ZEROFILL NOT NULL,
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadCols`
--

CREATE TABLE `uploadCols` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadRows`
--

CREATE TABLE `uploadRows` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadTables`
--

CREATE TABLE `uploadTables` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `uploadCells`
--
ALTER TABLE `uploadCells`
  ADD PRIMARY KEY (`id_uploadRows`,`id_uploadCols`),
  ADD KEY `id_uploadRows` (`id_uploadRows`),
  ADD KEY `id_uploadCols` (`id_uploadCols`);

--
-- Indexes for table `uploadCols`
--
ALTER TABLE `uploadCols`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uploadTables` (`id_uploadTables`);

--
-- Indexes for table `uploadRows`
--
ALTER TABLE `uploadRows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uploadTables` (`id_uploadTables`);

--
-- Indexes for table `uploadTables`
--
ALTER TABLE `uploadTables`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `uploadCols`
--
ALTER TABLE `uploadCols`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `uploadRows`
--
ALTER TABLE `uploadRows`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `uploadTables`
--
ALTER TABLE `uploadTables`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

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

-- important index for faster performance  
CREATE INDEX idx_uploadTables_name_timestamp ON uploadTables (name, timestamp);
