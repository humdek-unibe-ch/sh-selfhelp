DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter //

CREATE PROCEDURE get_uploadTable_with_filter( table_id_param INT, filter_param VARCHAR(1000) )
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
            SET @sql = CONCAT('select table_name, timestamp, row_id, entry_date, ', @sql, ' from view_uploadTables t
                where table_id = ', table_id_param,
                ' group by table_name, timestamp, row_id HAVING 1 ', filter_param);


            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        end;
    END IF;
END
//

DELIMITER ;
