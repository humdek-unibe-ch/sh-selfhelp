DELIMITER //

DROP PROCEDURE IF EXISTS get_page_fields //
CREATE PROCEDURE get_page_fields(
    IN page_id INT,
    IN language_id INT,
    IN default_language_id INT,
    IN filter_param VARCHAR(1000),
    IN order_param VARCHAR(1000)
)
READS SQL DATA
DETERMINISTIC
BEGIN  
    -- page_id = -1 returns all pages
    SET @@group_concat_max_len = 32000000;

    SELECT get_page_fields_helper(page_id, language_id, default_language_id) 
      INTO @sql;    
    
    IF @sql IS NULL THEN    
        SELECT * 
          FROM pages 
         WHERE 1=2;
    ELSE 
        BEGIN
            SET @sql = CONCAT(
                'SELECT 
                    p.id,
                    p.keyword,
                    p.url,
                    p.protocol,
                    p.id_actions,
                    "select" AS access_level,
                    p.id_navigation_section,
                    p.parent,
                    p.is_headless,
                    p.nav_position,
                    p.footer_position,
                    p.id_type,
                    p.id_pageAccessTypes,
                    a.lookup_code AS `action`, ',
                 @sql, '
                 FROM pages p
                 LEFT JOIN lookups AS a 
                   ON a.id = p.id_actions 
                  AND a.type_code = "pageActions"
                 LEFT JOIN pageType_fields AS ptf 
                   ON ptf.id_pageType = p.id_type 
                 LEFT JOIN fields AS f 
                   ON f.id = ptf.id_fields
                 WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ')
                 GROUP BY 
                   p.id, p.keyword, p.url, p.protocol, p.id_actions,
                   p.id_navigation_section, p.parent, p.is_headless,
                   p.nav_position, p.footer_position, p.id_type,
                   p.id_pageAccessTypes, a.lookup_code
                 HAVING 1 ', filter_param
            );
            
            IF order_param <> '' THEN             
                SET @sql = CONCAT(
                    'SELECT * FROM (',
                    @sql,
                    ') AS t ', order_param
                );
            END IF;

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END 
//

DELIMITER ;
