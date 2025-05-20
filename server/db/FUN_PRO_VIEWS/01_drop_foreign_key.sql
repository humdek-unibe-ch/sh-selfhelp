DELIMITER //
DROP PROCEDURE IF EXISTS drop_foreign_key //
CREATE PROCEDURE drop_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) = 0,
        "SELECT 'Foreign key does not exist'",
        CONCAT('ALTER TABLE `', param_table, '` DROP FOREIGN KEY ', fk_name, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
