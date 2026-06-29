DELIMITER //
DROP PROCEDURE IF EXISTS add_foreign_key //
CREATE PROCEDURE add_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100), fk_column VARCHAR(100), fk_references VARCHAR(200))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) > 0,
        "SELECT 'The foreign key already exists in the table'",
        CONCAT('ALTER TABLE ', param_table, ' ADD CONSTRAINT ', fk_name, ' FOREIGN KEY (', fk_column, ') REFERENCES ', fk_references, ' ON DELETE CASCADE ON UPDATE CASCADE;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
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
DELIMITER //

DROP PROCEDURE IF EXISTS add_table_column //
CREATE PROCEDURE add_table_column(
    IN param_table VARCHAR(100), 
    IN param_column VARCHAR(100), 
    IN param_column_type VARCHAR(500)
)
BEGIN
    SET @sqlstmt = (
        SELECT IF(
            (
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS
                WHERE `table_schema` = DATABASE()
                AND `table_name` = param_table
                AND `COLUMN_NAME` = param_column 
            ) > 0,
            "SELECT 'Column already exists in the table'",
            CONCAT('ALTER TABLE `', param_table, '` ADD COLUMN `', param_column, '` ', param_column_type, ';')
        )
    );

    PREPARE st FROM @sqlstmt;
    EXECUTE st;
    DEALLOCATE PREPARE st;
END
//

DELIMITER ;
DELIMITER //
DROP PROCEDURE IF EXISTS add_unique_key //
CREATE PROCEDURE add_unique_key(param_table VARCHAR(100), param_index VARCHAR(100), param_column VARCHAR(100))
BEGIN
    IF NOT EXISTS 
	(
		SELECT NULL 
		FROM information_schema.STATISTICS
		WHERE `table_schema` = DATABASE()
		AND `table_name` = param_table
		AND `index_name` = param_index 
	) THEN    
		SET @sqlstmt = CONCAT('ALTER TABLE ', param_table, ' ADD UNIQUE KEY ', param_index, ' (', param_column, ');');
		PREPARE st FROM @sqlstmt;
        EXECUTE st;
        DEALLOCATE PREPARE st;	
    END IF;
END

//

DELIMITER ;
DELIMITER //
DROP PROCEDURE IF EXISTS drop_foreign_key //
CREATE PROCEDURE drop_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) = 0,
        "SELECT 'Foreign key does not exist'",
        CONCAT('ALTER TABLE ', param_table, ' DROP FOREIGN KEY ', fk_name, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
DELIMITER //
DROP PROCEDURE IF EXISTS drop_index //
CREATE PROCEDURE drop_index(param_table VARCHAR(100), param_index_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,        
        CONCAT('ALTER TABLE ', param_table, ' DROP INDEX ', param_index_name),
        "SELECT 'The index does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
DELIMITER //
DROP PROCEDURE IF EXISTS drop_table_column //
CREATE PROCEDURE drop_table_column(param_table VARCHAR(100), param_column VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*) 
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_column 
		) = 0,
        "SELECT 'Column does not exist'",
        CONCAT('ALTER TABLE `', param_table, '` DROP COLUMN `', param_column, '` ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
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
	left join user_input_record record  on (ui.id_user_input_record = record.id)
    LEFT JOIN sections form ON (record.id_sections = form.id)
	LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
	LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
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
          '" THEN COALESCE((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' AND content <> "" LIMIT 1), COALESCE((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = (CASE WHEN f.display = 0 THEN 1 ELSE ',default_language_id,' END) LIMIT 1), "")) END) AS `',
          REPLACE(f.`name`, ' ', ''), '`'
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
DROP PROCEDURE IF EXISTS rename_table //
CREATE PROCEDURE rename_table(param_old_table_name VARCHAR(100), param_new_table_name VARCHAR(100))
BEGIN	
	DECLARE tableExists INT;
    SELECT COUNT(*) 
            INTO tableExists
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_old_table_name; 
    SET @sqlstmt = (SELECT IF(
		tableExists > 0,        
        CONCAT('RENAME TABLE ', param_old_table_name, ' TO ', param_new_table_name),
        "SELECT 'Table does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
DELIMITER //
DROP PROCEDURE IF EXISTS rename_table_column //
CREATE PROCEDURE rename_table_column(param_table VARCHAR(100), param_old_column_name VARCHAR(100), param_new_column_name VARCHAR(100))
BEGIN	
	DECLARE columnExists INT;
    DECLARE columnType VARCHAR(255);
    SELECT COUNT(*), COLUMN_TYPE 
            INTO columnExists, columnType
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_old_column_name; 
    SET @sqlstmt = (SELECT IF(
		columnExists > 0,        
        CONCAT('ALTER TABLE ', param_table, ' CHANGE COLUMN ', param_old_column_name, ' ', param_new_column_name, ' ', columnType, ';'),
        "SELECT 'Column does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
DROP VIEW IF EXISTS view_cmsPreferences;
CREATE VIEW view_cmsPreferences
AS
SELECT p.callback_api_key, p.default_language_id, l.`language` AS default_language, l.locale, p.firebase_config, p.anonymous_users
FROM cmsPreferences p
LEFT JOIN languages l ON (l.id = p.default_language_id)
WHERE p.id = 1;
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
sf.default_value, sf.help, sf.disabled, sf.hidden
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles)
left join view_fields f on (f.field_id = sf.id_fields);
DELIMITER //
DROP FUNCTION IF EXISTS get_field_type_id //

CREATE FUNCTION get_field_type_id(field_type varchar(100)) RETURNS INT
READS SQL DATA
BEGIN 
	DECLARE field_type_id INT;    
	SELECT id INTO field_type_id
	FROM fieldType
	WHERE name = field_type;
    RETURN field_type_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_field_id //

CREATE FUNCTION get_field_id(field varchar(100)) RETURNS INT
READS SQL DATA
BEGIN 
	DECLARE field_id INT;    
	SELECT id INTO field_id
	FROM fields
	WHERE name = field;
    RETURN field_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_style_id //

CREATE FUNCTION get_style_id(style varchar(100)) RETURNS INT
READS SQL DATA
BEGIN 
	DECLARE style_id INT;    
	SELECT id INTO style_id
	FROM styles
	WHERE name = style;
    RETURN style_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_style_group_id //

CREATE FUNCTION get_style_group_id(style_group varchar(100)) RETURNS INT
READS SQL DATA
BEGIN 
	DECLARE style_group_id INT;    
	SELECT id INTO style_group_id
	FROM styleGroup
	WHERE name = style_group;
    RETURN style_group_id;
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_dataTable_with_filter //

CREATE PROCEDURE get_dataTable_with_filter( 
    IN table_id_param INT, 
    IN user_id_param INT, 
    IN filter_param VARCHAR(1000),
    IN exclude_deleted_param BOOLEAN, -- If true it will exclude the deleted records and it will not return them
    IN selected_columns_param VARCHAR(4000) -- Comma separated list of data column names to be loaded
)
-- if the filter_param contains any of these we additionaly filter: LAST_HOUR, LAST_DAY, LAST_WEEK, LAST_MONTH, LAST_YEAR
READS SQL DATA
DETERMINISTIC
BEGIN
    SET @@group_concat_max_len = 32000000;
    SET @sql = NULL;
    SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'MAX(CASE WHEN col.`name` = "',
                col.name,
                '" THEN `value` END) AS `',
            replace(col.name, ' ', ''), '`'
        )
    ) INTO @sql
    FROM  dataTables t
    INNER JOIN dataCols col on (t.id = col.id_dataTables)
    WHERE t.id = table_id_param
        AND col.`name` NOT IN ('id_users','record_id','user_name','id_actionTriggerTypes','triggerType', 'entry_date', 'user_code')
        AND (
            IFNULL(TRIM(selected_columns_param), '') = ''
            OR FIND_IN_SET(col.`name`, selected_columns_param) > 0
        );

    IF (@sql is null) THEN
        SELECT `name` from view_dataTables where 1=2;
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
            
            SET @exclude_deleted_filter = '';
            CASE 
                WHEN exclude_deleted_param = TRUE THEN
                    SET @exclude_deleted_filter = CONCAT(' AND IFNULL(r.id_actionTriggerTypes, 0) <> ', (SELECT id FROM lookups WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'deleted' LIMIT 0,1));				
                ELSE
                    SET @exclude_deleted_filter = '';					
            END CASE;
            
            SET @sql = CONCAT('SELECT * FROM (SELECT r.id AS record_id, 
                    r.`timestamp` AS entry_date, r.id_users, u.`name` AS user_name, MAX(vc.code) AS user_code, r.id_actionTriggerTypes, l.lookup_code AS triggerType,', @sql, 
                    ' FROM dataTables t
                    INNER JOIN dataRows r ON (t.id = r.id_dataTables)
                    INNER JOIN dataCells cell ON (cell.id_dataRows = r.id)
                    INNER JOIN dataCols col ON (col.id = cell.id_dataCols)
                    LEFT JOIN users u ON (r.id_users = u.id)
                    LEFT JOIN validation_codes vc ON (u.id = vc.id_users)
                    LEFT JOIN lookups l ON (l.id = r.id_actionTriggerTypes)
                    WHERE t.id = ', table_id_param, @user_filter, @time_period_filter, @exclude_deleted_filter, 
                    ' GROUP BY r.id ) AS r WHERE 1=1  ', filter_param);
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END
//

DELIMITER ;
DROP VIEW IF EXISTS view_acl_groups_pages;
CREATE VIEW view_acl_groups_pages
AS
SELECT acl.id_groups, acl.id_pages, 
CASE
	WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
	ELSE acl.acl_select
END AS acl_select, 
acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type
FROM acl_groups acl
INNER JOIN pages p ON (acl.id_pages = p.id or (p.id_type = 4 and acl.id_pages = null)) -- add all open pages although that there is no specific ACL
GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
DROP VIEW IF EXISTS view_acl_users_pages;
CREATE VIEW view_acl_users_pages
AS
SELECT acl.id_users, acl.id_pages, 
CASE
	WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
	ELSE acl.acl_select
END AS acl_select, 
acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type
FROM acl_users acl
INNER JOIN pages p ON (acl.id_pages = p.id)
GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
DROP VIEW IF EXISTS view_transactions;
CREATE VIEW view_transactions
AS
SELECT t.id, t.transaction_time, t.id_transactionTypes, tran_type.lookup_value AS transaction_type,
id_transactionBy, tran_by.lookup_value AS transaction_by, id_users, u.name AS user_name,
table_name, id_table_name, REPLACE(JSON_EXTRACT(transaction_log, '$.verbal_log'), '"', '') AS transaction_verbal_log
FROM transactions t
INNER JOIN lookups tran_type ON (tran_type.id = t.id_transactionTypes)
INNER JOIN lookups tran_by ON (tran_by.id = t.id_transactionBy)
LEFT JOIN users u ON (u.id = t.id_users);
DROP VIEW IF EXISTS view_acl_users_in_groups_pages;
CREATE VIEW view_acl_users_in_groups_pages
AS
SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl.acl_select, 0)) as acl_select, MAX(IFNULL(acl.acl_insert, 0)) as acl_insert,
MAX(IFNULL(acl.acl_update, 0)) as acl_update, MAX(IFNULL(acl.acl_delete, 0)) as acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type
FROM users u
INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
INNER JOIN pages p ON (acl.id_pages = p.id)
GROUP BY ug.id_users, acl.id_pages, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
DROP VIEW IF EXISTS view_acl_users_union;
CREATE VIEW view_acl_users_union
AS
SELECT *
FROM view_acl_users_in_groups_pages

