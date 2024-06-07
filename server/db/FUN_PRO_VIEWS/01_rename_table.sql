DELIMITER //
DROP PROCEDURE IF EXISTS rename_table //
CREATE PROCEDURE rename_table(param_old_table_name VARCHAR(100), param_new_table_name VARCHAR(100))
BEGIN	
	DECLARE tableExists INT;
    SELECT COUNT(*) 
            INTO tableExists
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_old_table_name; 
    SET @sqlstmt = (SELECT IF(
		tableExists > 0,        
        CONCAT('RENAME TABLE ', param_old_table_name, ' TO ', param_new_table_name),
        "SELECT 'Table does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
