-- Adding the style conditional container to the db

INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'conditionalContainer', '0000000002', '0000000004');
SET @id_style_conditionalContainer = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'condition', '0000000008', '0');
SET @id_field_condition = LAST_INSERT_ID();

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES (@id_style_conditionalContainer, @id_field_condition, NULL), (@id_style_conditionalContainer, '0000000006', NULL);

UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `conditionalContainer` is a **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition is true.\r\n- `container` is an **invisible** wrapper.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styleGroup`.`id` = 0000000004;

UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.1.3 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.0/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.1/about/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = 0000000034 AND `sections_fields_translation`.`id_fields` = 0000000025 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;

UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.1.3 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.0/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.1/about/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = 0000000034 AND `sections_fields_translation`.`id_fields` = 0000000025 AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;

-- Audio Style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'audio', '0000000001', '0000000007');
SET @id_style_audio = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES (@id_style_audio, '0000000030', NULL), (@id_style_audio, '0000000071', NULL);
UPDATE `styleGroup` SET `description` = 'The media styles allow to display different media on a webpage. The following styles are available:\r\n\r\n- `audio` allows to load and replay an audio source on a page.\r\n- `figure` allows to attach a caption to media elements. A figure expects a media style as its immediate child.\r\n- `image` allows to render an image on a page.- `progressBar` allows to render a static progress bar.\r\n- `video` allows to load and display a video on a page.' WHERE `styleGroup`.`id` = 0000000007;