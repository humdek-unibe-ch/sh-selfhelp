DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_with_filter //

CREATE PROCEDURE get_form_data_with_filter( form_id_param INT, filter_param VARCHAR(1000) )
BEGIN  
    SET @@group_concat_max_len = 32000000;
	SELECT get_form_fields_helper(form_id_param) INTO @sql;	
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select * from (select record.id as record_id, max(edit_time) as edit_time, u.id as user_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)
		left join sections form  on (ui.id_section_form = form.id)
		left join user_input_record record  on (ui.id_user_input_record = record.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57		
		where form.id = ', form_id_param, ' group by u.id, u.name, record.id, vc.code, removed) as r where 1=1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
