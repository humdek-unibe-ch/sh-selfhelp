drop view if exists view_styles;
create view view_styles
as
select cast(s.id as int) as style_id, s.name as style_name, s.description as style_description,
cast(st.id as int) as style_type_id, st.name as style_type, cast(sg.id as int) as style_group_id,
sg.name as style_group, sg.description as style_group_description, sg.position as style_group_position
from styles s
left join "styleType" st on (s.id_type = st.id)
left join "styleGroup" sg on (s.id_group = sg.id);