UNION 

SELECT *
FROM view_acl_users_pages;
DELIMITER //

DROP PROCEDURE IF EXISTS get_group_acl //

CREATE PROCEDURE get_group_acl( param_group_id INT, param_page_id INT ) # when page_id is -1 then all pages
BEGIN

    SELECT acl.id_groups, acl.id_pages, 
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type
	FROM acl_groups acl
	INNER JOIN pages p ON (acl.id_pages = p.id or (p.id_type = 4 and acl.id_pages = null)) -- add all open pages although that there is no specific ACL
    WHERE acl.id_groups = param_group_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
    
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_navigation //

CREATE PROCEDURE get_navigation( param_locale VARCHAR(10) ) # when page_id is -1 then all pages
BEGIN

    SELECT Json_arrayagg(Json_object(keyword, (SELECT 
						 Json_object('id_navigation_section' 
						 , 
						 p.id_navigation_section, 'title', 
						 pft.content, 'children', (SELECT 
						 Json_arrayagg( 
						 Json_object(keyword, (SELECT 
												 Json_object('id_navigation_section' 
												 , 
												 p2.id_navigation_section, 'title', 
												 pft2.content, 'children', NULL)))) 
						 AS items 
												 FROM   pages AS p2 
												 LEFT JOIN pages_fields_translation 
														   AS pft2 
												 ON pft2.id_pages = p2.id 
												 LEFT JOIN languages AS l2 
												 ON l2.id = pft2.id_languages 
												 LEFT JOIN fields AS f2 
												 ON f2.id = pft2.id_fields 
												 WHERE  p2.parent = p.id 
												 AND ( l.locale = param_locale 
												 OR l.locale = 'all' ) 
												 AND f2.NAME = 'label' 
												 AND p2.nav_position IS NOT NULL 
												 ORDER  BY p2.nav_position ASC))))) AS 
		   pages 
	FROM   pages AS p 
		   LEFT JOIN pages_fields_translation AS pft 
				  ON pft.id_pages = p.id 
		   LEFT JOIN languages AS l 
				  ON l.id = pft.id_languages 
		   LEFT JOIN fields AS f 
				  ON f.id = pft.id_fields 
	WHERE  p.nav_position IS NOT NULL 
		   AND ( l.locale = param_locale 
				  OR l.locale = 'all' ) 
		   AND f.NAME = 'label' 
		   AND p.parent IS NULL 
