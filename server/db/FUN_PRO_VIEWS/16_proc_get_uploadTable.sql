DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable //

CREATE PROCEDURE get_uploadTable( table_id_param INT )
BEGIN
    CALL get_uploadTable_with_filter(table_id_param, '');
END
//

DELIMITER ;
