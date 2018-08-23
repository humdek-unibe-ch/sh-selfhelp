<?php
require_once __DIR__ . "/../../BaseModel.php";
/**
 * This class is used to prepare all data related to the user component such
 * that the data can easily be displayed in the view of the component.
 */
class UserModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $users;
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
        $this->users = array();
        $this->set_users($this->fetch_users());
        $this->selected_user_groups = $this->fetch_user_groups($uid);
    }

    /* Private Methods ********************************************************/

    private function fetch_user($uid)
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            WHERE u.id = :uid";
        return $this->db->query_db_first($sql, array("uid" => $uid));
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

    private function set_users($users)
    {
        foreach($users as $user)
        {
            $id = intval($user["id"]);
            $this->users[] = array(
                "id" => $id,
                "title" => $user["email"],
                "url" => $this->get_link_url("user", array("id" => $id))
            );
        }
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
        return $this->users;
    }

    public function get_acl_selected_user()
    {
        return $this->fetch_acl_by_user($this->uid);
    }
}
?>
