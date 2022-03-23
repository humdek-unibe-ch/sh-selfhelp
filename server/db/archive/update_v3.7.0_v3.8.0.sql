-- set DB version
UPDATE version
SET version = 'v3.8.0';

drop view if exists view_style_fields;
create view view_style_fields
as
select s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.display, f.position, 
sf.default_value, sf.help
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles)
left join view_fields f on (f.field_id = sf.id_fields);

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'css');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'login';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'container';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'jumbotron';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'heading';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'markdown';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'markdownInline';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'button';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'alert';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'card';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'figure';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'form';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'image';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'input';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'link';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'progressBar';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'quiz';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'rawText';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'select';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'slider';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'tab';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'tabs';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'textarea';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'video';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'accordeonList';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'navigationContainer';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'nestedList';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'sortableList';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'formUserInput';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'radio';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'showUserInput';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'div';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'register';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'conditionalContainer';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'audio';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'carousel';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'userProgress';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'mermaidForm';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'emailForm';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'autocomplete';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'graph';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'graphSankey';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'filterToggle';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'filterToggleGroup';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'graphPie';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'graphBar';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'graphLegend';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'qualtricsSurvey';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'search';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT `id`, @id_field, "Allows to assign CSS classes to the root item of the style." FROM `styles` WHERE `name` = 'version';
