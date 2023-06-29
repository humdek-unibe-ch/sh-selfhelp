-- set DB version
UPDATE version
SET version = 'v6.4.0';

-- add user types
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('userTypes', 'user', 'User', 'All default users');

SET @user_type_user_id = (SELECT id FROM lookups WHERE type_code = "userTypes" AND lookup_value = 'user');

SET @user_default_type_id = (SELECT CONCAT('INT(10) UNSIGNED ZEROFILL NOT NULL DEFAULT', ' ', @user_type_user_id));

CALL add_table_column('users', 'id_userTypes', @user_default_type_id);

DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT u.id, u.email, u.`name`, 
IFNULL(CONCAT(u.last_login, ' (', DATEDIFF(NOW(), u.last_login), ' days ago)'), 'never') AS last_login, 
us.`name` AS `status`,
us.description, u.blocked, 
CASE
	WHEN u.`name` = 'admin' THEN 'admin'
    WHEN u.`name` = 'tpf' THEN 'tpf'    
    ELSE IFNULL(vc.code, '-') 
END AS code,
GROUP_CONCAT(DISTINCT g.`name` SEPARATOR '; ') AS `groups`,
(SELECT COUNT(*) AS activity FROM user_activity WHERE user_activity.id_users = u.id) AS user_activity,
(SELECT COUNT(DISTINCT url) FROM user_activity WHERE user_activity.id_users = u.id AND id_type = 1) AS ac,
u.intern, u.id_userTypes, l_user_type.lookup_code AS user_type_code, l_user_type.lookup_value AS user_type
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN `groups` g ON g.id = ug.id_groups
LEFT JOIN validation_codes vc ON u.id = vc.id_users
INNER JOIN lookups l_user_type ON u.id_userTypes = l_user_type.id
WHERE u.intern <> 1 AND u.id_status > 0
GROUP BY u.id, u.email, u.`name`, u.last_login, us.`name`, us.description, u.blocked, vc.`code`, user_activity
ORDER BY u.email;

-- add field close_modal_at_end to style formUserInputLog
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('formUserInputLog'), get_field_id('close_modal_at_end'), 0, '`Only for mobile` - if selected the modal form will be closed once the survey is done');

-- add field close_modal_at_end to style formUserInputRecord
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('formUserInputRecord'), get_field_id('close_modal_at_end'), 0, '`Only for mobile` - if selected the modal form will be closed once the survey is done');

-- add column id_users in uploadRows
CALL add_table_column('uploadRows', 'id_users', 'INT(10) UNSIGNED ZEROFILL');
-- add foreign key for id_users in uploadRows
CALL add_foreign_key('uploadRows', 'uploadRows_fk_id_users', 'id_users', 'users (id)');
-- set the id_users if they are filled
UPDATE uploadRows AS r
INNER JOIN uploadCells AS cell ON cell.id_uploadRows = r.id
INNER JOIN uploadCols AS col ON cell.id_uploadCols = col.id
INNER JOIN uploadTables AS t ON col.id_uploadTables = t.id
SET r.id_users = cell.value
WHERE col.name LIKE "%id_users%";


DELIMITER //
DROP PROCEDURE IF EXISTS add_index //
CREATE PROCEDURE add_index(param_table VARCHAR(100), param_index_name VARCHAR(100), param_index_column VARCHAR(1000))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,
        "SELECT 'The index already exists in the table'",
        CONCAT('CREATE INDEX ', param_index_name, ' ON ', param_table, ' (', param_index_column, ');')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;

CALL add_index('uploadCells', 'idx_uploadCells_value', '`value`(255)');
CALL add_index('uploadRows', 'idx_uploadRows_timestamp', '`timestamp`');

DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter //

CREATE PROCEDURE get_uploadTable_with_filter( table_id_param INT, user_id_param INT, filter_param VARCHAR(1000))
-- if the filter_param contains any of these we additionaly filter: LAST_HOUR, LAST_DAY, LAST_WEEK, LAST_MONTH, LAST_YEAR
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
	INNER JOIN uploadCols col on (t.id = col.id_uploadTables)
    WHERE t.id = table_id_param AND col.`name` != 'id_users';

    IF (@sql is null) THEN
        SELECT table_name from view_uploadTables where 1=2;
    ELSE
        BEGIN
			SET @user_filter = '';
            IF user_id_param > 0 THEN
				SET @user_filter = CONCAT(' AND r.id_users = ', user_id_param);
            END IF;	
            
            SET @time_period_filter = '';
            CASE 
				WHEN filter_param LIKE '%LAST_HOUR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 HOUR';
				WHEN filter_param LIKE '%LAST_DAY%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 DAY';
				WHEN filter_param LIKE '%LAST_WEEK%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 WEEK';
				WHEN filter_param LIKE '%LAST_MONTH%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 MONTH';
				WHEN filter_param LIKE '%LAST_YEAR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 YEAR';
				ELSE
					SET @time_period_filter = '';					
			END CASE;
            
            SET @sql = CONCAT('select * from (select r.id as record_id, 
					r.timestamp as entry_date, r.id_users, u.name as user_name,', @sql, 
					' from uploadTables t
					inner join uploadRows r on (t.id = r.id_uploadTables)
					inner join uploadCells cell on (cell.id_uploadRows = r.id)
					inner join uploadCols col on (col.id = cell.id_uploadCols)
                    left join users u on (r.id_users = u.id)
					where t.id = ', table_id_param, @user_filter, @time_period_filter,
					' group by r.id ) as r where 1=1  ', filter_param);
            -- select @sql;
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END
//

DELIMITER ;



DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable //

CREATE PROCEDURE get_uploadTable( table_id_param INT )
BEGIN
    CALL get_uploadTable_with_filter(table_id_param, -1, '');
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
		SET @sql = CONCAT('select * from (select  record.id as record_id, max(edit_time) as edit_time, u.id as user_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)
		left join sections form  on (ui.id_section_form = form.id)
		left join user_input_record record  on (ui.id_user_input_record = record.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
		where form.id = ', form_id_param, ' and u.id = ', user_id_param,
		' group by u.id, u.name, record.id, vc.code, removed) as r where 1=1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

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

CALL add_unique_key('uploadTables','uploadTables_name','`name`');

