<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the validate component such
 * that the data can easily be displayed in the view of the component.
 */
class ValidateModel extends StyleModel
{
    /**
     * The id of the user to validate.
     */
    private $uid;

    /**
     * The validation token of the user to validate.
     */
    private $token;

    /**
     * The email address of the user to validate.
     */
    private $email;

    /**
     * The page keyword, if set it after successful validation the user is redirected to that page
     */
    private $redirect_page_keyword;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section with the validate style.
     * @param int $uid
     *  The id of the user to validate.
     * @param string $token
     *  The validation token of the user to validate.
     */
    public function __construct($services, $id, $uid, $token)
    {
        parent::__construct($services, $id);
        $this->uid = $uid;
        $this->token = $token;
        $this->email = null;
        $this->name = null;
        $this->gender = null;
        $page_keyword_id = $this->get_db_field("page_keyword");
        $this->redirect_page_keyword = $this->db->fetch_page_keyword_by_id($page_keyword_id);
        $data = $this->fetch_user_data($uid, $token);
        if($data)
        {
            $this->email = $data['email'];
            $this->name = $data['name'];
            $this->gender = $data['gender'];
        }
    }

    /**
     * Get the user data from the db given a user id and a token.
     *
     * @param int $uid
     *  The id of the user
     * @param string $token
     *  A valid token that is associated with the user id.
     * @retval array
     *  The data of the user or null if the email could not be fetched.An array
     *  following data keys is reurned:
     *   - email    The email address of the user.
     *   - name     The user name.
     *   - gender   The gender of the user.
     */
    private function fetch_user_data($uid, $token)
    {
        $sql = "SELECT u.email, u.name, g.name AS gender FROM users AS u
            LEFT JOIN genders AS g ON g.id = u.id_genders
            WHERE u.token = :token AND u.id = :uid";
        $data = $this->db->query_db_first($sql, array(
            ":token" => $token,
            ":uid" => $uid,
        ));
        if($data) return $data;
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
            "id_status" => 3
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

    /**
     * Gets the user name.
     *
     * @retval string
     *  The name of the activating user.
     */
    public function get_user_name()
    {
        return $this->name;
    }

    /**
     * Gets the user gender.
     *
     * @retval string
     *  The gender of the activating user.
     */
    public function get_user_gender()
    {
        return $this->gender;
    }

    /**
     * Get the page_keyword
     * @return string 
     * Returns the page keyword
     */
    public function get_redirect_page_keyword(){
        return $this->redirect_page_keyword;
    }

}
?>