ORDER  BY p.nav_position DESC;
    
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_user_acl //

CREATE PROCEDURE get_user_acl( param_user_id INT, param_page_id INT ) # when page_id is -1 then all pages
BEGIN

    SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl.acl_select, 0)) as acl_select, MAX(IFNULL(acl.acl_insert, 0)) as acl_insert,
	MAX(IFNULL(acl.acl_update, 0)) as acl_update, MAX(IFNULL(acl.acl_delete, 0)) as acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type
	FROM users u
	INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
	INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
	INNER JOIN pages p ON (acl.id_pages = p.id)
	WHERE ug.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY ug.id_users, acl.id_pages, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    
    UNION 
    
    SELECT acl.id_users, acl.id_pages, 
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type
	FROM acl_users acl
	INNER JOIN pages p ON (acl.id_pages = p.id)
    WHERE acl.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
    
END
//

DELIMITER ;
DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT 
    u.id, 
    u.email, 
    u.`name`, 
    IFNULL(CONCAT(u.last_login, ' (', DATEDIFF(NOW(), u.last_login), ' days ago)'), 'never') AS last_login, 
    us.`name` AS `status`,
    us.description, 
    u.blocked, 
    CASE
        WHEN u.`email` = 'admin' THEN 'admin'
        WHEN u.`email` = 'tpf' THEN 'tpf'    
        ELSE IFNULL(vc.code, '-') 
    END AS code,
    GROUP_CONCAT(DISTINCT g.`name` SEPARATOR '; ') AS `groups`,
    user_activity.activity_count AS user_activity,
    user_activity.distinct_url_count AS ac,
    u.intern, 
    u.id_userTypes, 
    l_user_type.lookup_code AS user_type_code, 
    l_user_type.lookup_value AS user_type
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN `groups` g ON g.id = ug.id_groups
LEFT JOIN validation_codes vc ON u.id = vc.id_users
INNER JOIN lookups l_user_type ON u.id_userTypes = l_user_type.id
LEFT JOIN (
    SELECT 
        id_users, 
        COUNT(*) AS activity_count,
        COUNT(DISTINCT CASE WHEN id_type = 1 THEN url ELSE NULL END) AS distinct_url_count
    FROM user_activity
    GROUP BY id_users
) AS user_activity ON u.id = user_activity.id_users
WHERE u.intern <> 1 
AND u.id_status > 0
GROUP BY 
    u.id, 
    u.email, 
    u.`name`, 
    u.last_login, 
    us.`name`, 
    us.description, 
    u.blocked, 
    vc.`code`, 
    user_activity.activity_count, 
    user_activity.distinct_url_count,
    u.intern, 
    u.id_userTypes, 
    l_user_type.lookup_code, 
    l_user_type.lookup_value
