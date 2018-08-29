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

    /* Public Methods *********************************************************/

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

    public function dump_acl_table()
    {
        $res = true;
        foreach($this->gacl as $key => $acl)
        {
            $pid = $this->db->fetch_page_id_by_keyword($key);
            foreach($acl["acl"] as $lvl => $val)
            {
                $grant_method = "grant_access_" . $lvl;
                $revoke_method = "revoke_access_" . $lvl;
                if($val)
                    $res &= $this->acl->$grant_method($this->gid, $pid,
                        $_SESSION['id_user']);
                else
                    $res &= $this->acl->$revoke_method($this->gid, $pid,
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
                "select" => $this->gacl["cmsSelect"]["acl"]["select"]
                    && $this->gacl["cms-link"]["acl"]["select"],
                "insert" => $this->gacl["cmsInsert"]["acl"]["insert"]
                    && $this->gacl["cms-link"]["acl"]["select"],
                "update" => $this->gacl["cmsUpdate"]["acl"]["update"]
                    && $this->gacl["cms-link"]["acl"]["select"],
                "delete" => $this->gacl["cmsDelete"]["acl"]["delete"]
                    && $this->gacl["cms-link"]["acl"]["select"],
            ),
        );
        $sgacl["user"] = array(
            "name" => "User Management",
            "acl" => array(
                "select" => $this->gacl["userSelect"]["acl"]["select"]
                    && $this->gacl["groupSelect"]["acl"]["select"]
                    && $this->gacl["cms-link"]["acl"]["select"],
                "insert" => $this->gacl["userInsert"]["acl"]["insert"]
                    && $this->gacl["cms-link"]["acl"]["select"],
                "update" => $this->gacl["userUpdate"]["acl"]["update"]
                    && $this->gacl["groupUpdate"]["acl"]["update"]
                    && $this->gacl["cms-link"]["acl"]["select"],
                "delete" => $this->gacl["userDelete"]["acl"]["delete"]
                    && $this->gacl["cms-link"]["acl"]["select"],
            ),
        );
        return $sgacl;
    }

    public function set_user_access($lvl)
    {
        $this->gacl["cms-link"]["acl"]["select"] = true;
        $this->gacl["user" . ucfirst($lvl)]["acl"]["select"] = true;
        $this->gacl["user" . ucfirst($lvl)]["acl"][$lvl] = true;
        if(in_array($lvl, array("select", "update")))
        {
            $this->gacl["group" . ucfirst($lvl)]["acl"]["select"] = true;
            $this->gacl["group" . ucfirst($lvl)]["acl"][$lvl] = true;
        }
    }

    public function set_page_access($lvl)
    {
        $this->gacl["cms-link"]["acl"]["select"] = true;
        $this->gacl["cms" . ucfirst($lvl)]["acl"]["select"] = true;
        $this->gacl["cms" . ucfirst($lvl)]["acl"][$lvl] = true;
    }

    private function get_cms_mod_access()
    {
        return $this->gacl["cms-link"]["acl"]["select"]
            && $this->gacl["cmsSelect"]["acl"]["select"]
            && $this->gacl["cmsUpdate"]["acl"]["select"]
            && $this->gacl["cmsUpdate"]["acl"]["update"];
    }

    private function set_cms_mod_access()
    {
        $this->gacl["cms-link"]["acl"]["select"] = true;
        $this->gacl["cmsSelect"]["acl"]["select"] = true;
        $this->gacl["cmsUpdate"]["acl"]["select"] = true;
        $this->gacl["cmsUpdate"]["acl"]["update"] = true;
    }

    private function get_core_access($lvl)
    {
        $res = true;
        if($lvl == "select")
            $res = $this->gacl["request"]["acl"]["select"]
                && $this->gacl["logout"]["acl"]["select"]
                && $this->gacl["profile-link"]["acl"]["select"];
        else
        {
            if($lvl == "update")
                $res = $this->gacl["logout"]["acl"]["update"]
                    && $this->gacl["profile-link"]["acl"]["update"];
            $res &= $this->get_cms_mod_access();
        }

        $res &= $this->gacl["agb"]["acl"][$lvl]
            && $this->gacl["disclaimer"]["acl"][$lvl]
            && $this->gacl["impressum"]["acl"][$lvl]
            && $this->gacl["missing"]["acl"][$lvl]
            && $this->gacl["no_access"]["acl"][$lvl]
            && $this->gacl["profile"]["acl"][$lvl]
            && $this->gacl["home"]["acl"][$lvl]
            && $this->gacl["login"]["acl"][$lvl];
        return $res;
    }

    public function set_core_access($lvl)
    {
        if($lvl == "select")
        {
            $this->gacl["request"]["acl"]["select"] = true;
            $this->gacl["logout"]["acl"]["select"] = true;
            $this->gacl["profile-link"]["acl"]["select"] = true;
        }
        else
        {
            if($lvl == "update")
            {
                $this->gacl["logout"]["acl"]["update"] = true;
                $this->gacl["profile-link"]["acl"]["update"] = true;
            }
            $this->set_cms_mod_access();
        }

        $this->gacl["agb"]["acl"][$lvl] = true;
        $this->gacl["disclaimer"]["acl"][$lvl] = true;
        $this->gacl["impressum"]["acl"][$lvl] = true;
        $this->gacl["missing"]["acl"][$lvl] = true;
        $this->gacl["no_access"]["acl"][$lvl] = true;
        $this->gacl["no_access_guest"]["acl"][$lvl] = true;
        $this->gacl["profile"]["acl"][$lvl] = true;
        $this->gacl["home"]["acl"][$lvl] = true;
        $this->gacl["login"]["acl"][$lvl] = true;
    }

    private function get_experiment_access($lvl)
    {
        $res = true;
        if($lvl != "select")
            $res = $this->get_cms_mod_access();

        $res &= $this->gacl["contact"]["acl"][$lvl]
            && $this->gacl["protocols"]["acl"][$lvl]
            && $this->gacl["session"]["acl"][$lvl]
            && $this->gacl["sessions"]["acl"][$lvl];
        return $res;
    }

    public function set_experiment_access($lvl)
    {
        if($lvl != "select")
            $this->set_cms_mod_access();

        $this->gacl["contact"]["acl"][$lvl] = true;
        $this->gacl["protocols"]["acl"][$lvl] = true;
        $this->gacl["session"]["acl"][$lvl] = true;
        $this->gacl["sessions"]["acl"][$lvl] = true;
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

    public function init_acl_table()
    {
        foreach($this->gacl as $key => $acl)
            foreach($acl["acl"] as $lvl => $val)
                $this->gacl[$key]["acl"][$lvl] = false;
    }
}
?>
