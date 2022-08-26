DROP FUNCTION IF EXISTS get_page_fields;
CREATE OR REPLACE FUNCTION get_page_fields( page_id INT, language_id INT, default_language_id INT, filter_param VARCHAR(1000), order_param VARCHAR(1000)) 
RETURNS text 
AS $func$
DECLARE
   sql_str TEXT; 
BEGIN  
	-- page_id -1 returns all pages   
	SELECT get_page_fields_helper(page_id, language_id, default_language_id) INTO sql_str;	
	
    IF (sql_str is null) THEN	
        sql_str := 'SELECT * FROM pages WHERE 1=2';
		RETURN sql_str;
    ELSE 
		BEGIN
		sql_str := CONCAT(
			'select p.id, p.keyword, p.url, p.protocol, p.id_actions, ''select'' AS access_level, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p."id_pageAccessTypes", a.name AS "action", ', 
			sql_str, 
			' FROM pages p
            LEFT JOIN actions AS a ON a.id = p.id_actions
			LEFT JOIN "pageType_fields" AS ptf ON ptf."id_pageType" = p.id_type 
			LEFT JOIN fields AS f ON f.id = ptf.id_fields
			WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ')
            GROUP BY p.id, p.keyword, p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p."id_pageAccessTypes", a.name HAVING 1=1 ', filter_param
        );
        
        IF (order_param <> '') THEN	        
			sql_str := concat(
				'SELECT * FROM (',
				sql_str,
				') AS t ', order_param
			);
		END IF;

		RETURN sql_str;
        END;
    END IF;
END 
$func$ LANGUAGE plpgsql;
