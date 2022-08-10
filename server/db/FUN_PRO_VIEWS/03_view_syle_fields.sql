drop view if exists view_style_fields;
create view view_style_fields
as
select s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.display, f.position, 
sf.default_value, sf.help, sf.disabled, sf.hidden
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles)
left join view_fields f on (f.field_id = sf.id_fields);
