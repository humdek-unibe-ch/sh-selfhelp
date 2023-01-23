DROP VIEW IF EXISTS view_data_tables;
CREATE VIEW view_data_tables
AS
SELECT 'dynamic' AS `type`, form_id AS id, form_name AS orig_name, concat(form_name, '_dynamic') AS `table_name`, CONCAT(form_id,"-","dynamic") AS form_id_plus_type, internal
FROM view_form

UNION

SELECT 'static' AS `type`, id AS id, `name` AS orig_name, CONCAT(`name`, '_static') AS `table_name`, CONCAT(FLOOR(id),"-","static") AS form_id_plus_type, 0  AS internal
FROM uploadTables;
