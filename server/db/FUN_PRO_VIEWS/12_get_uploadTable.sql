DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable //

CREATE PROCEDURE get_uploadTable( table_id_param INT )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when col_name = "',
		  col_name,
		  '" then value end) as `',
		  replace(col_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_uploadTables
    where table_id = table_id_param;
	
    IF (@sql is null) THEN
		select table_name from view_uploadTables where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select row_id, ', @sql, ' from view_uploadTables t
		where table_id = ', table_id_param,
		' group by row_id');

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
