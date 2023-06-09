-- set DB version
UPDATE version
SET version = 'v6.3.0';

ALTER TABLE `tasks`
MODIFY COLUMN config LONGTEXT;

INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'password', '11');
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'panel', '0');

-- add title to the page calendar view 
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), get_field_id('title'), '0000000001', 'Scheduled Jobs - Calendar View');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), get_field_id('title'), '0000000002', 'Scheduled Jobs - Calendar View');

UPDATE pages
SET url = "/admin/scheduledJobs/calendar/[i:uid]?/[i:aid]?"
WHERE keyword = 'moduleScheduledJobsCalendar';

-- create table libraries
CREATE TABLE IF NOT EXISTS `libraries` (
	`id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
	`name` VARCHAR(250) UNIQUE,
	`version` VARCHAR(500),
	`license` VARCHAR(1000),
	`comments` VARCHAR(1000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add libraries entries in the libraries table
INSERT IGNORE INTO `libraries` (`name`, version, license, comments) VALUES
('[Altorouter](http://altorouter.com/)', '1.2.0', '[MIT](https://tldrlegal.com/license/mit-license)', '[License Details](http://altorouter.com/license.html)'),
('[Autosize](https://github.com/jackmoore/autosize)', '1.1.6', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Bootstrap](https://getbootstrap.com/)', '4.4.1', '[MIT](https://tldrlegal.com/license/mit-license)', '[Browser Support](https://getbootstrap.com/docs/4.4/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.4/about/license/)'),
('[Datatables](https://datatables.net/)', '1.10.18', '[MIT](https://tldrlegal.com/license/mit-license)', '[License Details](https://datatables.net/license/)'),
('[Deepmerge](https://github.com/TehShrike/deepmerge)', '4.2.2', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Font Awesome](https://fontawesome.com/)', '5.2.0', 'Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL)', '[Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free)'),
('[GUMP](https://github.com/Wixel/GUMP.git)', '1.5.6', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[jQuery](https://jquery.com/)', '3.3.1', '[MIT](https://tldrlegal.com/license/mit-license)', '[Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/)'),
('[JsonLogic](https://github.com/jwadhams/json-logic-php/)', '1.3.10', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[mermaid](https://mermaidjs.github.io/)', '8.2.3', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Parsedown](https://github.com/erusev/parsedown)', '1.7.1', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[PHPMailer](https://github.com/PHPMailer/PHPMailer)', '6.0.7', '[LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1))', '[License Details](https://github.com/PHPMailer/PHPMailer#license)'),
('[Plotly.js](https://plotly.com/javascript)', '1.52.3', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[ResizeSensor](https://github.com/marcj/css-element-queries)', '1.2.2', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Monaco Editor](https://github.com/microsoft/monaco-editor)', '0.33.0', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[EasyMDE](https://github.com/ionaru/easy-markdown-editor)', '2.16.1', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Flatpickr](https://github.com/flatpickr/flatpickr)', '4.6.13', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Html2pdf](https://github.com/eKoopmans/html2pdf.js)', '0.9.2', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Iscroll](https://github.com/cubiq/iscroll)', '4.2.5', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[jQuery QueryBuilder](https://github.com/mistic100/jQuery-QueryBuilder)', '2.6.0', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[jQueryConfirm](https://craftpip.github.io/jquery-confirm/)', '3.3.4', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[PHP-fcm)](https://github.com/EdwinHoksberg/php-fcm)', '1.2.0', '[MIT](https://tldrlegal.com/license/mit-license)', ''),
('[Sortable](https://rubaxa.github.io/Sortable/)', '1.7.0', '[MIT](https://tldrlegal.com/license/mit-license)', '');

-- remove the section for the old libraries
DELETE FROM sections
WHERE `name` = 'impressum-ext-card';

DELETE FROM sections
WHERE `name` = 'impressum-ext-markdown';

-- add margin to the home button on missing page
INSERT IGNORE INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
((SELECT id FROM sections WHERE `name` = 'goHome-button'), get_field_id('css'), 0000000001, 0000000001, 'ml-3');


-- add column anonymous_users to table cmsPreferences 
CALL add_table_column('cmsPreferences', 'anonymous_users', "INT(11) DEFAULT '0'");

DROP VIEW IF EXISTS view_cmsPreferences;
CREATE VIEW view_cmsPreferences
AS
SELECT p.callback_api_key, p.default_language_id, l.language as default_language, l.locale, p.fcm_api_key, p.fcm_sender_id, p.anonymous_users
FROM cmsPreferences p
LEFT JOIN languages l ON (l.id = p.default_language_id)
WHERE p.id = 1;

-- remove 'sh_globals' page form the menu
UPDATE pages 
SET nav_position = NULL
WHERE keyword = 'sh_globals';

-- add page type security_questions
INSERT IGNORE INTO `pageType` (`name`) VALUES ('sh_security_questions');

SET @id_page_globals = (SELECT id FROM pages WHERE keyword = 'sh_globals');
-- add page security_questions
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_security_questions', '/admin/security_questions', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, @id_page_globals, 0, 100, NULL, (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'web'));
-- set acl for the page
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', (SELECT id FROM pages WHERE keyword = 'sh_security_questions'), '1', '0', '1', '0');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('title'), '0000000001', 'Security Questions');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('title'), '0000000002', 'Security Questions');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');

-- add new filed `enable_reset_password` from type checkbox
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'enable_reset_password', get_field_type_id('checkbox'), '0');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('enable_reset_password'), NULL, "If selected, the user can reset the password with the answers of his/her security questions. All 3 security questions` answers should match in order to reset the password.");

-- security_question_01
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_01', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_01'), NULL, 'Security question 1 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_01'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Was ist deine Lieblingsfarbe?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_01'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is your favorite color?');

