DELIMITER //
DROP FUNCTION IF EXISTS get_style_id //

CREATE FUNCTION get_style_id(style varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_id INT;    
	select id into style_id
	from styles
	where name = style;
    return style_id;
END
//

DELIMITER ;
