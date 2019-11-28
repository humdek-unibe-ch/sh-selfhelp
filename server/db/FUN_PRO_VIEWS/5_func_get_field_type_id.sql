DELIMITER //
DROP FUNCTION IF EXISTS get_field_type_id //

CREATE FUNCTION get_field_type_id(field_type varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_type_id INT;    
	select id into field_type_id
	from fieldType
	where name = field_type;
    return field_type_id;
END
//

DELIMITER ;
