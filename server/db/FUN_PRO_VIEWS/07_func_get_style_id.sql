CREATE OR REPLACE FUNCTION get_style_id(style varchar(100)) RETURNS INT
AS $$
DECLARE
   style_id integer; 
BEGIN    
	SELECT id INTO style_id
	FROM styles
	WHERE name = style;
    RETURN style_id;
END
$$ LANGUAGE plpgsql;
