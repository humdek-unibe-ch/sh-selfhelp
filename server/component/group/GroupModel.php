<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the group component such
 * that the data can easily be displayed in the view of the component.
 */
class GroupModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $selected_group;
    private $gid;
    private $gacl;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $gid
     *  The id of the current selected group.
     */
    public function __construct($services, $gid)
    {
        parent::__construct($services);
        $this->gid = $gid;
        $this->selected_group = null;
        if($gid != null) $this->selected_group = $this->fetch_group($gid);
        $this->gacl = $this->fetch_acl_by_group($gid);
    }

    /* Private Methods ********************************************************/

    /**
     * Fetch the user data from the db.
     *
     * @param int $gid
     *  The id of the group to fetch.
     * @retval array
     *  An array with the following keys:
     *   'id':      The id of the group.
     *   'name':    The name of the group.
     *   'desc':    The description of the group.
     */
    private function fetch_group($gid)
    {
        $sql = "SELECT g.name, g.description FROM groups AS g
            WHERE g.id = :gid";
        $res = $this->db->query_db_first($sql, array(":gid" => $gid));
        if(!$res) return null;
        return array(
            "id" => $gid,
            "name" => $res['name'],
            "desc" => $res['description'],
        );
    }

    /**
     * Fetch the list of groups
     *
     * @retval array
     *  A list of db items where each item has the keys
     *   'id':      The id of the group.
     *   'name':    The name of the group.
     */
    private function fetch_groups()
    {
        $sql = "SELECT g.id, g.name FROM groups AS g
            ORDER BY g.name";
        return $this->db->query_db($sql);
    }

    /**
     * Fetch all access rights to pages of a specific group.
     *
     * @param int $gid
     *  The id of the group.
     * @retval array
     *  A list of key value pairs where the key is the page id and the value
     *  an array of booleans, indicating the access rights select, insert,
     *  update, and delete (in this order).
     */
    private function fetch_acl_by_group($gid)
    {
        $acl = array();
        $sql = "SELECT p.id, p.keyword FROM pages AS p ORDER BY p.keyword";
        $pages = $this->db->query_db($sql);
        foreach($pages as $page)
        {
            $pid = intval($page['id']);
            $acl[$page['keyword']] = array(
                "name" => $page['keyword'],
                "acl" => array(
                    "select" => $this->acl->has_access_select($gid, $pid, true),
                    "insert" => $this->acl->has_access_insert($gid, $pid, true),
                    "update" => $this->acl->has_access_update($gid, $pid, true),
                    "delete" => $this->acl->has_access_delete($gid, $pid, true),
                )
            );
        }
        return $acl;
    }

    /**
     * Fetch all pages of a specific type from the db.
     *
     * @param string $type
     *  The name of the type to fetch.
     * @return array
     *  An array of page items where each item has the following keys:
     *   'keyword':     The name of the page.
     *   'id_actions':  The id of the action the page ought to perform. This is
     *                  used to identify links (where the label can be updated
     *                  but no sections can be added or removed).
     */
    private function fetch_pages_by_type($type)
    {
        $sql = "SELECT keyword, id_actions FROM pages
            LEFT JOIN pageType AS pt ON pages.id_type = pt.id
            WHERE pt.name = :type";
        return $this->db->query_db($sql, array(":type" => $type));
    }

    /**
     * Helper function to check whether access to a type of page collections at
     * a given level is permitted.
     *
     * @param string $type
     *  The type of the page collections i.e core or experiment
     * @param string $lvl
     *  The level of access e.g. select, insert, update, or delete.
     * @retval bool
     *  True if access is allowed, false otherwise.
     */
    private function get_access($type, $lvl)
    {
        $res = true;
        if($lvl != "select")
            $res &= $this->get_cms_mod_access();

        $pages = $this->fetch_pages_by_type($type);
        foreach($pages as $page)
        {
            if($page["id_actions"] == null)
            {
                // it's a link, can only be selected and updated
                if($lvl == "select" || $lvl == "update")
                    $res &= $this->gacl[$page["keyword"]]["acl"][$lvl];
            }
            else
                $res &= $this->gacl[$page["keyword"]]["acl"][$lvl];
        }
        return $res;
    }

    /**
     * Check whether the cms permissions allow to modify pages.
     *
     * @retval bool
     *  True if update access is allowed, false otherwise.
     */
    private function get_cms_mod_access()
    {
        return $this->gacl["cms-link"]["acl"]["select"]
            && $this->gacl["cmsSelect"]["acl"]["select"]
            && $this->gacl["cmsUpdate"]["acl"]["select"]
            && $this->gacl["cmsUpdate"]["acl"]["update"];
    }

    /**
     * Check whether the core permissions corresponding to a certain level are
     * given.
     *
     * @param string $lvl
     *  The level of access e.g. select, insert, update, or delete.
     * @retval bool
     *  True if access is allowed, false otherwise.
     */
    private function get_core_access($lvl)
    {
        $res = true;
        if($lvl == "select")
            $res &= $this->gacl["request"]["acl"]["select"];
        $res &= $this->get_access("core", $lvl);
        return $res;
    }

    /**
     * Check whether the experiment permissions corresponding to a certain
     * level are given.
     *
     * @param string $lvl
     *  The level of access e.g. select, insert, update, or delete.
     * @retval bool
     *  True if access is allowed, false otherwise.
     */
    private function get_experiment_access($lvl)
    {
        return $this->get_access("experiment", $lvl);
    }

    /**
     * Check whether the page permissions corresponding to a certain level are
     * given.
     *
     * @param string $lvl
     *  The level of access e.g. select, insert, update, or delete.
     * @retval bool
     *  True if access is allowed, false otherwise.
     */
    private function get_page_access($lvl)
    {
        $res = $this->gacl["cms-link"]["acl"]["select"];
        $res &= $this->gacl["cms" . ucfirst($lvl)]["acl"]["select"];
        $res &= $this->gacl["cms" . ucfirst($lvl)]["acl"][$lvl];
        return $res;
    }

    /**
     * Check whether the user permissions corresponding to a certain level are
     * given.
     *
     * @param string $lvl
     *  The level of access e.g. select, insert, update, or delete.
     * @retval bool
     *  True if access is allowed, false otherwise.
     */
    private function get_user_access($lvl)
    {
        $res = $this->gacl["cms-link"]["acl"]["select"];
        $res &= $this->gacl["user" . ucfirst($lvl)]["acl"]["select"];
        $res &= $this->gacl["export"]["acl"]["select"];
        $res &= $this->gacl["user" . ucfirst($lvl)]["acl"][$lvl];
        if($lvl == "select" || $lvl == "update")
        {
            $res &= $this->gacl["group" . ucfirst($lvl)]["acl"]["select"];
            $res &= $this->gacl["group" . ucfirst($lvl)]["acl"][$lvl];
        }
        return $res;
    }

    /**
     * Helper function to allow access to a type of page collections at a given
     * level.
     *
     * @param string $type
     *  The type of the page collections i.e core or experiment
     * @param string $lvl
     *  The level of access to be set e.g. select, insert, update, or delete.
     */
    private function set_access($type, $lvl)
    {
        if($lvl != "select")
            $this->set_cms_mod_access();

        $pages = $this->fetch_pages_by_type($type);
        foreach($pages as $page)
        {
            if($page["id_actions"] == null)
            {
                // it's a link, can only be selected and updated
                if($lvl == "select" || $lvl == "update")
                    $this->gacl[$page["keyword"]]["acl"][$lvl] = true;
            }
            else
                $this->gacl[$page["keyword"]]["acl"][$lvl] = true;
        }
    }

    /**
     * Set the access level to the cms page such that it allows to update the
     * content of pages.
     */
    private function set_cms_mod_access()
    {
        $this->gacl["cms-link"]["acl"]["select"] = true;
        $this->gacl["cmsSelect"]["acl"]["select"] = true;
        $this->gacl["cmsUpdate"]["acl"]["select"] = true;
        $this->gacl["cmsUpdate"]["acl"]["update"] = true;
    }

    /* Public Methods *********************************************************/

    /**
     * Checks whether the current user is allowed to create new groups.
     *
     * @retavl bool
     *  True if the current user can create new groups, false otherwise.
     */
    public function can_create_new_group()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("groupInsert"));
    }

    /**
     * Checks whether the current user is allowed to delete groups.
     *
     * @retavl bool
     *  True if the current user can delete groups, false otherwise.
     */
    public function can_delete_group()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("groupDelete"));
    }

    /**
     * Checks whether the current user is allowed to modify the ACL of groups.
     *
     * @retavl bool
     *  True if the current user can modify the ACL of groups, false otherwise.
     */
    public function can_modify_group_acl()
    {
        return $this->acl->has_access_update($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("groupUpdate"));
    }

    /**
     * Delete a group from the database.
     *
     * @param int $gid
     *  The id of the group to be deleted.
     * @retval bool
     *  True on success, false on failure.
     */
    public function delete_group($gid)
    {
        return $this->db->remove_by_fk("groups", "id", $gid);
    }

    /**
     * Updates the db table with the acl values stored in the property
     * GroupModel::gacl.
     *
     * @param int $gid
     *  The group id where the acl will be updated. If no id is provided, the
     *  current group id GroupModel::gid is used.
     * @retval bool
     *  True on success, false otherwise.
     */
    public function dump_acl_table($gid = null)
    {
        if($gid == null) $gid = $this->gid;
        $res = true;
        foreach($this->gacl as $key => $acl)
        {
            $pid = $this->db->fetch_page_id_by_keyword($key);
            foreach($acl["acl"] as $lvl => $val)
            {
                $grant_method = "grant_access_" . $lvl;
                $revoke_method = "revoke_access_" . $lvl;
                if($val)
                    $res &= $this->acl->$grant_method($gid, $pid,
                        $_SESSION['id_user']);
                else
                    $res &= $this->acl->$revoke_method($gid, $pid,
                        $_SESSION['id_user']);
            }
        }
        return $res;
    }

    /**
     * Get the ACL info of the selected group.
     *
     * @retval array
     *  See UserModel::fetch_acl_by_group.
     */
    public function get_acl_selected_group()
    {
        return $this->gacl;
    }

    /**
     * Get the simplified ACL info of the selected group.
     *
     * @retval array
     *  See UserModel::fetch_acl_by_group.
     */
    public function get_simple_acl_selected_group()
    {
        $sgacl = array();
        $sgacl["core"] = array(
            "name" => "Core Content",
            "acl" => array(
                "select" => $this->get_core_access("select"),
                "insert" => $this->get_core_access("insert"),
                "update" => $this->get_core_access("update"),
                "delete" => $this->get_core_access("delete"),
            ),
        );
        $sgacl["experiment"] = array(
            "name" => "Experiment Content",
            "acl" => array(
                "select" => $this->get_experiment_access("select"),
                "insert" => $this->get_experiment_access("insert"),
                "update" => $this->get_experiment_access("update"),
                "delete" => $this->get_experiment_access("delete"),
            ),
        );
        $sgacl["page"] = array(
            "name" => "Page Management",
            "acl" => array(
                "select" => $this->get_page_access("select"),
                "insert" => $this->get_page_access("insert"),
                "update" => $this->get_page_access("update"),
                "delete" => $this->get_page_access("delete"),
            ),
        );
        $sgacl["user"] = array(
            "name" => "User Management",
            "acl" => array(
                "select" => $this->get_user_access("select"),
                "insert" => $this->get_user_access("insert"),
                "update" => $this->get_user_access("update"),
                "delete" => $this->get_user_access("delete"),
            ),
        );
        return $sgacl;
    }

    /**
     * Return the properties of the current group.
     *
     * @retval array
     *  An array of group properties (see UserModel::fetch_group).
     */
    public function get_selected_group()
    {
        return $this->selected_group;
    }

    /**
     * Get a list of groups and prepares the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':      The id of the group.
     *   'title':   The name of the group.
     *   'url':     The url pointing to the group.
     */
    public function get_groups()
    {
        $res = array();
        foreach($this->fetch_groups() as $group)
        {
            $id = intval($group["id"]);
            $res[] = array(
                "id" => $id,
                "title" => $group["name"],
                "url" => $this->get_link_url("groupSelect", array("gid" => $id))
            );
        }
        return $res;
    }

    /**
     * Get the id of the selected group.
     *
     * @retval int
     *  The id of the selected group.
     */
    public function get_gid()
    {
        return $this->gid;
    }

    /**
     * Initialized each value of the local ACL table, i.e. the property
     * GroupModel::gacl, to false.
     */
    public function init_acl_table()
    {
        foreach($this->gacl as $key => $acl)
            foreach($acl["acl"] as $lvl => $val)
                $this->gacl[$key]["acl"][$lvl] = false;
    }

    /**
     * Insert a new group to the DB.
     *
     * @param string $name
     *  The name of the group to be added.
     * @param string $desc
     *  The description of the group to be added.
     * @retval int
     *  The id of the new group or false if the process failed.
     */
    public function insert_new_group($name, $desc)
    {
        return $this->db->insert("groups", array(
            "name" => $name,
            "description" => $desc,
        ));
    }

    /**
     * Set the access level for all pages that are targeted by the core content
     * collection.
     *
     * @param string $lvl
     *  The level of access to be set e.g. select, insert, update, or delete.
     */
    public function set_core_access($lvl)
    {
        if($lvl == "select")
            $this->gacl["request"]["acl"]["select"] = true;
        $this->set_access("core", $lvl);
    }

    /**
     * Set the access level for all pages that are targeted by the experiment
     * content collection.
     *
     * @param string $lvl
     *  The level of access to be set e.g. select, insert, update, or delete.
     */
    public function set_experiment_access($lvl)
    {
        $this->set_access("experiment", $lvl);
    }

    /**
     * Set the access level for all pages that are targeted by the user
     * management collection i.e. the user and group pages.
     *
     * @param string $lvl
     *  The level of access to be set e.g. select, insert, update, or delete.
     */
    public function set_user_access($lvl)
    {
        $this->gacl["cms-link"]["acl"]["select"] = true;
        $this->gacl["user" . ucfirst($lvl)]["acl"]["select"] = true;
        $this->gacl["user" . ucfirst($lvl)]["acl"][$lvl] = true;
        $this->gacl["group" . ucfirst($lvl)]["acl"]["select"] = true;
        $this->gacl["group" . ucfirst($lvl)]["acl"][$lvl] = true;
        $this->gacl["export"]["acl"]["select"] = true;
    }

    /**
     * Set the access level for all pages that are targeted by the page
     * management collection i.e. the cms pages.
     *
     * @param string $lvl
     *  The level of access to be set e.g. select, insert, update, or delete.
     */
    public function set_page_access($lvl)
    {
        $this->gacl["cms-link"]["acl"]["select"] = true;
        $this->gacl["cms" . ucfirst($lvl)]["acl"]["select"] = true;
        $this->gacl["cms" . ucfirst($lvl)]["acl"][$lvl] = true;
    }
}
?>
