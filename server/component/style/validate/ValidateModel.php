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
        $this->email = $this->fetch_user_email();
    }

    private function fetch_user_email()
    {
        $sql = "SELECT email FROM users WHERE token = :token AND id = :uid";
        $email = $this->db->query_db_first($sql, array(
            ":token" => $this->token,
            ":uid" => $this->uid,
        ));
        if($email) return $email['email'];
        else return null;
    }

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

    public function is_token_valid()
    {
        return ($this->email !== null);
    }

    public function get_user_email()
    {
        return $this->email;
    }
}
?>
