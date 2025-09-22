DROP VIEW IF EXISTS view_style_fields;
CREATE VIEW view_style_fields 
AS
SELECT s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.config, f.display, f.position, 
sf.default_value, sf.help, sf.disabled, sf.hidden
FROM view_styles s
LEFT JOIN styles_fields sf ON (s.style_id = sf.id_styles)
LEFT JOIN view_fields f ON (f.field_id = sf.id_fields);
