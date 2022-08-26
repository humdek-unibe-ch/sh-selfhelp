CREATE OR REPLACE FUNCTION get_page_fields_helper(page_id INT, language_id INT, default_language_id INT) RETURNS TEXT
AS $$
-- page_id -1 returns all pages
DECLARE
   sql_str TEXT; 
BEGIN 
	sql_str := NULL;
	SELECT
	  STRING_AGG(DISTINCT
		CONCAT(
		  'MAX(CASE WHEN f."name" = ''',
		  f."name",
		  ''' THEN IF(COALESCE((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' LIMIT 1), '''') = '''', (SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = (CASE WHEN f.display = 0 THEN 1 ELSE ',default_language_id,' END) LIMIT 1),(SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' LIMIT 1))  end) as "',
		  replace(f."name", ' ', ''), '"'
		),', '
	  ) INTO sql_str
	FROM  pages AS p
	LEFT JOIN "pageType_fields" AS ptf ON ptf."id_pageType" = p.id_type 
	LEFT JOIN fields AS f ON f.id = ptf.id_fields
    WHERE p.id = page_id OR page_id = -1;
	
    RETURN sql_str;
END
$$ LANGUAGE plpgsql;
