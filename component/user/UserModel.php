<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the user component such
 * that the data can easily be displayed in the view of the component.
 */
class UserModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $selected_user;
    private $uid;
    private $did;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $uid, $did=null)
    {
        parent::__construct($services);
        $this->uid = $uid;
        $this->did = $did;
        $this->selected_user = null;
        if($uid != null) $this->selected_user = $this->fetch_user($uid);
    }

    /* Private Methods ********************************************************/

    private function fetch_user($uid)
    {
        $sql = "SELECT u.email, (u.password IS NOT NULL) AS active, u.blocked
            FROM users AS u
            WHERE u.id = :uid and u.intern <> 1";
        $res = $this->db->query_db_first($sql, array(":uid" => $uid));
        if(!$res) return null;
        return array(
            "id" => $uid,
            "email" => $res['email'],
            "active" => ($res['active'] == '1') ? true : false,
            "blocked" => ($res['blocked'] == '1') ? true : false,
        );
    }

    private function fetch_users()
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            WHERE u.intern <> 1
            ORDER BY u.email";
        return $this->db->query_db($sql);
    }

    private function fetch_user_groups($uid)
    {
        $sql = "SELECT g.id, g.name AS title FROM groups AS g
            LEFT JOIN users_groups AS ug ON ug.id_groups = g.id
            WHERE ug.id_users = :uid";
        $res_db = $this->db->query_db($sql, array(":uid" => $uid));
        $res = array();
        foreach($res_db as $item)
        {
            $item["id"] = intval($item["id"]);
            $res[] = $item;
        }
        return $res;
    }

    private function fetch_acl_by_user($uid)
    {
        $acl = array();
        $sql = "SELECT p.id, p.keyword FROM pages AS p ORDER BY p.keyword";
        $pages = $this->db->query_db($sql);
        foreach($pages as $page)
        {
            $pid = intval($page['id']);
            $acl[$page['keyword']] = array(
                $this->acl->has_access_select($uid, $pid),
                $this->acl->has_access_insert($uid, $pid),
                $this->acl->has_access_update($uid, $pid),
                $this->acl->has_access_delete($uid, $pid)
            );
        }
        return $acl;
    }

    /* Public Methods *********************************************************/

    public function can_delete_user()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userDelete"));
    }

    public function can_create_new_user()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userInsert"));
    }

    public function can_modify_user()
    {
        return $this->acl->has_access_update($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userUpdate"));
    }

    public function get_rm_group_name()
    {
        if($this->did == null) return "";
        $sql = "SELECT name FROM groups WHERE id = :gid";
        $res = $this->db->query_db_first($sql, array(":gid" => $this->did));
        return $res["name"];
    }

    public function get_selected_user()
    {
        return $this->selected_user;
    }

    public function get_selected_user_groups()
    {
        return $this->fetch_user_groups($this->uid);
    }

    public function get_users()
    {
        $res = array();
        foreach($this->fetch_users() as $user)
        {
            $id = intval($user["id"]);
            $res[] = array(
                "id" => $id,
                "title" => $user["email"],
                "url" => $this->get_link_url("user", array("uid" => $id))
            );
        }
        return $res;
    }

    public function get_acl_selected_user()
    {
        return $this->fetch_acl_by_user($this->uid);
    }

    public function get_new_group_options($uid)
    {
        $sql = "SELECT g.id AS value, g.name AS text FROM groups AS g
            LEFT JOIN users_groups AS ug ON ug.id_groups = g.id AND ug.id_users = :uid
            WHERE ug.id_users IS NULL
            ORDER BY g.name";
        return $this->db->query_db($sql, array(":uid" => $uid));
    }

    public function get_group_options()
    {
        $sql = "SELECT g.id AS value, g.name AS text FROM groups AS g
            ORDER BY g.name";
        return $this->db->query_db($sql);
    }

    public function get_did()
    {
        return $this->did;
    }

    public function is_duplicate_email($email)
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            WHERE u.email = :email";
        $res = $this->db->query_db_first($sql, array(":email" => $email));
        if($res) return true;
        return false;
    }

    public function block_user($uid)
    {
        return $this->db->update_by_ids("users", array("blocked" => 1),
            array("id" => $uid));
    }

    public function unblock_user($uid)
    {
        return $this->db->update_by_ids("users", array("blocked" => 0),
            array("id" => $uid));
    }

    public function delete_user($id)
    {
        return $this->db->remove_by_fk("users", "id", $id);
    }

    public function add_groups_to_user($uid, $groups)
    {
        $groups_db = array();
        foreach($groups as $group)
            $groups_db[] = array($uid, intval($group));
        return $this->db->insert_mult("users_groups",
            array("id_users", "id_groups"), $groups_db);
    }
    public function rm_group_from_user($uid, $gid)
    {
        return $this->db->remove_by_ids("users_groups", array(
            "id_users" => $uid,
            "id_groups" => $gid,
        ));
    }

    public function insert_new_user($email, $groups)
    {
        $uid = $this->db->insert("users", array("email" => $email));
        if(!$uid) return false;
        return $this->add_groups_to_user($uid, $groups);
    }
}
?>
