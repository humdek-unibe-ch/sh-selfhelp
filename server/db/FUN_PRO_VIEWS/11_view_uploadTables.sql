drop view if exists view_uploadTables;
create view view_uploadTables
as
select t.id as table_id, r.id as row_id, r.timestamp as entry_date, col.id as col_id, t.name as table_name, col.name as col_name, cell.value as value, t.timestamp, r.id_users
from uploadTables t
inner join uploadRows r on (t.id = r.id_uploadTables)
inner join uploadCells cell on (cell.id_uploadRows = r.id)
inner join uploadCols col on (col.id = cell.id_uploadCols);
