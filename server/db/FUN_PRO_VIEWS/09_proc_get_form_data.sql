DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data //

CREATE PROCEDURE get_form_data( form_id_param INT )
BEGIN  
    CALL get_form_data_with_filter(form_id_param, '');
END 
//

DELIMITER ;
