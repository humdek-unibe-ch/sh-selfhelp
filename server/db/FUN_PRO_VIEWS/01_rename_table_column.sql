DELIMITER //
DROP PROCEDURE IF EXISTS rename_table_column //
CREATE PROCEDURE rename_table_column(param_table VARCHAR(100), param_old_column_name VARCHAR(100), param_new_column_name VARCHAR(100))
BEGIN	
	DECLARE columnExists INT;
    DECLARE columnType VARCHAR(255);
    SELECT COUNT(*), COLUMN_TYPE 
            INTO columnExists, columnType
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_old_column_name; 
    SET @sqlstmt = (SELECT IF(
		columnExists > 0,        
        CONCAT('ALTER TABLE ', param_table, ' CHANGE COLUMN ', param_old_column_name, ' ', param_new_column_name, ' ', columnType, ';'),
        "SELECT 'Column does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
