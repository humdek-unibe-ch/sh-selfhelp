DROP FUNCTION IF EXISTS get_form_data_for_user_with_filter;
CREATE OR REPLACE FUNCTION get_form_data_for_user_with_filter( form_id_param INT, user_id_param INT, filter_param VARCHAR(1000) )
RETURNS TEXT 
AS $func$
DECLARE
   sql_str TEXT; 
BEGIN  
	SELECT get_form_fields_helper(form_id_param) INTO sql_str;	
	
    IF (sql_str is null) THEN
		sql_str := 'SELECT user_id, name FROM users where 1=2;';
		RETURN sql_str;
    ELSE 
		BEGIN
		sql_str := CONCAT('select u.id as user_id, sft_if.content as form_name, max(edit_time) as edit_time, record.id as record_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)
		left join sections form  on (ui.id_section_form = form.id)
		left join user_input_record record  on (ui.id_user_input_record = record.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
		LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
		where form.id = ', form_id_param, ' and u.id = ', user_id_param,
		' group by u.id, sft_if.content, u.name, record.id, vc.code, removed HAVING 1 ', filter_param);

		
		RETURN sql_str;
        END;
    END IF;
END 
$func$ LANGUAGE plpgsql;
