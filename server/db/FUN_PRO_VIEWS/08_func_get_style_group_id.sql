DELIMITER //
DROP FUNCTION IF EXISTS get_style_group_id //

CREATE FUNCTION get_style_group_id(style_group varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_group_id INT;    
	select id into style_group_id
	from styleGroup
	where name = style_group;
    return style_group_id;
END
//

DELIMITER ;
