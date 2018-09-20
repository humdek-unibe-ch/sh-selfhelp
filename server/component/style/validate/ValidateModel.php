<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the validate component such
 * that the data can easily be displayed in the view of the component.
 */
class ValidateModel extends StyleModel
{
    private $uid;
    private $token;
    private $email;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services, $id, $uid, $token)
    {
        parent::__construct($services, $id);
        $this->uid = $uid;
        $this->token = $token;
        $this->email = $this->fetch_user_email($uid, $token);
    }

    /**
     * Get the user email address from the db given a user id and a token.
     *
     * @param int $uid
     *  The id of the user
     * @param string $token
     *  A valid token that is associated with the user id.
     * @retval string
     *  The email address of the user or null if the email could not be fetched.
     */
    private function fetch_user_email($uid, $token)
    {
        $sql = "SELECT email FROM users WHERE token = :token AND id = :uid";
        $email = $this->db->query_db_first($sql, array(
            ":token" => $token,
            ":uid" => $uid,
        ));
        if($email) return $email['email'];
        else return null;
    }

    /**
     * Performs the activation process of a user:
     *  - Set user name
     *  - Set user password
     *  - Set user gender
     *  - Remove validation token
     *
     * @param string $name
     *  The name of the user.
     * @param string $pw
     *  The password hash of the user password.
     * @param int $gender
     *  The gender type id
     * @retval bool
     *  True if the process was successful, false otherwise.
     */
    public function activate_user($name, $pw, $gender)
    {
        if(!$this->is_token_valid()) return false;
        return $this->db->update_by_ids("users", array(
            "name" => $name,
            "password" => $pw,
            "id_genders" => $gender,
            "token" => null,
        ), array(
            "id" => $this->uid,
        ));
    }

    /**
     * Checks whether the token is valid.
     *
     * @retval bool
     *  True if the token is valid, false otherwise.
     */
    public function is_token_valid()
    {
        return ($this->email !== null);
    }

    /**
     * Gets the user email.
     *
     * @retval string
     *  The email address of the activating user.
     */
    public function get_user_email()
    {
        return $this->email;
    }
}
?>
