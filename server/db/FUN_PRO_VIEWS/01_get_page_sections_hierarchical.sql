DELIMITER //

DROP PROCEDURE IF EXISTS `get_page_sections_hierarchical` //

CREATE PROCEDURE `get_page_sections_hierarchical`(IN page_id INT)
BEGIN
    WITH RECURSIVE section_hierarchy AS (
        -- Base case: get top-level sections for the page, position starts from 10
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            st.can_have_children,
            ps.`position` AS position,      -- Start at 10
            0 AS `level`,
            CAST(s.id AS CHAR(200)) AS `path`
        FROM pages_sections ps
        JOIN sections s ON ps.id_sections = s.id
        JOIN styles st ON s.id_styles = st.id
        LEFT JOIN sections_hierarchy sh ON s.id = sh.child
        WHERE ps.id_pages = page_id
        AND sh.parent IS NULL
        
        UNION ALL
        
        -- Recursive case: get children of sections
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            st.can_have_children,
            sh.position AS position,        -- Add 10 to each level
            h.`level` + 1,
            CONCAT(h.`path`, ',', s.id) AS `path`
        FROM section_hierarchy h
        JOIN sections_hierarchy sh ON h.id = sh.parent
        JOIN sections s ON sh.child = s.id
        JOIN styles st ON s.id_styles = st.id
    )
    
    -- Select the result
    SELECT 
        id,
        `name`,
        id_styles,
        style_name,
        can_have_children,
        position,
        `level`,
        `path`
    FROM section_hierarchy
    ORDER BY `path`, `position`;
END //

DELIMITER ;
