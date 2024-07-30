-- set DB version
UPDATE version
SET version = 'v6.12.0';

DELIMITER //
DROP FUNCTION IF EXISTS get_form_fields_helper //

CREATE FUNCTION get_form_fields_helper(form_id_param INT) RETURNS TEXT
READS SQL DATA
DETERMINISTIC
BEGIN 
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when sft_in.content = "',
		  sft_in.content,
		  '" then value end) as `',
		  replace(sft_in.content, ' ', ''), '`'
		)
	  ) INTO @sql
	from user_input ui
	left join users u on (ui.id_users = u.id)
	left join validation_codes vc on (ui.id_users = vc.id_users)
	left join sections field on (ui.id_sections = field.id)
	left join sections form  on (ui.id_section_form = form.id)
	left join user_input_record record  on (ui.id_user_input_record = record.id)
	LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
	LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
    WHERE form.id = form_id_param;
	
    RETURN @sql;
END
//

DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS get_page_fields_helper //

CREATE FUNCTION get_page_fields_helper(page_id INT, language_id INT, default_language_id INT) RETURNS TEXT
-- page_id -1 returns all pages
READS SQL DATA
DETERMINISTIC
BEGIN 
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'MAX(CASE WHEN f.`name` = "',
		  f.`name`,
		  '" THEN IF(IFNULL((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' LIMIT 0,1), "") = "", (SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = (CASE WHEN f.display = 0 THEN 1 ELSE ',default_language_id,' END) LIMIT 0,1),(SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' LIMIT 0,1))  end) as `',
		  replace(f.`name`, ' ', ''), '`'
		)
	  ) INTO @sql
	FROM  pages AS p
	LEFT JOIN pageType_fields AS ptf ON ptf.id_pageType = p.id_type 
	LEFT JOIN fields AS f ON f.id = ptf.id_fields
    WHERE p.id = page_id OR page_id = -1;
	
    RETURN @sql;
END
//

DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS get_sections_fields_helper //

CREATE FUNCTION get_sections_fields_helper(section_id INT, language_id INT, gender_id INT) RETURNS TEXT
-- section_id -1 returns all sections
READS SQL DATA
DETERMINISTIC
BEGIN 
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when f.`name` = "',
		  f.`name`,
		  '" then sft.content end) as `',
		  replace(f.`name`, ' ', ''), '`'
		)
	  ) INTO @sql
	from  sections AS s
	LEFT JOIN sections_fields_translation AS sft ON sft.id_sections = s.id AND (language_id = sft.id_languages OR sft.id_languages = 1) AND (sft.id_genders = gender_id)
	LEFT JOIN fields AS f ON f.id = sft.id_fields
    WHERE s.id = section_id OR section_id = -1;
	
    RETURN @sql;
END
//

DELIMITER ;

DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter //

CREATE PROCEDURE get_uploadTable_with_filter( table_id_param INT, user_id_param INT, filter_param VARCHAR(1000))
-- if the filter_param contains any of these we additionaly filter: LAST_HOUR, LAST_DAY, LAST_WEEK, LAST_MONTH, LAST_YEAR
READS SQL DATA
DETERMINISTIC
BEGIN
    SET @@group_concat_max_len = 32000000;
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

DROP PROCEDURE IF EXISTS get_form_data_for_user_with_filter //

CREATE PROCEDURE get_form_data_for_user_with_filter( form_id_param INT, user_id_param INT, filter_param VARCHAR(1000) )
READS SQL DATA
DETERMINISTIC
BEGIN  
    SET @@group_concat_max_len = 32000000;
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
READS SQL DATA
DETERMINISTIC
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

DELIMITER //

DROP PROCEDURE IF EXISTS get_page_fields //

CREATE PROCEDURE get_page_fields( page_id INT, language_id INT, default_language_id INT, filter_param VARCHAR(1000), order_param VARCHAR(1000))
READS SQL DATA
DETERMINISTIC
BEGIN  
	-- page_id -1 returns all pages
    SET @@group_concat_max_len = 32000000;
	SELECT get_page_fields_helper(page_id, language_id, default_language_id) INTO @sql;	
	
    IF (@sql is null) THEN	
        SELECT * FROM pages WHERE 1=2;
    ELSE 
		BEGIN
		SET @sql = CONCAT(
			'select p.id, p.keyword, p.url, p.protocol, p.id_actions, "select" AS access_level, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p.id_pageAccessTypes, a.name AS `action`, ', 
			@sql, 
			'FROM pages p
            LEFT JOIN actions AS a ON a.id = p.id_actions
			LEFT JOIN pageType_fields AS ptf ON ptf.id_pageType = p.id_type 
			LEFT JOIN fields AS f ON f.id = ptf.id_fields
			WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ')
            GROUP BY p.id, p.keyword, p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p.id_pageAccessTypes, a.name HAVING 1 ', filter_param
        );
        
        IF (order_param <> '') THEN	        
			SET @sql = concat(
				'SELECT * FROM (',
				@sql,
				') AS t ', order_param
			);
		END IF;

		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

DELIMITER //

DROP PROCEDURE IF EXISTS get_sections_fields //

CREATE PROCEDURE get_sections_fields( section_id INT, language_id INT, gender_id INT, filter_param VARCHAR(1000), order_param VARCHAR(1000))
READS SQL DATA
DETERMINISTIC
BEGIN  
	-- section_id -1 returns all sections
    SET @@group_concat_max_len = 32000000;
	SELECT get_sections_fields_helper(section_id, language_id, gender_id) INTO @sql;	
	
    IF (@sql is null) THEN	
        SELECT * FROM sections WHERE 1=2;
    ELSE 
		BEGIN
		SET @sql = CONCAT(
			'SELECT s.id AS section_id, s.name AS section_name, st.id AS style_id, st.name AS style_name, ', 
			@sql, 
			'FROM sections s
            INNER JOIN styles st ON (s.id_styles = st.id)
			LEFT JOIN sections_fields_translation AS sft ON sft.id_sections = s.id   
			LEFT JOIN fields AS f ON sft.id_fields = f.id
			WHERE (s.id = ', section_id, ' OR -1 = ', section_id, ') AND ( IFNULL(id_languages, 1) = 1 OR id_languages=', language_id, ') 
            GROUP BY s.id, s.name, st.id, st.name HAVING 1 ', filter_param
        );
        
        IF (order_param <> '') THEN	        
			SET @sql = concat(
				'SELECT * FROM (',
				@sql,
				') AS t ', order_param
			);
		END IF;

		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

