DROP VIEW IF EXISTS view_fields;
CREATE VIEW view_fields
AS
SELECT f.id AS field_id, f.`name` AS field_name, f.display, ft.id AS field_type_id, ft.`name` AS field_type, ft.position, f.config
FROM `fields` f
LEFT JOIN fieldType ft ON (f.id_type = ft.id);
