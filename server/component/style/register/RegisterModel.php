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

    /**
     * Claim a validation code for a user and make it unusable for any other
     * registration.
     *
     * @param string $code
     *  The code string entered by the user.
     * @param int $uid
     *  The id of the user claiming the coed.
     * @retval bool
     *  True if the check was successful, false otherwise.
     */
    private function claim_validation_code($code, $uid)
    {
        return $this->db->update_by_ids('validation_codes',
            array('id_users' => $uid),
            array('code' => $code)
        );
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
            if ($uid && $this->claim_validation_code($code, $uid) !== false) {
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
            if($uid && $this->claim_validation_code($code, $uid) !== false)
            {
                return $uid;
            }
        }
        return false;
    }

    public function register_user_without_code($email){
        $user = $this->user_model->is_user_invited($email); 
        if ($user && $user['id'] > 0 ){
            // if the user already is created and we want to resend the activation link
            return $this->user_model->create_new_user($email, $user['code'], true);
        }
        $code = $this->user_model->generate_and_add_code();        
        if ($code === false){
            return false;
        }
        return $this->register_user($email, $code);
    }
}
?>
