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
