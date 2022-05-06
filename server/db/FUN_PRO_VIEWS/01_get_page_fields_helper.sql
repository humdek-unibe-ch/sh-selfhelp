DELIMITER //
DROP FUNCTION IF EXISTS get_page_fields_helper //

CREATE FUNCTION get_page_fields_helper(page_id INT, language_id INT) RETURNS TEXT
-- page_id -1 returns all pages
BEGIN 
	SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when f.`name` = "',
		  f.`name`,
		  '" then pft.content end) as `',
		  replace(f.`name`, ' ', ''), '`'
		)
	  ) INTO @sql
	from  pages AS p
	LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id AND (language_id = pft.id_languages OR pft.id_languages = 1)
	LEFT JOIN fields AS f ON f.id = pft.id_fields
    WHERE p.id = page_id OR page_id = -1;
	
    RETURN @sql;
END
//

DELIMITER ;
