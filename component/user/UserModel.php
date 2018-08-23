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
    private $selected_user_groups;
    private $uid;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $uid)
    {
        parent::__construct($services);
        $this->uid = $uid;
        $this->selected_user = null;
        if($uid != null) $this->selected_user = $this->fetch_user($uid);
        $this->selected_user_groups = $this->fetch_user_groups($uid);
    }

    /* Private Methods ********************************************************/

    private function fetch_user($uid)
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            WHERE u.id = :uid";
        $res = $this->db->query_db_first($sql, array(":uid" => $uid));
        $res['id'] = $uid;
        return $res;
    }

    private function fetch_users()
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            ORDER BY u.email";
        return $this->db->query_db($sql);
    }

    private function fetch_user_groups($uid)
    {
        $sql = "SELECT g.id, g.name AS title FROM groups AS g
            LEFT JOIN users_groups AS ug ON ug.id_groups = g.id
            WHERE ug.id_users = :uid";
        return $this->db->query_db($sql, array("uid" => $uid));
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

    public function get_selected_user()
    {
        return $this->selected_user;
    }

    public function get_selected_user_groups()
    {
        return $this->selected_user_groups;
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
                "url" => $this->get_link_url("user", array("id" => $id))
            );
        }
        return $res;
    }

    public function get_acl_selected_user()
    {
        return $this->fetch_acl_by_user($this->uid);
    }

    public function get_group_options()
    {
        $sql = "SELECT g.id AS value, g.name AS text FROM groups AS g
            ORDER BY g.name";
        return $this->db->query_db($sql);
    }

    public function is_duplicate_email($email)
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            WHERE u.email = :email";
        $res = $this->db->query_db_first($sql, array(":email" => $email));
        if($res) return true;
        return false;
    }

    public function insert_new_user($email, $groups)
    {
        $uid = $this->db->insert("users", array("email" => $email));
        if(!$uid) return false;
        $groups_db = array();
        foreach($groups as $group)
            $groups_db[] = array($uid, intval($group));
        return $this->db->insert_mult("users_groups",
            array("id_users", "id_groups"), $groups_db);
    }
}
?>
