#fieldType code
INSERT INTO fieldType (name, position) VALUES ('code', 42);
#field code
INSERT INTO fields (name, id_type, display) VALUES ('code', (select id from `fieldType` where `name` = 'code' limit 1), 1);
#mermaidForm style
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('mermaidForm', '2', (select id from styleGroup where `name` = 'Form' limit 1), 'Style to create diagrams using markdown syntax. Use <a href="https://mermaidjs.github.io/demos.html" target="_blank">mermaid markdown</a> syntax here.');
#mermaid styles fields
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'code' limit 1), 'Use <a href="https://mermaidjs.github.io/demos.html" target="_blank">mermaid markdown</a> syntax here.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'name' limit 1), 'Name of the form');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'label' limit 1), 'Label of the form');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'type' limit 1), 'Type of the form');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'children' limit 1), 'Add only styles from type `input` for the edditable nodes. If they have input they could be eddited by the subject when they are clicked.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'alert_success' limit 1), 'The alert message for the succes');

