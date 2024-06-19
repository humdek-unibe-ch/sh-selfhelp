DELIMITER //

DROP PROCEDURE IF EXISTS get_dataTable_with_filter //

CREATE PROCEDURE get_dataTable_with_filter( 
	IN table_id_param INT, 
    IN user_id_param INT, 
    IN filter_param VARCHAR(1000),
    IN exclude_deleted_param BOOLEAN -- If true it will exclude the deleted records and it will not return them
)
-- if the filter_param contains any of these we additionaly filter: LAST_HOUR, LAST_DAY, LAST_WEEK, LAST_MONTH, LAST_YEAR
READS SQL DATA
DETERMINISTIC
BEGIN
    SET @@group_concat_max_len = 32000000;
    SET @sql = NULL;
    SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'MAX(CASE WHEN col.`name` = "',
                col.name,
                '" THEN `value` END) AS `',
            replace(col.name, ' ', ''), '`'
        )
    ) INTO @sql
    FROM  dataTables t
	INNER JOIN dataCols col on (t.id = col.id_dataTables)
    WHERE t.id = table_id_param AND col.`name` NOT IN ('id_users','record_id','user_name','id_actionTriggerTypes','triggerType');

    IF (@sql is null) THEN
        SELECT `name` from view_dataTables where 1=2;
    ELSE
        BEGIN
			SET @user_filter = '';
            IF user_id_param > 0 THEN
				SET @user_filter = CONCAT(' AND r.id_users = ', user_id_param);
            END IF;	
            
            SET @time_period_filter = '';
            CASE 
				WHEN filter_param LIKE '%LAST_HOUR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 HOUR';
				WHEN filter_param LIKE '%LAST_DAY%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 DAY';
				WHEN filter_param LIKE '%LAST_WEEK%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 WEEK';
				WHEN filter_param LIKE '%LAST_MONTH%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 MONTH';
				WHEN filter_param LIKE '%LAST_YEAR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 YEAR';
				ELSE
					SET @time_period_filter = '';					
			END CASE;
            
            SET @exclude_deleted_filter = '';
            CASE 
				WHEN exclude_deleted_param = TRUE THEN
					SET @exclude_deleted_filter = CONCAT(' AND IFNULL(r.id_actionTriggerTypes, 0) <> ', (SELECT id FROM lookups WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'deleted' LIMIT 0,1));				
				ELSE
					SET @exclude_deleted_filter = '';					
			END CASE;
            
            SET @sql = CONCAT('SELECT * FROM (SELECT r.id AS record_id, 
					r.`timestamp` AS entry_date, r.id_users, u.`name` AS user_name, r.id_actionTriggerTypes, l.lookup_code AS triggerType,', @sql, 
					' FROM dataTables t
					INNER JOIN dataRows r ON (t.id = r.id_dataTables)
					INNER JOIN dataCells cell ON (cell.id_dataRows = r.id)
					INNER JOIN dataCols col ON (col.id = cell.id_dataCols)
                    LEFT JOIN users u ON (r.id_users = u.id)
                    LEFT JOIN lookups l ON (l.id = r.id_actionTriggerTypes)
					WHERE t.id = ', table_id_param, @user_filter, @time_period_filter, @exclude_deleted_filter, 
					' GROUP BY r.id ) AS r WHERE 1=1  ', filter_param);
            -- select @sql;
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END
//

DELIMITER ;
