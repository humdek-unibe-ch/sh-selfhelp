CREATE OR REPLACE FUNCTION get_field_type_id(field_type varchar(100)) RETURNS INT
AS $$
DECLARE field_type_id INT;   
BEGIN 
	 
	SELECT id INTO field_type_id
	FROM "fieldType"
	WHERE name = field_type;
    RETURN field_type_id;
END
$$ LANGUAGE plpgsql;