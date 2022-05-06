DELIMITER //

DROP PROCEDURE IF EXISTS get_page_fields //

CREATE PROCEDURE get_page_fields( page_id INT, language_id INT, filter_param VARCHAR(1000), order_param VARCHAR(1000))
BEGIN  
	-- page_id -1 returns all pages
    SET @@group_concat_max_len = 32000;
	SELECT get_page_fields_helper(page_id, language_id) INTO @sql;	
	
    IF (@sql is null) THEN	
        SELECT * FROM pages WHERE 1=2;
    ELSE 
		BEGIN
		SET @sql = CONCAT(
			'select p.id, p.keyword, p.url, p.protocol, p.id_actions, "select" AS access_level, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p.id_pageAccessTypes, a.name AS `action`, ', 
			@sql, 
			'FROM pages p
            LEFT JOIN actions AS a ON a.id = p.id_actions
			LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id   
			LEFT JOIN fields AS f ON pft.id_fields = f.id
			WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ') AND ( IFNULL(id_languages, 1) = 1 OR id_languages=', language_id, ') 
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
