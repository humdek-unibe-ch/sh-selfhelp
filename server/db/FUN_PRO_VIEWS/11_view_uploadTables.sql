drop view if exists view_uploadTables;
create view view_uploadTables
as
select t.id as table_id, r.id as row_id, col.id as col_id, t.name as table_name, col.name as col_name, cell.value as value, r.timestamp
from uploadTables t
left join uploadRows r on (t.id = r.id_uploadTables)
left join uploadCells cell on (cell.id_uploadRows = r.id)
left join uploadCols col on (col.id = cell.id_uploadCols)
