drop view if exists view_user_input;
create view view_user_input
as
select cast(ui.id as unsigned) as id, cast(u.id as unsigned) as user_id, u.name as user_name, cast(form.id as unsigned) form_id, sft_if.content as form_name, cast(field.id as unsigned) as field_id, 
sft_in.content as field_name, ui.value, ui.edit_time, ui.removed
from user_input ui
left join users u on (ui.id_users = u.id)
left join sections field on (ui.id_sections = field.id)
left join sections form  on (ui.id_section_form = form.id)
LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57;

