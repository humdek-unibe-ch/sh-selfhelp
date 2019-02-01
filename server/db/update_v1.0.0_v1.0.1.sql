-- seperate style description from style group description
ALTER TABLE `styles` ADD `description` LONGTEXT NOT NULL AFTER `id_group`;
UPDATE `styles` SET `description` = 'provides a small form where the user can enter his or her email and password to access the WebApp. It also includes a link to reset a password.' WHERE `styles`.`name` = "login";
UPDATE `styles` SET `description` = 'is an **invisible** wrapper.' WHERE `styles`.`name` = "container";
UPDATE `styles` SET `description` = 'is a **visible** wrapper that wraps its content in a grey box with large spacing.' WHERE `styles`.`name` = "jumbotron";
UPDATE `styles` SET `description` = 'is used to display the 6 levels of HTML headings.' WHERE `styles`.`name` = "heading";
UPDATE `styles` SET `description` = 'is the bread-and-butter style which allows to style content in a very flexible way. In addition to markdown syntax, pure HTML statements are allowed which makes this style very versatile. It is recommended to limit the usage of HTML to a minimum in order to keep the layout of the webpage consistent.' WHERE `styles`.`name` = "markdown";
UPDATE `styles` SET `description` = 'is similar to the markdown style but is intended for one-line text where emphasis is required.' WHERE `styles`.`name` = "markdownInline";
UPDATE `styles` SET `description` = 'renders a button-style link with several predefined colour schemes.' WHERE `styles`.`name` = "button";
UPDATE `styles` SET `description` = 'is a **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.' WHERE `styles`.`name` = "alert";
UPDATE `styles` SET `description` = 'is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.' WHERE `styles`.`name` = "card";
UPDATE `styles` SET `description` = 'allows to attach a caption to media elements. A figure expects a media style as its immediate child.' WHERE `styles`.`name` = "figure";
UPDATE `styles` SET `description` = 'provides only the client-side functionality and does not do anything with the submitted data. This is intended to be connected with a custom component (required PHP programming).' WHERE `styles`.`name` = "form";
UPDATE `styles` SET `description` = 'allows to render an image on a page.' WHERE `styles`.`name` = "image";
UPDATE `styles` SET `description` = 'is a one-line input field style that allows to enter different types of data (e.g. text, color, time, date, checkbox).' WHERE `styles`.`name` = "input";
UPDATE `styles` SET `description` = 'renders simple text. No special syntax is allowed here.' WHERE `styles`.`name` = "plaintext";
UPDATE `styles` SET `description` = 'renders a standard link but allows to open the target in a new tab.' WHERE `styles`.`name` = "link";
UPDATE `styles` SET `description` = 'allows to render a static progress bar.' WHERE `styles`.`name` = "progressBar";
UPDATE `styles` SET `description` = 'is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.' WHERE `styles`.`name` = "quiz";
UPDATE `styles` SET `description` = 'renders text in a mono-space font which makes it useful to display code.' WHERE `styles`.`name` = "rawText";
UPDATE `styles` SET `description` = 'is a input field style that provides a predefined set of choices which can be selected with a dropdown menu. In contrast to the radio style the select style has a different visual appearance and provides a list of options where also multiple options can be chosen.' WHERE `styles`.`name` = "select";
UPDATE `styles` SET `description` = 'is an extension of the style input of type range. It allows to provide a label for each position of the slider.' WHERE `styles`.`name` = "slider";
UPDATE `styles` SET `description` = 'is a child element of the style `tabs`.' WHERE `styles`.`name` = "tab";
UPDATE `styles` SET `description` = 'is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styles`.`name` = "tabs";
UPDATE `styles` SET `description` = 'is a multi-line input field style that allows to enter multiple lines of text.' WHERE `styles`.`name` = "textarea";
UPDATE `styles` SET `description` = 'allows to load and display a video on a page.' WHERE `styles`.`name` = "video";
UPDATE `styles` SET `description` = 'is a **hierarchical** list where the root level is rendered as an accordion with only one root item expanded at a time.' WHERE `styles`.`name` = "accordionList";
UPDATE `styles` SET `description` = 'is an **invisible** wrapper and is used specifically for navigation pages.' WHERE `styles`.`name` = "navigationContainer";
UPDATE `styles` SET `description` = 'is a **hierarchical** list where each root item item can be collapsed and expanded by clicking on a chevron.' WHERE `styles`.`name` = "nestedList";
UPDATE `styles` SET `description` = 'is **non-hierarchical** but can be sorted, new items can be added as well as items can be deleted. Note that only the visual aspects of these functions are rendered. The implementation of the functions need to be defined separately with javascript (See <a href=\"https://github.com/RubaXa/Sortable\" target=\"_blank\">Sortable</a> for more details).' WHERE `styles`.`name` = "sortableList";
UPDATE `styles` SET `description` = 'stores the data from all child input fields into the database and displays the latest set of data in the database as values in the child input field (if `is_log` is unchecked).' WHERE `styles`.`name` = "formUserInput";
UPDATE `styles` SET `description` = 'allows to predefine a set of options for the user to select. It provides a list of options where only one option can be chosen.' WHERE `styles`.`name` = "radio";
UPDATE `styles` SET `description` = 'allows to display user input data. Use the name of a form to display the corresponding data.' WHERE `styles`.`name` = "showUserInput";
UPDATE `styles` SET `description` = 'allows to wrap its children in a simple HTML `<div>` tag. This allows to create more complex layouts with the help of bootstrap classes.' WHERE `styles`.`name` = "div";
UPDATE `styles` SET `description` = 'provides a small form to allow a user to register for the WebApp. In order to register a user must provide a valid email and activation code. Activation codes can be generated in the admin section of the WebApp. The list of available codes can be exported.' WHERE `styles`.`name` = "register";
UPDATE `styles` SET `description` = 'is an **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition resolves to true.' WHERE `styles`.`name` = "conditionalContainer";
UPDATE `styles` SET `description` = 'allows to load and replay an audio source on a page.' WHERE `styles`.`name` = "audio";
UPDATE `styles` SET `description` = 'allows to render multiple images as a slide-show.' WHERE `styles`.`name` = "carousel";

