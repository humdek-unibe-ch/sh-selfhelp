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
