-- Add new field type and field
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'data-source', '15');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'data-source');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'data-source', @id_field_type, '0');
