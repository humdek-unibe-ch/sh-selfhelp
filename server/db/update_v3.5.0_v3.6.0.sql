-- set DB version
UPDATE version
SET version = 'v3.6.0';

drop view if exists view_style_fields;
create view view_style_fields
as
select s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.display, f.position, 
sf.default_value, sf.help
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles or sf.id_fields = (SELECT id FROM fields WHERE name = 'css')) -- hardcoded for css field
left join view_fields f on (f.field_id = sf.id_fields);
