-- set DB version
UPDATE version
SET version = 'v5.4.0';

UPDATE pages
SET id_actions = (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1)
WHERE keyword = 'admin-link';

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'confirmation_title', get_field_type_id('text'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('button'), get_field_id('confirmation_title'), '', 'Confirmation title for the modal when the button is clicked');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('button'), get_field_id('label_cancel'), '', 'Cancel button label on the confirmation modal');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_continue', get_field_type_id('text'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('button'), get_field_id('label_continue'), 'OK', 'Continue button for the modal when the button is clicked');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_message', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('button'), get_field_id('label_message'), 'Do you want to continue?', 'The message shown on the modal');