--
-- Table structure for table `styleGroup`
--

CREATE TABLE `styleGroup` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` longtext,
  `position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `styleGroup`
--

INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES
(0000000001, 'intern', NULL, NULL),
(0000000002, 'Form', 'A form is a wrapper for input fields. It allows to send content of the input fields to the server and store the data to the database. Several style are available:\r\n\r\n- `form` provides only the client-side functionality and does not do anything with the submitted data. This is intended to be connected with a custom component (required PHP programming).\r\n- `formDoc` stored the data from all child input fields into the database and displays the latest set of data in the database as values in the child input fields. Each form submission updates the values in the database.\r\n- `formLog` stores the data from all child input fields into the database together with a timestamp. Each form submission is treated as a separate item. This is useful for journals where e.g. each day a new entry must be created.', 60),
(0000000003, 'Input', 'An input field must be placed inside a form wrapper. An input field allows a user to enter data and submit these to the server. The chosen form wrapper decides what happens with the submitted data. The following input fields styles are available:\r\n\r\n- `input` is a one-line input field style that allows to enter different types of data (e.g. text, color, time, date, checkbox).\r\n- `radio` allows to predefine a set of options for the user to select. It provides a list of options where only one option can be chosen.\r\n- `select` is a input field style that provides a predefined set of choices which can be selected with a dropdown menu. In contrast to the radio style the select style has a different visual appearance and provides a list of options where also multiple options can be chosen.\r\n- `slider` is an extension of the style input of type range. It allows to provide a label for each position of the slider.\r\n- `textarea` is a multi-line input field style that allows to enter multiple lines of text.', 70),
(0000000004, 'Wrapper', 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with css classes. The following wrapers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `container` is an **invisible** wrapper.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visisble** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.', 10),
(0000000005, 'Text', 'Text styles allow to control how text is displayed. These styles are used to create the main content. The following styles are available:\r\n\r\n\r\n- `heading` is used to display the 6 levels of HTML headings.\r\n- `markdown` is the bread-and-butter style which allows to style content in a very flexible way. In addition to markdown syntax, pure HTML statements are allowed which makes this style very versatile. It is recommended to limit the usage of HTML to a minimum in order to keep the layout of the webpage consistent.\r\n- `markdownInline` is similar to the markdown style but is intended for one-line text where emphasis is required.\r\n- `plaintext` renders simple text. No special syntax is allowed here.\r\n- `rawText` renders text in a mono-space font which makes it useful to display code.', 20),
(0000000006, 'List', 'Lists are styles that allow to define more sophisticated lists than the markdown syntax allows. They come with attached javascript functionality. The following lists are available:\r\n\r\n- `accordionList` is a hierarchical list where the root level is rendered as an accordion whith only one root item expanded at a time.\r\n- `nestedList`is a hierarchical list where each root item item can be collapsed and expanded by clicking on a chevron.\r\n- `sortableList` is not hierarchical but can be sorted, new items can be added as well as items can be deleted. Note that only the visual aspects of these functions are rendered. The implementation of the functions need to be defined separately with javascript (See <a href=\"https://github.com/RubaXa/Sortable\" target=\"_blank\">Sortable</a> for more details).', 50),
(0000000007, 'Media', 'The media styles allow to display different media on a webpage. The following styles are available:\r\n\r\n- `figure` allows to attach a caption to media elements. A figure expects a media style as its immediate child.\r\n- `image` allows to render an image on a page.- `progressBar` allows to render a static progress bar.\r\n- `video` allows to load and display a video on a page.', 40),
(0000000008, 'Link', 'Link styles allow to render different types of links:\r\n\r\n- `button` renders a button-style link with several predefined colour schemes.\r\n- `link` renders a standard link but allows to open the target in a new tab.', 30);

--
-- Indexes for table `styleGroup`
--
ALTER TABLE `styleGroup`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `styleGroup`
--
ALTER TABLE `styleGroup`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- Remove column intern from table style
ALTER TABLE `styles` DROP `intern`;

-- Add column id_group to the table style
ALTER TABLE `styles` ADD `id_group` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '1' AFTER `id_type`, ADD INDEX (`id_group`);
ALTER TABLE `styles` ADD CONSTRAINT `styles_fk_id_group` FOREIGN KEY (`id_group`) REFERENCES `styleGroup`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Set style group values
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000003;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000004;
UPDATE `styles` SET `id_group` = '0000000005' WHERE `styles`.`id` = 0000000005;
UPDATE `styles` SET `id_group` = '0000000005' WHERE `styles`.`id` = 0000000006;
UPDATE `styles` SET `id_group` = '0000000005' WHERE `styles`.`id` = 0000000007;
UPDATE `styles` SET `id_group` = '0000000008' WHERE `styles`.`id` = 0000000008;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000011;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000012;
UPDATE `styles` SET `id_group` = '0000000007' WHERE `styles`.`id` = 0000000013;
UPDATE `styles` SET `id_group` = '0000000002' WHERE `styles`.`id` = 0000000014;
UPDATE `styles` SET `id_group` = '0000000007' WHERE `styles`.`id` = 0000000015;
UPDATE `styles` SET `id_group` = '0000000003' WHERE `styles`.`id` = 0000000016;
UPDATE `styles` SET `id_group` = '0000000005' WHERE `styles`.`id` = 0000000017;
UPDATE `styles` SET `id_group` = '0000000008' WHERE `styles`.`id` = 0000000018;
UPDATE `styles` SET `id_group` = '0000000007' WHERE `styles`.`id` = 0000000019;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000020;
UPDATE `styles` SET `id_group` = '0000000005' WHERE `styles`.`id` = 0000000021;
UPDATE `styles` SET `id_group` = '0000000003' WHERE `styles`.`id` = 0000000022;
UPDATE `styles` SET `id_group` = '0000000003' WHERE `styles`.`id` = 0000000023;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000024;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000025;
UPDATE `styles` SET `id_group` = '0000000003' WHERE `styles`.`id` = 0000000026;
UPDATE `styles` SET `id_group` = '0000000007' WHERE `styles`.`id` = 0000000027;
UPDATE `styles` SET `id_group` = '0000000006' WHERE `styles`.`id` = 0000000028;
UPDATE `styles` SET `id_group` = '0000000004' WHERE `styles`.`id` = 0000000030;
UPDATE `styles` SET `id_group` = '0000000006' WHERE `styles`.`id` = 0000000032;
UPDATE `styles` SET `id_group` = '0000000006' WHERE `styles`.`id` = 0000000034;
UPDATE `styles` SET `id_group` = '0000000002' WHERE `styles`.`id` = 0000000036;
UPDATE `styles` SET `id_group` = '0000000002' WHERE `styles`.`id` = 0000000037;
UPDATE `styles` SET `id_group` = '0000000003' WHERE `styles`.`id` = 0000000038;

-- Add the field is_expanded to the tab style
INSERT INTO `styles_fields` (`id_styles`, `id_fields`) VALUES ('0000000024', '0000000046');

-- Add default value to style field association
ALTER TABLE `styles_fields` ADD `default_value` VARCHAR(100) NULL DEFAULT NULL AFTER `id_fields`;
UPDATE `styles_fields` SET `default_value` = 'light' WHERE `styles_fields`.`id_styles` = 0000000012 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000012 AND `styles_fields`.`id_fields` = 0000000046;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000012 AND `styles_fields`.`id_fields` = 0000000047;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000028 AND `styles_fields`.`id_fields` = 0000000084;
UPDATE `styles_fields` SET `default_value` = 'primary' WHERE `styles_fields`.`id_styles` = 0000000011 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000011 AND `styles_fields`.`id_fields` = 0000000045;
UPDATE `styles_fields` SET `default_value` = 'primary' WHERE `styles_fields`.`id_styles` = 0000000008 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000003 AND `styles_fields`.`id_fields` = 0000000029;
UPDATE `styles_fields` SET `default_value` = 'primary' WHERE `styles_fields`.`id_styles` = 0000000036 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = 'primary' WHERE `styles_fields`.`id_styles` = 0000000037 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000016 AND `styles_fields`.`id_fields` = 0000000056;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000038 AND `styles_fields`.`id_fields` = 0000000056;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000022 AND `styles_fields`.`id_fields` = 0000000056;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000026 AND `styles_fields`.`id_fields` = 0000000056;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000005 AND `styles_fields`.`id_fields` = 0000000021;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000015 AND `styles_fields`.`id_fields` = 0000000029;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000027 AND `styles_fields`.`id_fields` = 0000000029;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000031 AND `styles_fields`.`id_fields` = 0000000029;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000033 AND `styles_fields`.`id_fields` = 0000000029;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000018 AND `styles_fields`.`id_fields` = 0000000087;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000032 AND `styles_fields`.`id_fields` = 0000000046;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000032 AND `styles_fields`.`id_fields` = 0000000047;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000032 AND `styles_fields`.`id_fields` = 0000000084;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000017 AND `styles_fields`.`id_fields` = 0000000059;
UPDATE `styles_fields` SET `default_value` = 'primary' WHERE `styles_fields`.`id_styles` = 0000000019 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000019 AND `styles_fields`.`id_fields` = 0000000060;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000019 AND `styles_fields`.`id_fields` = 0000000061;
UPDATE `styles_fields` SET `default_value` = 'light' WHERE `styles_fields`.`id_styles` = 0000000020 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000038 AND `styles_fields`.`id_fields` = 0000000086;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000022 AND `styles_fields`.`id_fields` = 0000000067;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000023 AND `styles_fields`.`id_fields` = 0000000069;
UPDATE `styles_fields` SET `default_value` = '5' WHERE `styles_fields`.`id_styles` = 0000000023 AND `styles_fields`.`id_fields` = 0000000070;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000034 AND `styles_fields`.`id_fields` = 0000000078;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000034 AND `styles_fields`.`id_fields` = 0000000079;
UPDATE `styles_fields` SET `default_value` = 'light' WHERE `styles_fields`.`id_styles` = 0000000024 AND `styles_fields`.`id_fields` = 0000000028;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000024 AND `styles_fields`.`id_fields` = 0000000046;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000033 AND `styles_fields`.`id_fields` = 0000000046;
UPDATE `styles_fields` SET `default_value` = '0' WHERE `styles_fields`.`id_styles` = 0000000033 AND `styles_fields`.`id_fields` = 0000000047;
UPDATE `styles_fields` SET `default_value` = '1' WHERE `styles_fields`.`id_styles` = 0000000033 AND `styles_fields`.`id_fields` = 0000000075;
UPDATE `styles_fields` SET `default_value` = 'text' WHERE `styles_fields`.`id_styles` = 0000000016 AND `styles_fields`.`id_fields` = 0000000054;
