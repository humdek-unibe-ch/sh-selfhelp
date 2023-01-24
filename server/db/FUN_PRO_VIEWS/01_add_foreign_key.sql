DELIMITER //
DROP PROCEDURE IF EXISTS add_foreign_key //
CREATE PROCEDURE add_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100), fk_column VARCHAR(100), fk_references VARCHAR(200))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) > 0,
        "SELECT 'The foreign key already exists in the table'",
        CONCAT('ALTER TABLE ', param_table, ' ADD CONSTRAINT ', fk_name, ' FOREIGN KEY (', fk_column, ') REFERENCES ', fk_references, ' ON DELETE CASCADE ON UPDATE CASCADE;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;
