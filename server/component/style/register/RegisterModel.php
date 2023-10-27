<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../../user/UserModel.php";
/**
 * This class is used to prepare all data related to the register component such
 * that the data can easily be displayed in the view of the component.
 */
class RegisterModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The instance of the user model from the user component.
     */
    private $user_model = null;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of the register component instance.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params=array(), $id_page=-1, $entry_record=array())
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);

        $this->user_model = new UserModel($services, GUEST_USER_ID);
    }

    /* Private Methods ********************************************************/

    /**
     * Checks whether the specified validation code is valid.
     *
     * @param string $code
     *  The validation code.
     * @retval bool
     *  True if the check was successful, false otherwise.
     */
    private function check_validation_code($code)
    {
        $sql = "SELECT vc.* 
                FROM validation_codes vc
                LEFT JOIN users u ON (vc.id_users = u.id)
                LEFT JOIN userStatus us ON (u.id_status = us.id)
                WHERE (id_users is NULL || us.name = 'auto_created') AND code = :code";
        $res = $this->db->query_db_first($sql, array(':code' => $code));
        if($res) return true;
        else return false;
    }    

    /* Private Methods ********************************************************/

    /**
     * Get the group it there is one assigned to that validation code
     *
     * @param string $code
     *  The validation code.
     * @retval bool
     *  True if the check was successful, false otherwise.
     */
    private function get_group_from_code($code)
    {
        $sql = "SELECT id_groups FROM codes_groups
            WHERE code = :code";
        return $this->db->query_db($sql, array(':code' => $code));
    }

    /* Public Methods *********************************************************/

    /**
     * Register a user by inserting a new user entry into the database. This is
     * only possible if a valid validation_code is provided.
     * 
     * Assing group to the user based on the validation code. If there is no grroup for the code assing to the default one: SUBJECT_GROUP_ID
     *
     * @param string $email
     *  The email address of the user.
     * @param string $code
     *  The code string entered by the user.
     * @param bool $skip_group
     * if true no default group is assign to the user
     * @retval mixed
     *  The user id the new user if the registration was successful,
     *  false otherwise.
     */
    public function register_user($email, $code, $skip_group = false)
    {
        if($this->check_validation_code($code)) {
            $group = $this->get_group_from_code($code);
            $groupId = explode(',', $this->get_db_field("group", SUBJECT_GROUP_ID)); // assign predefined group in the controller i not set the default group `subject`
            if (!empty($group)) {
                $groupId = array_column($group, 'id_groups'); //if there is a group assigned to that validation code, assign it or them
            }
            $uid = $this->user_model->create_new_user($email, $code, true);
            if ($uid && $this->user_model->claim_validation_code($code, $uid) !== false) {
                if (!$skip_group) {
                    $this->user_model->add_groups_to_user($uid, $groupId);
                }
                return $uid;
            }
        } else if ($this->user_model->is_user_invited($email)['id'] > 0) {
            // if the user already is created and we want to resend the activation link
            return $this->user_model->create_new_user($email, $code, true);
        }
        return false;
    }

    /**
     * Register auto created user from callback
     * @param string $email 
     * email adress
     * @param string $code
     * the vlaidation code
     */
    public function register_user_from_callback($email, $code)
    {
        if($this->check_validation_code($code))
        {
            $uid = $this->user_model->auto_create_user($email);
            if($uid && $this->user_model->claim_validation_code($code, $uid) !== false)
            {
                return $uid;
            }
        }
        return false;
    }

    /**
     * Register user without a code
     * @param string @email
     * The user email
     * @return mixed
     * User id or false
     */
    public function register_user_without_code($email)
    {
        $user = $this->user_model->is_user_invited($email);
        if ($user && $user['id'] > 0) {
            // if the user already is created and we want to resend the activation link
            return $this->user_model->create_new_user($email, $user['code'], true);
        }
        $code = $this->user_model->generate_and_add_code();
        if ($code === false) {
            return false;
        }
        return $this->register_user($email, $code);
    }

    /**
     * Check if the settings are for anonymous_users
     * @return bool
     * Return the result
     */
    public function is_anonymous_users()
    {
        return $this->db->is_anonymous_users();
    }

    /**
     * Get the security questions and return them in array for a select style
     * @return array
     * Return the security questions in array to be used in a select
     */
    public function get_security_questions()
    {
        $arr = array();
        $security_questions = $this->db->fetch_page_info(SH_SECURITY_QUESTIONS);
        foreach ($security_questions as $key => $value) {
            if (strpos($key, 'security_question_') !== false) {
                array_push($arr, array("value" => $key, "text" => $value));
            }
        }
        return $arr;
    }

    /**
     * Register an anonymous user by inserting a new user entry into the database. This is
     * only possible if a valid validation_code is provided.
     * 
     * Assign group to the user based on the validation code. If there is no group for the code assign to the default one: SUBJECT_GROUP_ID
     *
     * @param string $code
     *  The code string entered by the user.
     * @return mixed
     *  The user id the new user if the registration was successful,
     *  false otherwise.
     */
    public function register_anonymous_user($code, $security_questions)
    {
        if ($this->check_validation_code($code)) {
            $group = $this->get_group_from_code($code);
            $groups = explode(',', $this->get_db_field("group", SUBJECT_GROUP_ID)); // assign predefined group in the controller i not set the default group `subject`
            if (!empty($group)) {
                $groups = array_column($group, 'id_groups'); //if there is a group assigned to that validation code, assign it to them
            }
            return $this->user_model->create_new_anonymous_user($code, $groups, $security_questions);
        }
        return false;
    }
}
?>
