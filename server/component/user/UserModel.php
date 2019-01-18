<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the user component such
 * that the data can easily be displayed in the view of the component.
 */
class UserModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /**
     * An array of user properties (see UserModel::fetch_user).
     */
    private $selected_user;

    /**
     * The active user id.
     */
    private $uid;

    /**
     * The user id to delete.
     */
    private $did;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $uid
     *  The active user id.
     * @param int $did
     *  The user id to delete or null if nothing ought to be deleted.
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

    /**
     * Fetch the user data from the db.
     *
     * @param int $uid
     *  The id of the user to fetch.
     * @retval array
     *  An array with the following keys:
     *   'id':      The id of the user.
     *   'email':   The email of the user.
     *   'active':  A boolean indicationg whether the user is active or not.
     *   'blocked': A boolean indication whether the user is blocked or not.
     */
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

    /**
     * Fetch the list of non internal users.
     *
     * @retval array
     *  A list of db items where each item has the keys
     *   'id':      The id of the user.
     *   'email':   The email of the user.
     */
    private function fetch_users()
    {
        $sql = "SELECT u.id, u.email FROM users AS u
            WHERE u.intern <> 1
            ORDER BY u.email";
        return $this->db->query_db($sql);
    }

    /**
     * Fetch the list of groups, associated to a user.
     *
     * @param int $uid
     *  The id of the user.
     * @retval array
     *  An array of group items where each item has the following keys:
     *   'id':      The id of the group.
     *   'title':   The name of the group.
     */
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

    /**
     * Fetch all access rights to pages of a specific user.
     *
     * @param int $uid
     *  The id of the user.
     * @retval array
     *  A list of key value pairs where the key is the page id and the value
     *  an array of booleans, indication access rights select, insert, update,
     *  delete.
     */
    private function fetch_acl_by_user($uid)
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
                    "select" => $this->acl->has_access_select($uid, $pid),
                    "insert" => $this->acl->has_access_insert($uid, $pid),
                    "update" => $this->acl->has_access_update($uid, $pid),
                    "delete" => $this->acl->has_access_delete($uid, $pid),
                )
            );
        }
        return $acl;
    }

    /**
     * Generate random validation codes and store them to the database.
     *
     * @param int $count
     *  The number of codes to generate.
     * @retval bool
     *  True on success, false on failure.
     */
    private function generate_code()
    {
        return bin2hex(openssl_random_pseudo_bytes(4));
    }

    /* Public Methods *********************************************************/

    /**
     * Add groups to the group list of a user.
     *
     * @param int $uid
     *  The id of the user where groups will be added.
     * @param array $groups
     *  An array of ids where an id correspond to the id of a group.
     * @retval bool
     *  True on success, false on failure.
     */
    public function add_groups_to_user($uid, $groups)
    {
        $groups_db = array();
        foreach($groups as $group)
            $groups_db[] = array($uid, intval($group));
        return $this->db->insert_mult("users_groups",
            array("id_users", "id_groups"), $groups_db);
    }

    /**
     * Set the block flag of a user in the db.
     *
     * @param int $uid
     *  The id of the user to be blocked.
     * @retval bool
     *  True on success, false on failure.
     */
    public function block_user($uid)
    {
        return $this->db->update_by_ids("users", array("blocked" => 1),
            array("id" => $uid));
    }

    /**
     * Checks whether the current user is allowed to delete users.
     *
     * @retval bool
     *  True if the current user can delete users, false otherwise.
     */
    public function can_delete_user()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userDelete"));
    }

    /**
     * Checks whether the current user is allowed to create new users.
     *
     * @retval bool
     *  True if the current user can create new users, false otherwise.
     */
    public function can_create_new_user()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userInsert"));
    }

    /**
     * Checks whether the current user is allowed to modify users.
     *
     * @retval bool
     *  True if the current user can modify users, false otherwise.
     */
    public function can_modify_user()
    {
        return $this->acl->has_access_update($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userUpdate"));
    }

    /**
     * Delete a user from the database.
     *
     * @param int $uid
     *  The id of the user to be deleted.
     * @retval bool
     *  True on success, false on failure.
     */
    public function delete_user($uid)
    {
        return $this->db->remove_by_fk("users", "id", $uid);
    }

    /**
     * Generate random validation codes and store them to the database.
     *
     * @param int $count
     *  The number of codes to generate.
     * @retval int
     *  The number of generated codes.
     */
    public function generate_codes($count)
    {
        $codes = [];
        for($i = 0; $i < $count; $i++)
            $codes[] = $this->generate_code();

        $sql = "INSERT INTO validation_codes (code) VALUES('" . implode("'),('", array_unique($codes)) . "')";
        return $this->db->execute_db($sql);
    }

    /**
     * Get the ACL info of the selected user.
     *
     * @retval array
     *  See UserModel::fetch_acl_by_user.
     */
    public function get_acl_selected_user()
    {
        return $this->fetch_acl_by_user($this->uid);
    }

    /**
     * Get the number of validation codes.
     *
     * @retval int
     *  The number of validation codes.
     */
    public function get_code_count()
    {
        $sql = "SELECT COUNT(*) AS count FROM validation_codes";
        $res = $this->db->query_db_first($sql);
        if($res)
            return intval($res['count']);
        else
            return 0;
    }

    /**
     * Get the id of the group to delete.
     *
     * @retval int
     *  The id of a group to be deleted (passed by GET params)
     */
    public function get_did()
    {
        return $this->did;
    }

    /**
     * Return a list of all group items prepared in a form such that it can be
     * passed to a select form.
     *
     * @retval array
     *  An array of group items where each item has the following keys:
     *   'value':   The id of the group.
     *   'text':    The name of the group.
     */
    public function get_group_options()
    {
        $groups = array();
        $sql = "SELECT g.id AS value, g.name AS text FROM groups AS g
            ORDER BY g.name";
        $groups_db = $this->db->query_db($sql);
        foreach($groups_db as $group)
        {
            if($this->is_group_allowed(intval($group['value'])))
                $groups[] = $group;
        }
        return $groups;
    }

    /**
     * Return a list of group items the user is not already assigned to. The
     * list is prepared such that it can be passed to a select form.
     *
     * @retval array
     *  An array of group items where each item has the following keys:
     *   'value':   The id of the group.
     *   'text':    The name of the group.
     */
    public function get_new_group_options($uid)
    {
        $groups = array();
        $sql = "SELECT g.id AS value, g.name AS text FROM groups AS g
            LEFT JOIN users_groups AS ug ON ug.id_groups = g.id AND ug.id_users = :uid
            WHERE ug.id_users IS NULL
            ORDER BY g.name";
        $groups_db = $this->db->query_db($sql, array(":uid" => $uid));
        foreach($groups_db as $group)
        {
            if($this->is_group_allowed(intval($group['value'])))
                $groups[] = $group;
        }
        return $groups;
    }

    /**
     * Get the name of the group to be removed.
     *
     * @retval string
     *  The name of the group to be removed. The id specifying the group to be
     *  removed is passed via a GET parameter.
     */
    public function get_rm_group_name()
    {
        if($this->did == null) return "";
        $sql = "SELECT name FROM groups WHERE id = :gid";
        $res = $this->db->query_db_first($sql, array(":gid" => $this->did));
        return $res["name"];
    }

    /**
     * Return the properties of the curren user.
     *
     * @retval array
     *  An array of user properties (see UserModel::fetch_user).
     */
    public function get_selected_user()
    {
        return $this->selected_user;
    }

    /**
     * Return the user groups.
     *
     * @retval array
     *  An array of user groups (see UserModel::fetch_user_groups).
     */
    public function get_selected_user_groups()
    {
        return $this->fetch_user_groups($this->uid);
    }

    /**
     * Get a list of users and prepare the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':      The id of the user.
     *   'title':   The email address of the user.
     *   'url':     The url pointing to the user.
     */
    public function get_users()
    {
        $res = array();
        foreach($this->fetch_users() as $user)
        {
            $id = intval($user["id"]);
            $res[] = array(
                "id" => $id,
                "title" => $user["email"],
                "url" => $this->get_link_url("userSelect", array("uid" => $id))
            );
        }
        return $res;
    }

    /**
     * Get the id of the selected user.
     *
     * @retval int
     *  The id of the selected user.
     */
    public function get_uid()
    {
        return $this->uid;
    }

    /**
     * Insert a new user to the DB.
     *
     * @param string $email
     *  The email address of the user to be added.
     * @param string $code
     *  A unique user code.
     * @retval int
     *  The id of the new user or false if the process failed.
     */
    public function insert_new_user($email, $code=null)
    {
        $token = $this->login->create_token();
        $uid = $this->db->insert("users", array(
            "email" => $email,
            "token" => $token,
        ));
        $code_res = true;
        if($code !== null)
            $code_res = $this->db->insert("validation_codes", array(
                "code" => $code,
                "id_users" => $uid,
            ));
        if(!$uid || !$code_res) return null;
        $url = $this->get_link_url("validate", array(
            "uid" => $uid,
            "token" => $token,
            "mode" => "activate",
        ));
        $url = "https://" . $_SERVER['HTTP_HOST'] . $url;
        $subject = $_SESSION['project'] . " Email Verification";
        $from = "noreply@" . $_SERVER['HTTP_HOST'];
        $this->login->email_send($from, $email, $subject,
            $this->login->email_get_content($url, 'email_activate'));
        return $uid;
    }

    /**
     * Checks whether a group can be added by the current user.
     *
     * @retval bool
     *  Returns true if the current user has at least the same access level as
     *  the group for each page. Otherwise false is returned.
     */
    public function is_group_allowed($id_group)
    {
        return $this->acl->is_user_of_higer_level($_SESSION['id_user'],
                $id_group);
    }

    /**
     * Remove a group from the group list of a user.
     *
     * @param int $uid
     *  The id of the user where groups will be added.
     * @param int $gid
     *  The id of the group to be removed.
     * @retval bool
     *  True on success, false on failure.
     */
    public function rm_group_from_user($uid, $gid)
    {
        return $this->db->remove_by_ids("users_groups", array(
            "id_users" => $uid,
            "id_groups" => $gid,
        ));
    }

    /**
     * Set the id of the user to be deleted to null.
     */
    public function reset_did()
    {
        $this->did = null;
    }

    /**
     * Unset the block flag of a user in the db.
     *
     * @param int $uid
     *  The id of the user to be unblocked.
     * @retval bool
     *  True on success, false on failure.
     */
    public function unblock_user($uid)
    {
        return $this->db->update_by_ids("users", array("blocked" => 0),
            array("id" => $uid));
    }
}
?>
