INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'autocomplete', '0000000001', '0000000001', 'Provides a text input field which executes an AJAX request on typing.\r\nA AJAX request class and method must be defined for this to work.');
SET @id_style = LAST_INSERT_ID();
