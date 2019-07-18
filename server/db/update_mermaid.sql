INSERT INTO fieldType (name, position) VALUES ('code', 42);
INSERT INTO fields (name, id_type, display) VALUES ('code', (select id from `fieldType` where `name` = 'code' limit 1), 1);
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('mermaid', '1', (select id from styleGroup where `name` = 'Media' limit 1), 'Use <a href="https://mermaidjs.github.io/demos.html" target="_blank">mermaid markdown</a> syntax here.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaid' limit 1), (select id from `fields` where `name` = 'code' limit 1), 'Use <a href="https://mermaidjs.github.io/demos.html" target="_blank">mermaid markdown</a> syntax here.');

