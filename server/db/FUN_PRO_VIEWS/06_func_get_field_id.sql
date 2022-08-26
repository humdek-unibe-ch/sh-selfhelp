CREATE OR REPLACE FUNCTION get_field_id(field varchar(100)) RETURNS INT
AS $$
DECLARE
   field_id integer; 
BEGIN 
	--DECLARE field_id INT;    
	SELECT id INTO field_id
	FROM fields
	WHERE name = field;
    RETURN field_id;
END;
$$ LANGUAGE plpgsql;