-- security_question_02
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_02', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_02'), NULL, 'Security question 2 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_02'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Wie hiess deine erste Schule?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_02'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What was the name of your first school?');

-- security_question_03
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_03', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_03'), NULL, 'Security question 3 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_03'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Was ist dein Lieblingsfilm?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_03'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is your favorite movie?');

-- security_question_04
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_04', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_04'), NULL, 'Security question 4 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_04'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'In welcher Stadt hast du deinen Ehepartner/Partner kennengelernt?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_04'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'In which city did you meet your spouse/partner?');

-- security_question_05
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_05', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_05'), NULL, 'Security question 5 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_05'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Was ist dein Lieblingssportteam?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_05'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is your favorite sports team?');

-- security_question_06
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_06', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_06'), NULL, 'Security question 6 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_06'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Wie lautet der Name deines besten Kindheitsfreunds?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_06'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is the name of your best childhood friend?');

-- security_question_07
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_07', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_07'), NULL, 'Security question 7 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_07'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Was ist dein Lieblingsurlaubsziel?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_07'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is your favorite holiday destination?');

-- security_question_08
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_08', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_08'), NULL, 'Security question 8 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_08'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Wie hiess dein erstes Haustier?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_08'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What was the name of your first pet?');

-- security_question_09
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_09', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_09'), NULL, 'Security question 9 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_09'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Wie lautet der zweite Vorname deines ältesten Geschwisters?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_09'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is the middle name of your oldest sibling?');

-- security_question_10
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'security_question_10', get_field_type_id('textarea'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 0,1), get_field_id('security_question_10'), NULL, 'Security question 10 description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_10'), (SELECT id FROM languages WHERE locale = 'de-CH' LIMIT 0,1), 'Was ist dein Lieblingsbuch?');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'sh_security_questions'), get_field_id('security_question_10'), (SELECT id FROM languages WHERE locale = 'en-GB' LIMIT 0,1), 'What is your favorite book?');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_security_question_1', get_field_type_id('text'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('register'), get_field_id('label_security_question_1'), 'Select security question 1', 'The label for the security question 1 when the anonymous registration is used');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_security_question_2', get_field_type_id('text'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('register'), get_field_id('label_security_question_2'), 'Select security question 2', 'The label for the security question 2 when the anonymous registration is used');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'anonymous_users_registration', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('register'), get_field_id('anonymous_users_registration'), 'Please describe the process to the user', 'The text is shown for the user when they register an anonymous user. Please use the field to describe the process to the user.');

-- add column security_questions to table users
CALL add_table_column('users', 'security_questions', "VARCHAR(1000)");