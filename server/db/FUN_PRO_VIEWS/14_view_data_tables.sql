drop view if exists view_data_tables;
create view view_data_tables
as
select 'dynamic' as type, form_id as id, form_name as orig_name, concat(form_name, '_dynamic') as table_name
from view_form

union

select 'static' as type, id as id, name as orig_name, concat(name, '_static') as table_name
from uploadTables;
