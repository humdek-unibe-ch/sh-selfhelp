DELIMITER //

DROP PROCEDURE IF EXISTS get_user_route_acl //
CREATE PROCEDURE get_user_route_acl(
    IN param_user_id    INT,
    IN param_route_id   INT    -- -1 means “all routes”
)
BEGIN
    SELECT
        param_user_id              AS id_users,
        agr.id_api_routes          AS id_api_routes,
        MAX(agr.acl_select)  AS acl_select,
        MAX(agr.acl_insert)  AS acl_insert,
        MAX(agr.acl_update)  AS acl_update,
        MAX(agr.acl_delete)  AS acl_delete,
        r.route_name,
        r.version,
        r.path,
        r.controller,
        r.methods,
        r.requirements,
        r.params
    FROM users_groups AS ug
    JOIN acl_group_api_routes AS agr
      ON agr.id_groups = ug.id_groups
    JOIN api_routes AS r
      ON r.id = agr.id_api_routes
    WHERE ug.id_users = param_user_id
      AND (param_route_id = -1 OR agr.id_api_routes = param_route_id)
    GROUP BY
        agr.id_api_routes,
        r.route_name,
        r.version,
        r.path,
        r.controller,
        r.methods,
        r.requirements,
        r.params;

END //
DELIMITER ;
