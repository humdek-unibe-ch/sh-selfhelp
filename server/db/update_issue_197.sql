SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'formUserInput');
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'anchor-section', '14');
SET @id_fieldType = LAST_INSERT_ID();
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'anchor', @id_fieldType, '0');
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Search for the name of the anchor section to jump to after submitting the form. The ID of the section will be used as anchor. If this field is not set the section ID of the form itself will be used as anchor. This is useful if the form is placed within a collapsable card and the form anchor is hidden. In this case it makes sense to use the parent card as anchor here.');
