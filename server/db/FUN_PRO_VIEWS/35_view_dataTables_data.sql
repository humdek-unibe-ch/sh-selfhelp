DROP VIEW IF EXISTS view_dataTables_data;
CREATE VIEW view_dataTables_data
AS
SELECT t.id as table_id, r.id AS row_id, r.`timestamp` AS entry_date, col.id AS col_id, 
t.`name` AS `table_name`, col.`name` AS col_name, cell.`value` AS `value`, t.`timestamp`, r.id_users,
t.displayName AS displayName
FROM dataTables t
INNER JOIN dataRows r ON (t.id = r.id_dataTables)
INNER JOIN dataCells cell ON (cell.id_dataRows = r.id)
INNER JOIN dataCols col ON (col.id = cell.id_dataCols);
