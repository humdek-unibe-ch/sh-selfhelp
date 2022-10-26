-- set DB version
UPDATE version
SET version = 'v5.6.0';

UPDATE `fields`
SET id_type = get_field_type_id('code')
WHERE `name` = 'filter';

DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_with_filter //

CREATE PROCEDURE get_form_data_with_filter( form_id_param INT, filter_param VARCHAR(1000) )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SELECT get_form_fields_helper(form_id_param) INTO @sql;	
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select * from (select u.id as user_id, sft_if.content as form_name, max(edit_time) as edit_time, record.id as record_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)
		left join sections form  on (ui.id_section_form = form.id)
		left join user_input_record record  on (ui.id_user_input_record = record.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
		LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
		where form.id = ', form_id_param, ' group by u.id, sft_if.content, u.name, record.id, vc.code, removed) as r where 1=1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_for_user_with_filter //

CREATE PROCEDURE get_form_data_for_user_with_filter( form_id_param INT, user_id_param INT, filter_param VARCHAR(1000) )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT get_form_fields_helper(form_id_param) INTO @sql;	
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select * from (select u.id as user_id, sft_if.content as form_name, max(edit_time) as edit_time, record.id as record_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)
		left join sections form  on (ui.id_section_form = form.id)
		left join user_input_record record  on (ui.id_user_input_record = record.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
		LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
		where form.id = ', form_id_param, ' and u.id = ', user_id_param,
		' group by u.id, sft_if.content, u.name, record.id, vc.code, removed) as r where 1=1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter //

CREATE PROCEDURE get_uploadTable_with_filter( table_id_param INT, filter_param VARCHAR(1000) )
BEGIN
    SET @@group_concat_max_len = 32000;
    SET @sql = NULL;
    SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'max(case when col.name = "',
                col.name,
                '" then value end) as `',
            replace(col.name, ' ', ''), '`'
        )
    ) INTO @sql
    FROM  uploadTables t
	INNER JOIN uploadRows r on (t.id = r.id_uploadTables)
	INNER JOIN uploadCells cell on (cell.id_uploadRows = r.id)
	INNER JOIN uploadCols col on (col.id = cell.id_uploadCols)
    WHERE t.id = table_id_param;

    IF (@sql is null) THEN
        SELECT table_name from view_uploadTables where 1=2;
    ELSE
        BEGIN
            SET @sql = CONCAT('select * from (select t.name as table_name, t.timestamp as timestamp, r.id as record_id, r.timestamp as entry_date, ', IF(@sql LIKE '%id_users%', @sql, CONCAT(@sql,', -1 AS id_users')), 
                ' from uploadTables t
					inner join uploadRows r on (t.id = r.id_uploadTables)
					inner join uploadCells cell on (cell.id_uploadRows = r.id)
					inner join uploadCols col on (col.id = cell.id_uploadCols)
					where t.id = ', table_id_param,
					' group by t.name, t.timestamp, r.id ) as r where 1=1  ', filter_param);
			IF LOCATE('id_users', @sql) THEN
				-- get user_name if there is id_users column
				SET @sql = CONCAT('select v.*, u.name as user_name from (', @sql, ')  as v left join users u on (v.id_users = u.id)');
			END IF;

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END
//

DELIMITER ;
