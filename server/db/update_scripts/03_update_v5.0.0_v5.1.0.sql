-- set DB version
UPDATE version
SET version = 'v5.1.0';

DELIMITER //
DROP PROCEDURE IF EXISTS add_table_column //
CREATE PROCEDURE add_table_column(param_table VARCHAR(100), param_column VARCHAR(100), param_column_type VARCHAR(500))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*) 
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_column 
		) > 0,
        "SELECT 'Column already exists in the table'",
        CONCAT('ALTER TABLE ', param_table, ' ADD COLUMN ', param_column, ' ', param_column_type, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


CALL add_table_column('hooks', 'priority', 'INT DEFAULT 10')
