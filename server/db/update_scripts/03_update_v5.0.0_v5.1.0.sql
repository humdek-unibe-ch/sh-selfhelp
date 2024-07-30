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

CALL add_table_column('hooks', 'priority', 'INT DEFAULT 10');

drop view if exists view_style_fields;
create view view_style_fields
as
select s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.display, f.position, 
sf.default_value, sf.help, sf.disabled, sf.hidden
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles)
left join view_fields f on (f.field_id = sf.id_fields);


