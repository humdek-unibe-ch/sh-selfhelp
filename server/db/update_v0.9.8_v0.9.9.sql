-- Adding the style conditional container to the db

INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'conditionalContainer', '0000000002', '0000000004');
SET @id_style_conditionalContainer = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'condition', '0000000008', '0');
SET @id_field_condition = LAST_INSERT_ID();

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES (@id_style_conditionalContainer, @id_field_condition, NULL), (@id_style_conditionalContainer, '0000000006', NULL);

UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `conditionalContainer` is a **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition is true.\r\n- `container` is an **invisible** wrapper.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styleGroup`.`id` = 0000000004;
