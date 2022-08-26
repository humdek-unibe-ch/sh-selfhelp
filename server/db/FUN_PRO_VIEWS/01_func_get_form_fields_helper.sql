DROP FUNCTION IF EXISTS get_form_fields_helper;

CREATE OR REPLACE FUNCTION get_form_fields_helper(form_id_param INT) RETURNS TEXT
AS $$
DECLARE
   sql_str TEXT; 
BEGIN 	
	sql_str := NULL;
	SELECT
	  STRING_AGG(DISTINCT
		CONCAT(
		  'MAX(CASE WHEN sft_in.content = ''',
		  sft_in.content,
		  ''' THEN value END) AS "',
		  replace(sft_in.content, ' ', ''), '"'
		),', '
	  ) INTO sql_str
	from user_input ui
	left join users u on (ui.id_users = u.id)
	left join validation_codes vc on (ui.id_users = vc.id_users)
	left join sections field on (ui.id_sections = field.id)
	left join sections form  on (ui.id_section_form = form.id)
	left join user_input_record record  on (ui.id_user_input_record = record.id)
	LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
	LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
    WHERE form.id = form_id_param;
	
    RETURN sql_str;
END
$$ LANGUAGE plpgsql;