ORDER BY u.email;

DROP VIEW IF EXISTS view_sections_fields;
CREATE VIEW view_sections_fields
AS
SELECT
   s.id AS id_sections,
   s.name AS section_name,
   IFNULL(sft.content, '') AS content,
   IFNULL(sft.meta, '') AS meta,
   s.id_styles,
   fields.style_name,
   field_id AS id_fields,
   field_name,
   IFNULL(l.locale, '') AS locale,
   IFNULL(g.name, '') AS gender 
FROM sections s 
LEFT JOIN view_style_fields fields ON (fields.style_id = s.id_styles) 
LEFT JOIN sections_fields_translation sft ON (sft.id_sections = s.id AND sft.id_fields = fields.field_id) 
LEFT JOIN languages l ON (sft.id_languages = l.id) 
LEFT JOIN genders g ON (sft.id_genders = g.id);
DROP VIEW IF EXISTS view_scheduledJobs;
CREATE VIEW view_scheduledJobs
AS
SELECT sj.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS `status`, 
l_types.lookup_code AS type_code, l_types.lookup_value AS `type`, sj.config,
sj.date_create, date_to_be_executed, date_executed, `description`, 
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.recipient_emails    
    WHEN l_types.lookup_code = 'notification' THEN ''
    WHEN l_types.lookup_code = 'task' THEN ''
    ELSE ""
