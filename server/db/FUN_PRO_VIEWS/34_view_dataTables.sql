DROP VIEW IF EXISTS view_dataTables;
CREATE VIEW view_dataTables
AS
SELECT id, 
CASE 
	WHEN IFNULL(displayName, '') = '' THEN `name`
    ELSE displayName
END AS `name`,
`timestamp`,
id AS `value`, -- used for slect dropdowns
CASE 
	WHEN IFNULL(displayName, '') = '' THEN `name`
    ELSE displayName
END AS `text` -- used for slect dropdowns
FROM dataTables;
