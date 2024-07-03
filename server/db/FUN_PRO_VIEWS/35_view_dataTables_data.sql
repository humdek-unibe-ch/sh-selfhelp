DROP VIEW IF EXISTS view_dataTables_data;
CREATE VIEW view_dataTables_data
AS
SELECT t.id as table_id, r.id AS row_id, r.`timestamp` AS entry_date, col.id AS col_id, 
t.`name` AS `table_name`, col.`name` AS col_name, cell.`value` AS `value`, t.`timestamp`, r.id_users,
t.displayName AS displayName
FROM dataTables t
LEFT JOIN dataRows r ON (t.id = r.id_dataTables)
LEFT JOIN dataCells cell ON (cell.id_dataRows = r.id)
LEFT JOIN dataCols col ON (col.id = cell.id_dataCols);
