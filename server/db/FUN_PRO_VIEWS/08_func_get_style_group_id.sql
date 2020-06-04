DELIMITER //
DROP FUNCTION IF EXISTS get_style_group_id //

CREATE FUNCTION get_style_group_id(style_group varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_group_id INT;    
	SELECT id INTO style_group_id
	FROM styleGroup
	WHERE name = style_group COLLATE utf8_unicode_ci;
    RETURN style_group_id;
END
//

DELIMITER ;
