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
