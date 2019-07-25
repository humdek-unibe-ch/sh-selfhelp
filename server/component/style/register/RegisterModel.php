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
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);

        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields);
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
        $sql = "SELECT * FROM validation_codes
            WHERE id_users is NULL AND code = :code";
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
        return (bool)$this->db->update_by_ids('validation_codes',
            array('id_users' => $uid),
            array('code' => $code)
        );
    }

    /* Public Methods *********************************************************/

    /**
     * Register a user by inserting a new user entry into the database. This is
     * only possible if a valid validation_code is provided.
     *
     * @param string $email
     *  The email address of the user.
     * @param string $code
     *  The code string entered by the user.
     * @retval mixed
     *  The user id the new user if the registration was successful,
     *  false otherwise.
     */
    public function register_user($email, $code)
    {
        if($this->check_validation_code($code))
        {
            $uid = $this->user_model->create_new_user($email);
            if($uid && $this->claim_validation_code($code, $uid))
            {
                $this->user_model->add_groups_to_user($uid,
                    array(SUBJECT_GROUP_ID));
                return $uid;
            }
        }
        return false;
    }
}
?>
