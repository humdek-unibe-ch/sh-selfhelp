INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'css_nav', '0000000001', '0')
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000033', '0000000089', NULL);

UPDATE `fields` SET `display` = '1' WHERE `fields`.`id` = 0000000053;
UPDATE `fields` SET `display` = '1' WHERE `fields`.`id` = 0000000071;
