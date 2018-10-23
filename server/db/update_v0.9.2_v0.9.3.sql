-- Change field 'title' to style 'markdown-inline' (old id_type = 1)
UPDATE `fields` SET `id_type` = '0000000007' WHERE `fields`.`id` = 0000000022;

-- Add new style fields
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'is_inline', '0000000003', '0');

-- New field for style link
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'open_in_new_tab', '0000000003', '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000018', '0000000087');

-- New style 'formDoc'
INSERT INTO `styles` (`id`, `name`, `id_type`, `intern`) VALUES (NULL, 'formDoc', '0000000002', '0');

-- Create formDoc style field association
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000036', '0000000006');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000036', '0000000008');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000036', '0000000028');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000036', '0000000035');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000036', '0000000057');

-- New style 'formLog'
INSERT INTO `styles` (`id`, `name`, `id_type`, `intern`) VALUES (NULL, 'formLog', '0000000002', '0');

-- Create formLog style field association
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000037', '0000000006');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000037', '0000000008');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000037', '0000000028');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000037', '0000000035');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000037', '0000000057');

-- New style radio
INSERT INTO `styles` (`id`, `name`, `id_type`, `intern`) VALUES (NULL, 'radio', '0000000002', '0');

-- Create radio style field association
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000038', '0000000008');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000038', '0000000056');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000038', '0000000057');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000038', '0000000058');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000038', '0000000066');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000038', '0000000086');

-- Change formField styles to component styles (old id_type = 1)
UPDATE `styles` SET `id_type` = '0000000002' WHERE `styles`.`id` = 0000000016;
UPDATE `styles` SET `id_type` = '0000000002' WHERE `styles`.`id` = 0000000022;
UPDATE `styles` SET `id_type` = '0000000002' WHERE `styles`.`id` = 0000000023;
UPDATE `styles` SET `id_type` = '0000000002' WHERE `styles`.`id` = 0000000026;

-- Add form section id to user_input table
ALTER TABLE `user_input` ADD `id_section_form` INT UNSIGNED ZEROFILL NOT NULL AFTER `id_sections`, ADD INDEX (`id_section_form`);
ALTER TABLE `user_input` ADD CONSTRAINT `user_input_fk_id_section_form` FOREIGN KEY (`id_section_form`) REFERENCES `sections`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Add timestamp update trigger
ALTER TABLE `user_input` CHANGE `edit_time` `edit_time` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