END AS recipient,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.`subject`
    WHEN l_types.lookup_code = 'notification' THEN n.`subject`
    ELSE ""
END AS title,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.body
    WHEN l_types.lookup_code = 'notification' THEN n.body
    ELSE ""
END AS message,
sj_mq.id_mailQueue, id_jobTypes, id_jobStatus, a.id_formActions,
a.id_dataRows, dt.`name` AS dataTables_name
FROM scheduledJobs sj
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
LEFT JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_scheduledJobs = sj.id)
LEFT JOIN mailQueue mq ON (mq.id = sj_mq.id_mailQueue)
LEFT JOIN scheduledJobs_notifications sj_n ON (sj_n.id_scheduledJobs = sj.id)
LEFT JOIN notifications n ON (n.id = sj_n.id_notifications)
LEFT JOIN scheduledJobs_formActions a ON (a.id_scheduledJobs = sj.id)
LEFT JOIN dataRows r ON (r.id = a.id_dataRows)
LEFT JOIN view_dataTables dt ON (r.id_dataTables = dt.id);
DROP VIEW IF EXISTS view_scheduledJobs_transactions;
CREATE VIEW view_scheduledJobs_transactions
AS
SELECT sj.id, sj.date_create, date_to_be_executed, date_executed, t.id AS transaction_id, transaction_time, 
transaction_type, transaction_by, user_name, transaction_verbal_log
FROM scheduledJobs sj
INNER JOIN view_transactions t ON (t.table_name = 'scheduledJobs' AND t.id_table_name = sj.id)
ORDER BY sj.id ASC, t.id ASC;
DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT sj.id AS id, from_email, from_name,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
reply_to, recipient_emails, cc_emails, bcc_emails, `subject`, body, is_html, mq.id AS id_mailQueue, id_jobTypes,
id_jobStatus, sj.config, id_dataRows, dataTables_name
FROM mailQueue mq
INNER JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_mailQueue = mq.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_mq.id_scheduledJobs);
DROP VIEW IF EXISTS view_notifications;
CREATE VIEW view_notifications
AS
SELECT sj.id AS id,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
recipient, `subject`, body, url, id_notifications, id_jobTypes,
id_jobStatus, sj.config, id_dataRows, dataTables_name
FROM notifications n
INNER JOIN scheduledJobs_notifications sj_n ON (sj_n.id_notifications = n.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_n.id_scheduledJobs);
DROP VIEW IF EXISTS view_tasks;
CREATE VIEW view_tasks
AS
SELECT sj.id AS id,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
recipient, t.config, id_tasks, id_jobTypes, id_jobStatus, `description`, id_dataRows, dataTables_name
FROM tasks t
INNER JOIN scheduledJobs_tasks sj_t ON (sj_t.id_tasks = t.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_t.id_scheduledJobs);
DROP VIEW IF EXISTS view_formActions;
CREATE VIEW view_formActions
AS
SELECT fa.id AS id, fa.`name` AS action_name, dt.`name` AS dataTable_name,
fa.id_formProjectActionTriggerTypes, trig.lookup_value AS trigger_type, trig.lookup_code AS trigger_type_code,
config,
dt.id AS id_dataTables
FROM formActions fa 
INNER JOIN lookups trig ON (trig.id = fa.id_formProjectActionTriggerTypes)
LEFT JOIN view_dataTables dt ON (dt.id = fa.id_dataTables);
DROP VIEW IF EXISTS view_scheduledJobs_reminders;
CREATE VIEW view_scheduledJobs_reminders
AS
SELECT r.id_scheduledJobs, r.id_dataTables,
r.session_start_date, r.session_end_date, sju.id_users,l_status.lookup_code as job_status_code, l_status.lookup_value as job_status
FROM scheduledJobs_reminders r
INNER JOIN scheduledJobs sj ON (sj.id = r.id_scheduledJobs)
INNER JOIN scheduledJobs_users sju ON (sj.id = sju.id_scheduledJobs)
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus);
DROP VIEW IF EXISTS view_user_codes;
CREATE VIEW view_user_codes
AS
SELECT u.id, u.email, u.name, u.blocked, 
CASE
	WHEN u.name = 'admin' THEN 'admin'
    WHEN u.name = 'tpf' THEN 'tpf'    
    ELSE IFNULL(vc.code, '-') 
