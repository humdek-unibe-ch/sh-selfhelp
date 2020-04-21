DELIMITER //
DROP FUNCTION IF EXISTS get_field_id //

CREATE FUNCTION get_field_id(field varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_id INT;    
	select id into field_id
	from fields
	where name = field;
    return field_id;
END
//

DELIMITER ;
