-- set DB version
UPDATE version
SET version = 'v5.12.0';

UPDATE sections_fields_translation
SET content = REPLACE(content, '"type": "static"', '"type": "EXTERNAL"')
WHERE id_fields = get_field_id('data_config');

UPDATE sections_fields_translation
SET content = REPLACE(content, '"type": "dynamic"', '"type": "INTERNAL"')
WHERE id_fields = get_field_id('data_config');

UPDATE sections_fields_translation
SET content = REPLACE(content, '-static', '-EXTERNAL')
WHERE id_fields = get_field_id('formName');

UPDATE sections_fields_translation
SET content = REPLACE(content, '-dynamic', '-INTERNAL')
WHERE id_fields = get_field_id('formName');


DROP VIEW IF EXISTS view_data_tables;
CREATE VIEW view_data_tables
AS
SELECT 'INTERNAL' AS `type`, form_id AS id, form_name AS orig_name, concat(form_name, '_dynamic') AS `table_name`, CONCAT(form_id,"-","INTERNAL") AS form_id_plus_type, internal
FROM view_form

UNION

SELECT 'EXTERNAL' AS `type`, id AS id, `name` AS orig_name, CONCAT(`name`, '_static') AS `table_name`, CONCAT(FLOOR(id),"-","EXTERNAL") AS form_id_plus_type, 0  AS internal
FROM uploadTables;