END AS code,
u.intern
FROM users AS u
LEFT JOIN validation_codes vc ON u.id = vc.id_users
WHERE u.intern <> 1 AND u.id_status > 0;
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
DROP VIEW IF EXISTS view_dataTables;
CREATE VIEW view_dataTables
AS
SELECT id, 
`name` AS name_id,
CASE 
	WHEN IFNULL(displayName, '') = '' THEN `name`
    ELSE displayName
END AS `name`,
`timestamp`,
id AS `value`, -- used for slect dropdowns
CASE 
	WHEN IFNULL(displayName, '') = '' THEN `name`
    ELSE displayName
END AS `text` -- used for slect dropdowns
FROM dataTables;
DROP VIEW IF EXISTS view_dataTables_data;
CREATE VIEW view_dataTables_data
AS
SELECT t.id as table_id, r.id AS row_id, r.`timestamp` AS entry_date, col.id AS col_id, 
t.`name` AS `table_name`, col.`name` AS col_name, cell.`value` AS `value`, t.`timestamp`, r.id_users,
t.displayName AS displayName
FROM dataTables t
LEFT JOIN dataRows r ON (t.id = r.id_dataTables)
LEFT JOIN dataCells cell ON (cell.id_dataRows = r.id)
LEFT JOIN dataCols col ON (col.id = cell.id_dataCols);
