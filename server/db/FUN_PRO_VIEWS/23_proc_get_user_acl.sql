DELIMITER //

DROP PROCEDURE IF EXISTS get_user_acl //
CREATE PROCEDURE get_user_acl(param_user_id INT, param_page_id INT) 
BEGIN

    SELECT
        param_user_id AS id_users,
        id_pages,
        MAX(acl_select) AS acl_select,
        MAX(acl_insert) AS acl_insert,
        MAX(acl_update) AS acl_update,
        MAX(acl_delete) AS acl_delete,
        keyword,
        url,
        protocol,
        id_actions,
        id_navigation_section,
        parent,
        is_headless,
        nav_position,
        footer_position,
        id_type,
        id_pageAccessTypes
    FROM
        (
            -- UNION part 1: users_groups and acl_groups
            SELECT
                ug.id_users,
                acl.id_pages,
                acl.acl_select,
                acl.acl_insert,
                acl.acl_update,
                acl.acl_delete,
                p.keyword,
                p.url,
                p.protocol,
                p.id_actions,
                p.id_navigation_section,
                p.parent,
                p.is_headless,
                p.nav_position,
                p.footer_position,
                p.id_type,
                p.id_pageAccessTypes
            FROM
                users u
            INNER JOIN users_groups AS ug ON ug.id_users = u.id
            INNER JOIN acl_groups acl ON acl.id_groups = ug.id_groups
            INNER JOIN pages p ON acl.id_pages = p.id
            WHERE
                ug.id_users = param_user_id
                AND (param_page_id = -1 OR acl.id_pages = param_page_id)

            UNION ALL

            -- UNION part 2: acl_users
            SELECT
                acl.id_users,
                acl.id_pages,
                acl.acl_select,
                acl.acl_insert,
                acl.acl_update,
                acl.acl_delete,
                p.keyword,
                p.url,
                p.protocol,
                p.id_actions,
                p.id_navigation_section,
                p.parent,
                p.is_headless,
                p.nav_position,
                p.footer_position,
                p.id_type,
                p.id_pageAccessTypes
            FROM
                acl_users acl
            INNER JOIN pages p ON acl.id_pages = p.id
            WHERE
                acl.id_users = param_user_id
                AND (param_page_id = -1 OR acl.id_pages = param_page_id)

            UNION ALL

            -- UNION part 3: open access pages
            SELECT
                param_user_id AS id_users,
                p.id AS id_pages,
                1 AS acl_select,
                0 AS acl_insert,
                0 AS acl_update,
                0 AS acl_delete,
                p.keyword,
                p.url,
                p.protocol,
                p.id_actions,
                p.id_navigation_section,
                p.parent,
                p.is_headless,
                p.nav_position,
                p.footer_position,
                p.id_type,
                p.id_pageAccessTypes
            FROM
                pages p
            WHERE
                p.is_open_access = 1
        ) AS combined_acl
    GROUP BY
        param_user_id,
        id_pages,
        keyword,
        url,
        protocol,
        id_actions,
        id_navigation_section,
        parent,
        is_headless,
        nav_position,
        footer_position,
        id_type,
        id_pageAccessTypes;

END
//

DELIMITER ;
