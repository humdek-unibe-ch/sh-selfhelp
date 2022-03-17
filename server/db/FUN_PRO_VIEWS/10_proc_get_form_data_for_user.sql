DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_for_user //

CREATE PROCEDURE get_form_data_for_user( form_id_param INT, user_id_param INT )
BEGIN  
    CALL get_form_data_for_user_with_filter(form_id_param, user_id_param, '');
END 
//

DELIMITER ;
