<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/../export/ExportModel.php";
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
    public function __construct($services, $uid=null, $did=null)
    {
        parent::__construct($services);
        $this->uid = $uid;
        $this->did = $did;
        $this->selected_user = null;
        if($uid != null) $this->selected_user = $this->get_user($this->fetch_user($uid));
    }

    /* Private Methods ********************************************************/    

    /**
     * Fetch the user data from the db.
     *
     * @param string $email
     *  The email of the user to fetch.
     * @retval array
     *  An array with the following keys:
     *   'id':          The id of the user.
     */
    private function fetch_user_by_email($email)
    {
        $sql = "SELECT id
            FROM users         
            WHERE email = :email and intern <> 1";
        $res = $this->db->query_db_first($sql, array(":email" => $email));
        return $res;
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
        $sql = "SELECT g.id, g.name AS title FROM `groups` AS g
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
        $acl_db = $this->acl->get_access_levels_db_user_all_pages($uid);
        foreach($acl_db as $page)
        {
            $acl[$page['keyword']] = array(
                "name" => $page['keyword'],
                "acl" => array(
                    "select" => $page['acl_select'] == 1,
                    "insert" => $page['acl_insert'] == 1,
                    "update" => $page['acl_update'] == 1,
                    "delete" => $page['acl_delete'] == 1,
                )                
            );
        }
        return $acl;
    }

    /**
     * Check if the code exist already in the database
     * @param string $code
     * 
     * @retval bool
     */
    private function code_exists($code){
        return count($this->db->select_by_fk('validation_codes', 'code', $code)) > 0;
    }

    /**
     * Generate random validation codes and store them to the database.
     *
     * @retval string
     *  A random string token.
     */
    private function generate_code()
    {
        $hash = bin2hex(openssl_random_pseudo_bytes(5));
        return base_convert($hash, 16, 36);
    }

    /**
     * Read the email content from the db.
     *
     * @param string $url
     *  The activation link that will be included into the mail content.
     * @param string $email_type
     *  The field name identifying which email will be loaded from the database.
     * @retval string
     *  The email content with replaced keywords.
     */
    private function get_content($url, $email_type)
    {
        $email_page = $this->db->fetch_page_info(SH_EMAIL);
        $content = $email_page[$email_type];
        $content = str_replace('@project', $_SESSION['project'], $content);
        $content = str_replace('@link', $url, $content);
        return $content;
    }

    /**
     * Get the email address which will be used to send an activation email
     * @return string
     * Return the email address
     */
    private function get_email_activate_from_email_address(){
        $email_activate_fields = $this->db->fetch_page_info(SH_EMAIL);
        return isset($email_activate_fields[PF_EMAIL_ACTIVATE_EMAIL_ADDRESS]) && $email_activate_fields[PF_EMAIL_ACTIVATE_EMAIL_ADDRESS] != '' ? $email_activate_fields[PF_EMAIL_ACTIVATE_EMAIL_ADDRESS] : "noreply@" . $_SERVER['HTTP_HOST'];

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
        if(!$this->acl->is_user_of_higer_level_than_user($_SESSION['id_user'], $uid))
            return false;
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
        return $this->acl->has_access_update(
            $_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userUpdate")
        );
    }

    /**
     * Clean all user data in the db.
     *
     * @param int $uid
     *  The id of the user from which the data will be cleaned.
     * @retval bool
     *  True on success, false on failure.
     */
    public function clean_user_data($uid)
    {
        if (!$this->acl->is_user_of_higer_level_than_user($_SESSION['id_user'], $uid))
            return false;

        try {
            $this->db->begin_transaction();
            $res = $this->db->remove_by_fk('user_activity', 'id_users', $uid);
            $res &= $this->db->remove_by_fk('user_input', 'id_users', $uid);

            // remove scheduled jobs
            $sql_scheduled_jobs = "SELECT id_scheduledJobs
                            FROM scheduledJobs sj 
                            INNER JOIN scheduledJobs_users u ON (u.id_scheduledJobs = sj.id)
                            WHERE u.id_users = :uid;";
            $res_scheduled_jobs = $this->db->query_db($sql_scheduled_jobs, array(
                ":uid" => $uid
            ));
            foreach ($res_scheduled_jobs as $key => $row) {
                $res &= $this->db->remove_by_ids("scheduledJobs", array(
                    "id" => $row['id_scheduledJobs']
                ));
            }
            $res &= $this->db->remove_by_fk('scheduledJobs_users', 'id_users', $uid);

            //remove external data
            foreach ($this->db->select_table('uploadTables') as $row => $table) {
                foreach ($this->user_input->get_data($table['id'], '', true, FORM_EXTERNAL, $uid) as $key => $row_record) {
                    $res &= $this->db->remove_by_ids("uploadRows", array(
                        "id" => $row_record['record_id'],
                        "id_uploadTables" => $table['id']
                    ));
                }
            }
            $this->db->commit();
            return $res;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Create a new user. This generates a new validation token, adds the user
     * to the DB, and sends an email to the user with the activation link.
     *
     * @param string $email
     *  The email address of the user to be added.
     * @param string $code
     *  A unique user code.
     * @param boolean $code_exists
     * does the code exist already in validation_codes, if exist dont insert it again
     * @retval int
     *  The id of the new user or false if the process failed.
     */
    public function create_new_user($email, $code=null, $code_exists = false)
    {
        $token = $this->login->create_token();
        $uid = $this->is_user_interested($email);
        if (!($uid > 0)) {
            // we check if the user is invited already
            $user_info = $this->is_user_invited($email);
            if ($user_info) {
                $uid = $user_info['id'];
            }
        }
        if (!($uid > 0)) {
            //check if the user is autocreated
            $uid = $this->is_user_auto_created($code);
        }
        if ($uid > 0) {
            // user is in status interested  or auto_created; change it to invited and assign the token for activation    
            $this->set_user_status($uid, $token, USER_STATUS_INVITED, $email);
        } else {
            // if the user is not already interested (in database), create a new one
            $uid = $this->insert_new_user($email, $token, 2);
        }
        if ($code_exists) {
            //this option is used for auto_created users
            $code = null;
        }
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
        $subject = $this->get_content($url, PF_EMAIL_ACTIVATE_SUBJECT);
        $from = $this->get_email_activate_from_email_address();
        $msg = $this->get_content($url, PF_EMAIL_ACTIVATE);
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', time()),
            "from_email" => $from,
            "from_name" => $from,
            "reply_to" => $from,
            "recipient_emails" => $email,
            "subject" => $subject,
            "body" => $msg,
            "is_html" => 1,
            "description" => "Registration Email"
        );
        $mail['id_users'][] = $uid;
        $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
        return $uid;
    }

    /**
     * insert user in the database with status auto_created and the email is code@selfhelp.psy.unibe.ch
     * @param string $email
     * @retval int
     * the user id
     */
    public function auto_create_user($email)
    {
        return $this->db->insert("users", array(
            "email" => $email,
            "id_status" => $this->db->query_db_first('SELECT id FROM userStatus WHERE `name` = "auto_created"')['id'],
        ));
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
        return $this->login->delete_user($uid, $this->selected_user['email'], transactionBy_by_user);
    }    

    /**
     * Fetch the list of non internal users.
     *
     * @retval array
     *  A list of db items where each item has the keys
     *   'id':          The id of the user.
     *   'email':       The email of the user.
     *   'name':        The name of the user.
     *   'last_login':  The date of the last login.
     *   'status':      Indicates the state of the user.
     *   'description': The state description of the user.
     *   'blocked':     Indicates whether the user is blocked or not.
     *   'groups':      Groups assigned to the user
     */
    public function fetch_users()
    {
        $sql = "SELECT *
                FROM view_users";
        return $this->db->query_db($sql);
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

        $sql = "INSERT IGNORE INTO validation_codes (code) VALUES('" . implode("'),('", array_unique($codes)) . "')";
        $dbh = $this->db->get_dbh();
        $insert = $dbh->prepare($sql);
        $insert->execute();
        return $insert->rowCount();
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
     * Get the number of consumed validation codes.
     *
     * @retval int
     *  The number of consumed validation codes.
     */
    public function get_code_count_consumed()
    {
        $sql = "SELECT COUNT(*) AS count FROM validation_codes
            WHERE id_users IS NOT NULL";
        $res = $this->db->query_db_first($sql);
        if($res)
            return intval($res['count']);
        else
            return 0;
    }

    /**
     * Return the necessary fields to render the validation code export buttons.
     * See ExportModel::get_export_view_fields() for more details.
     *
     * @retval array
     *  An array of fields as defined in ExportModel::get_export_view_fields().
     */
    public function get_export_button_fields()
    {
        $model = new ExportModel($this->services);
        return $model->get_export_view_fields("validation_codes");
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
        $sql = "SELECT g.id AS value, g.name AS text FROM `groups` AS g
            ORDER BY g.name";
        $groups_db = $this->db->query_db($sql);
        foreach ($groups_db as $group) {
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
        $sql = "SELECT g.id AS value, g.name AS text FROM `groups` AS g
            LEFT JOIN users_groups AS ug ON ug.id_groups = g.id AND ug.id_users = :uid
            WHERE ug.id_users IS NULL
            ORDER BY g.name";
        $groups_db = $this->db->query_db($sql, array(":uid" => $uid));
        foreach ($groups_db as $group) {
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
        $sql = "SELECT name FROM `groups` WHERE id = :gid";
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
     * Prepare a set of user data such that it is compatible with a list
     * component item.
     *
     * @param array $user
     *  The data returnd by a db query to the user db (see
     *  UserModel::fetch_users() and UserData::fetch_user()).
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':          The id of the user.
     *   'title':       The email address of the user.
     *   'name':        The name of the user.
     *   'url':         The url pointing to the user.
     *   'status':      The status of the user.
     *   'blocked':     A boolean indication whether the user is blocked or not.
     *   'description': The description of the user status.
     *   'last_login':  The date of the last login.
     */
    public function get_user($user)
    {
        if ($user) {
            $id = intval($user["id"]);
            $state = $user["status"];
            $desc = $user['description'];
            if ($user['blocked']) {
                $state = "<strong>[blocked]</strong> " . $state;
                $desc = "This user cannot login until the blocked status is reversed";
            }
            return array(
                "id" => $id,
                "title" => $user["email"],
                "email" => $user["email"],
                "name" => $user["name"],
                "code" => $user["code"],
                "user_activity" => $user["user_activity"],
                "last_login" => $user["last_login"],
                "status" => $state,
                "blocked" => ($user['blocked'] == '1') ? true : false,
                "description" => $desc,
                "groups" => $user ? (array_key_exists('groups', $user) ? $user['groups'] : '') : '',
                "url" => $this->get_link_url("userSelect", array("uid" => $id))
            );
        } else {
            array();
        }
    }

    /**
     * Get a list of users and prepare the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the keys as defined in
     *  UserModel::get_user().
     */
    public function get_users()
    {
        $res = array();
        foreach($this->fetch_users() as $user)
            $res[] = $this->get_user($user);
        return $res;
    }

    /**
     * Count all pages of type experiment as well as all navigation page
     * sections
     * 
     * @retval int returnt the page_count
     */
    public function calc_pages_for_progress(){
        $sql = "SELECT id_pages FROM sections_navigation";
        $nav_sections = $this->db->query_db($sql);
        $pc = count($nav_sections);

        $sql = "SELECT id, parent FROM pages WHERE id_type = 3";
        $pages = $this->db->query_db($sql);

        // do not count parent pages and parent navigation pages (those are not
        // reachable)
        foreach($pages as $parent_page)
        {
            $has_child = false;
            foreach($pages as $child_page)
                if($parent_page['id'] === $child_page['parent'] )
                {
                    $has_child = true;
                    break;
                }
            foreach($nav_sections as $nav_section)
                if($parent_page['id'] === $nav_section['id_pages'] )
                {
                    $has_child = true;
                    break;
                }
            if(!$has_child)
                $pc++;
        }
        return $pc;
    }

    /**
     *  and copmpare this count to all distinct URLs the user visited.
     *
     * @param int $id
     *  The id of the user to be counted
     * 
     * @param int $pc
     *  The Count all pages of type experiment as well as all navigation page
     *  sections
     *
     * @retval float
     *  A percentage between 0 and 1
     */
    public function get_user_progress($id, $pc)
    {
        $sql = "SELECT DISTINCT url FROM user_activity
            WHERE id_users = :uid AND id_type = 1";
        $activity = $this->db->query_db($sql, array(':uid' => $id));
        $ac = count($activity);
        if($pc === 0 || $ac > $pc)
            return 1;
        return $ac/$pc;
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
     * Add a new user to the DB.
     *
     * @param string $email
     *  The email of the user.
     * @param string $token
     *  The validation token of the new user.
     * @param int $id_status
     *  The initial status of the new user.
     * @retval int
     *  The id of the new user.
     */
    public function insert_new_user($email, $token, $id_status)
    {
        return $this->db->insert("users", array(
            "email" => $email,
            "token" => $token,
            "id_status" => $id_status,
        ));
    }

    /**
     * Check is a user already interested or invited but not activated
     *
     * @param string $email
     *  The email of the user.
     * @retval int
     *  The id of the new user.
     */
    public function is_user_interested($email)
    {
        $user_id = -1;
        $sql = "SELECT id
        FROM users 
        WHERE email = :email AND id_status = :user_status_interested";
        $res = $this->db->query_db_first($sql, array(
            ":email" => $email,
            ":user_status_interested" => USER_STATUS_INTERESTED
        ));
        if ($res) {
            $user_id = $res['id'];
        }
        return $user_id;
    }

    /**
     * Check is a user already interested or invited but not activated
     *
     * @param string $email
     *  The email of the user.
     * @retval array
     *  The id of the new user and the code.
     */
    public function is_user_invited($email)
    {
        $sql = "SELECT u.id, v.code
        FROM users u
        INNER JOIN validation_codes v ON (u.id = v.id_users) 
        WHERE email = :email AND id_status = :user_status_invited";
        $res = $this->db->query_db_first($sql, array(
            ":email" => $email,
            ":user_status_invited" => USER_STATUS_INVITED
        ));
        return $res;
    }

    /**
     * Check is a user already auto_created
     *
     * @param string $code
     *  The code of the user.
     * @retval int
     *  The id of the new user.
     */
    public function is_user_auto_created($code)
    {
        $user_id = -1;
        $sql = "SELECT id
        from users u
        inner join validation_codes vc on (vc.id_users = u.id)
        where vc.code = :code and id_status = :user_status_auto_created";
        $res = $this->db->query_db_first($sql, array(
            ":code" => $code,
            ":user_status_auto_created" => $this->db->query_db_first('SELECT id FROM userStatus WHERE `name` = "auto_created"')['id']));
        if($res){
            $user_id = $res['id'];
        }
        return $user_id;
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
        return $this->acl->is_user_of_higer_level_than_group($_SESSION['id_user'],
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
     * Set the user status and token
     *
     * @param int $uid
     *  The id of the user
     * @param int $token
     *  The token which will be used for account activation
     * @param int $status
     *  The new status
     * @param string $email
     * email to be updated if the user was auto_created
     * @retval bool
     *  True on success, false on failure.
     */
    public function set_user_status($uid, $token, $status, $email)
    {
        return $this->db->update_by_ids('users', 
            array(
                "token" => $token, 
                "id_status" => $status, 
                "email" => $email, 
                ),
            array("id" => $uid));
    }

    /**
     * Set the user token
     *
     * @param int $uid
     *  The id of the user
     * @param int $token
     *  The token which will be used for account activation
     * @retval bool
     *  True on success, false on failure.
     */
    public function set_user_token($uid, $token)
    {
        return $this->db->update_by_ids('users', 
            array(
                "token" => $token, 
                ),
            array("id" => $uid));
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
        if(!$this->acl->is_user_of_higer_level_than_user($_SESSION['id_user'], $uid))
            return false;
        return $this->db->update_by_ids("users", array("blocked" => 0),
            array("id" => $uid));
    }

    /**
     * Send activation email to the selected user
     *
     * @param string $email
     *  The email of the user
     * @retval bool
     *  True on success, false on failure.
     */
    public function send_activation_email($email)
    {
        try {
            if($email == 'admin' || $email == 'tpf' || $email == 'sysadmin'){
                // these accounts cannot send activation emails. If you want to reset them they shoudl contact us
                return false;
            }
            $token = $this->login->create_token();
            $uid = $this->fetch_user_by_email($email)['id'];
            $this->set_user_token($uid, $token);
            $url = $this->get_link_url("validate", array(
                "uid" => $uid,
                "token" => $token,
                "mode" => "activate",
            ));
            $url = "https://" . $_SERVER['HTTP_HOST'] . $url;
            $subject = $this->get_content($url, PF_EMAIL_ACTIVATE_SUBJECT);
            $from = $this->get_email_activate_from_email_address();
            $msg = $this->get_content($url, PF_EMAIL_ACTIVATE);
            $mail = array(
                "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
                "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
                "date_to_be_executed" => date('Y-m-d H:i:s', time()),
                "from_email" => $from,
                "from_name" => $from,
                "reply_to" => $from,
                "recipient_emails" => $email,
                "subject" => $subject,
                "body" => $msg,
                "is_html" => 1,
                "description" => "Activation Email"
            );
            $mail['id_users'][] = $uid;
            $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Generate validation code and insert it in the dabase
     * @retval string $code
     * return the code or false if it fails;
     */
    public function generate_and_add_code(){
        $code = $this->generate_code();
        while ($this->code_exists($code)){
            $code = $this->generate_code();
        }
        $sql = "INSERT IGNORE INTO validation_codes (code) VALUES('$code')";
        $dbh = $this->db->get_dbh();
        $insert = $dbh->prepare($sql);
        $insert->execute();
        return $insert->rowCount() > 0 ? $code : false;
    }

    /**
     * Fetch the user data from the db.
     *
     * @param int $uid
     *  The id of the user to fetch.
     * @retval array
     *  An array with the following keys:
     *   'id':          The id of the user.
     *   'email':       The email of the user.
     *   'name':        The name of the user.
     *   'last_login':  The date of the last login.
     *   'status':      The status of the user.
     *   'description': The description of status of the user.
     *   'blocked':     A boolean indication whether the user is blocked or not.
     *   'code':        The validation code of the user.
     *   'groups':      The groups in which the user belongs.
     */
    public function fetch_user($uid)
    {
        $sql = "SELECT *
            FROM view_users         
            WHERE id = :uid and intern <> 1";
        $res = $this->db->query_db_first($sql, array(":uid" => $uid));
        if($res)
            $res['id'] = $uid;
        return $res;
    }
}
?>
