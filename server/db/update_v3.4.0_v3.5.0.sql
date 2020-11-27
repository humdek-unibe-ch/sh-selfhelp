-- set DB version
UPDATE version
SET version = 'v3.5.0';

-- add trigers styleGroup
INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'Triggers', 'Trigger styles allow to attach an action that can be executed when the user fulfill the triger condition', 75);
SET @id_group = LAST_INSERT_ID();
-- add triger style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'trigger', '0000000002', @id_group, 'Create a basic trigger that execute selected action.');
SET @id_style = LAST_INSERT_ID();

-- Add new field type `select-plugin` and field `plugin` in style trigger
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-plugin', '8');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'plugin', get_field_type_id('select-plugin'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('trigger'), get_field_id('plugin'), '', 'Select a plugin which will execute a predifined action. Tip: Plugins are additional functionality and they are not included in the basic Selfhelp. If a plugin is missing contact your administrator!');

-- create table plugins
CREATE TABLE `plugins` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `name` varchar(500),
  `version` varchar(500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