UPDATE `styleGroup` SET `description` = 'A form is a wrapper for input fields. It allows to send content of the input fields to the server and store the data to the database. Several style are available:' WHERE `styleGroup`.`id` = 0000000002;
UPDATE `styleGroup` SET `description` = 'An input field must be placed inside a form wrapper. An input field allows a user to enter data and submit these to the server. The chosen form wrapper decides what happens with the submitted data. The following input fields styles are available:' WHERE `styleGroup`.`id` = 0000000003;
UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:' WHERE `styleGroup`.`id` = 0000000004;
UPDATE `styleGroup` SET `description` = 'Text styles allow to control how text is displayed. These styles are used to create the main content. The following styles are available:' WHERE `styleGroup`.`id` = 0000000005;
UPDATE `styleGroup` SET `description` = 'Lists are styles that allow to define more sophisticated lists than the markdown syntax allows. They come with attached javascript functionality. The following lists are available:' WHERE `styleGroup`.`id` = 0000000006;
UPDATE `styleGroup` SET `description` = 'The media styles allow to display different media on a webpage. The following styles are available:' WHERE `styleGroup`.`id` = 0000000007;
UPDATE `styleGroup` SET `description` = 'Link styles allow to render different types of links:' WHERE `styleGroup`.`id` = 0000000008;
UPDATE `styleGroup` SET `description` = 'The admin styles are for user registration and access handling.\r\nThe following styles are available:' WHERE `styleGroup`.`id` = 0000000009;

-- allow to enable/disable navigation menu
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_navigation_menu', '0000000003', '0');
SET @id_field_has_navigation_menu = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000033', @id_field_has_navigation_menu, '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000031', @id_field_has_navigation_menu, '1');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) SELECT id AS id_sections, @id_field_has_navigation_menu AS id_fields, 1 AS id_languages, 1 AS id_genders, 1 AS content FROM sections WHERE id_styles = 33 ON DUPLICATE KEY UPDATE content=1;
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) SELECT id AS id_sections, @id_field_has_navigation_menu AS id_fields, 1 AS id_languages, 1 AS id_genders, 1 AS content FROM sections WHERE id_styles = 31 ON DUPLICATE KEY UPDATE content=1;

-- set default of navigation container text_md field
UPDATE `styles_fields` SET `default_value` = '# @title' WHERE `styles_fields`.`id_styles` = 0000000030 AND `styles_fields`.`id_fields` = 0000000025;
