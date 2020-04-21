drop view if exists view_form;
create view view_form
as
select distinct cast(form.id as unsigned) form_id, sft_if.content as form_name
from user_input ui
left join sections form  on (ui.id_section_form = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57;
