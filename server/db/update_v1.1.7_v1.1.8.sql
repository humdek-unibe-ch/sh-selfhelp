update pages
set url = '/admin/export/[user_input|user_activity|validation_codes|user_input_form:selector]/[all|used|open:option]?/[i:id]?'
where keyword = 'exportData';

update pages
set protocol = 'GET|POST'
where keyword = 'export';

drop view if exists view_fields;
create view view_fields
as
select cast(f.id as unsigned) as field_id, f.name as field_name, f.display, cast(ft.id as unsigned) as field_type_id, ft.name as field_type, ft.position
from fields f
left join fieldType ft on (f.id_type = ft.id);
drop view if exists view_styles;
create view view_styles
as
select cast(s.id as unsigned) as style_id, s.name as style_name, s.description as style_description,
cast(st.id as unsigned) as style_type_id, st.name as style_type, cast(sg.id as unsigned) as style_group_id,
sg.name as style_group, sg.description as style_group_description, sg.position as style_group_position
from styles s
left join styleType st on (s.id_type = st.id)
left join styleGroup sg on (s.id_group = sg.id);
drop view if exists view_style_fields;
create view view_style_fields
as
select s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.display, f.position, 
sf.default_value, sf.help
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles)
left join view_fields f on (f.field_id = sf.id_fields);
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

DELIMITER //
DROP FUNCTION IF EXISTS get_field_type_id //

CREATE FUNCTION get_field_type_id(field_type varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_type_id INT;    
	select id into field_type_id
	from fieldType
	where name = field_type;
    return field_type_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_field_id //

CREATE FUNCTION get_field_id(field varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_id INT;    
	select id into field_id
	from fields
	where name = field;
    return field_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_style_id //

CREATE FUNCTION get_style_id(style varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_id INT;    
	select id into style_id
	from styles
	where name = style;
    return style_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_style_group_id //

CREATE FUNCTION get_style_group_id(style_group varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_group_id INT;    
	select id into style_group_id
	from styleGroup
	where name = style_group;
    return style_group_id;
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data //

CREATE PROCEDURE get_form_data( form_id_param INT )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when field_name = "',
		  field_name,
		  '" then value end) as `',
		  replace(field_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_user_input
    where form_id = form_id_param;
	
    IF (@sql is null) THEN
		SELECT "";
    ELSE 
		begin
		SET @sql = CONCAT('select user_name, form_name, edit_time, ', @sql, ' from view_user_input
		where form_id = ', form_id_param,
		' group by form_name, user_name, edit_time');

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
