<?php
/**
 * This class allows to check wheter a login is valid.
 */
class Login
{
    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * If true the current url is stored as session attribute.
     */
    private $store_url;

    /**
     * Start the session.
     */
    public function __construct($db, $store_url=false)
    {
        $this->db = $db;
        $this->store_url = $store_url;
        $this->init_session();
    }

    /**
     * Initialise the php session.
     */
    private function init_session()
    {
        session_name(PROJECT_NAME);
        session_start();
        if(!isset($_SESSION['gender'])) $_SESSION['gender'] = "male";
        if(!isset($_SESSION['user_gender'])) $_SESSION['user_gender'] = "male";
        if(!isset($_SESSION['cms_gender'])) $_SESSION['cms_gender'] = "male";
        if(!isset($_SESSION['language'])) $_SESSION['language'] = LANGUAGE;
        if(!isset($_SESSION['user_language'])) $_SESSION['user_language'] = LANGUAGE;
        if(!isset($_SESSION['cms_language'])) $_SESSION['cms_language'] = LANGUAGE;
        if(!isset($_SESSION['cms_edit_url'])) $_SESSION['cms_edit_url'] = array(
            "pid" => null,
            "sid" => null,
            "ssid" => null
        );
        $_SESSION['active_section_id'] = null;
        $_SESSION['project'] = $this->db->get_link_title("home");
        if(!array_key_exists('target_url', $_SESSION))
            $_SESSION['target_url'] = null;
        if($this->store_url)
            $_SESSION['target_url'] = $_SERVER['REQUEST_URI'];
        if(!$this->is_logged_in())
        {
            $_SESSION['logged_in'] = false;
            $_SESSION['id_user'] = GUEST_USER_ID;
        }
        else if($this->store_url)
            $this->update_last_url($_SESSION['id_user'], $_SESSION['target_url']);
    }

    /**
     * Update the last visited url of the active user.
     *
     * @param $id
     *  The user id
     * @param $url
     *  The target url
     */
    private function update_last_url($id, $url)
    {
        $this->db->update_by_ids('users',
            array('last_url' => $url), array('id' => $id));
    }

    /**
     * Update the timestamp of the last login.
     *
     * @param int $id
     *  The user id
     * @retval int
     *  The number of affected rows or false on failure.
     */
    private function update_timestamp($id)
    {
        $ui = new UserInput($this->db);
        $val = 0;
        $field = $ui->get_input_fields(array(
            'page' => 'profile',
            'id_user' => $id,
            'form_name' => 'notification',
            'field_name' => 'reminder',
        ));
        if(count($field) === 0 || $field[0]['value'] !== "")
            $val = 1;
        $sql = "UPDATE users SET last_login = now(), is_reminded = :field
            WHERE id = :id";
        return $this->db->execute_update_db($sql, array(
            ':id' => $id,
            ':field' => $val,
        ));
    }

    /**
     * Check login credentials with the db and set the session variable if
     * successful. If the check fails, the session variable is destroyed.
     *
     * @param string $email
     *  The email address of the user.
     * @param string $password
     *  The password string entered by the user.
     * @retval bool
     *  true if the check was successful, false otherwise.
     */
    public function check_credentials($email, $password)
    {
        $sql = "SELECT u.id, u.password, g.name AS gender FROM users AS u
            LEFT JOIN genders AS g ON g.id = u.id_genders
            WHERE email = :email AND password IS NOT NULL AND blocked <> '1'";
        $user = $this->db->query_db_first($sql, array(':email' => $email));
        if($user && password_verify($password, $user['password']))
        {
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['gender'] = $user['gender'];
            $_SESSION['user_gender'] = $user['gender'];
            $this->update_timestamp($user['id']);
            return true;
        }
        else
        {
            $_SESSION['logged_in'] = false;
            $_SESSION['id_user'] = GUEST_USER_ID;
            return false;
        }
    }

    /**
     * Change the password of the active user.
     *
     * @param string $password
     *  The new password.
     * @retval bool
     *  True if the change was successful, false otherwise
     */
    public function change_password($password)
    {
        return $this->db->update_by_ids(
            "users",
            array("password" => password_hash($password, PASSWORD_DEFAULT)),
            array("id" => $_SESSION["id_user"])
        );

    }

    /**
     * Create a random token string that can be used for verification.
     *
     * @retval string
     *  A random string.
     */
    public function create_token()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * Delete the user if the given email address matches with the email
     * address stored in the database.
     *
     * @param int $uid
     *  The user id.
     * @param string $email
     *  The user email address.
     * @retval bool
     *  True if the deleting process was successful, false otherwise.
     */
    public function delete_user($uid, $email)
    {
        $sql = "SELECT email FROM users WHERE id = :id";
        $user = $this->db->query_db_first($sql,
            array(':id' => $uid));
        if($email != $user['email']) return false;
        return $this->db->remove_by_fk("users", "id", $uid);
    }

    /**
     * Get the target URL to redirec after login. This is either
     *  1. a target url specified by a link
     *  2. the last used url by the user
     *  3. the home url
     *
     * @retval string
     *  The target URL.
     */
    public function get_target_url($default_url)
    {
        // if target_url is set use it
        if($_SESSION['target_url'] !== null)
            return $_SESSION['target_url'];

        $url = $_SESSION['target_url'] ?? $default_url;

        // if user is not logged in use target_url or fallback
        if(!$this->is_logged_in())
            return $url;

        $sql = "SELECT last_url FROM users WHERE id = :uid";
        $url_db = $this->db->query_db_first($sql,
            array(':uid' => $_SESSION['id_user']));

        // if last_url is set n the DB use it
        if($url_db['last_url'] != "")
            $url = $url_db['last_url'];

        return $url;
    }

    /**
     * Check whether the user is logged in by ckecking for a user id in the
     * session variable.
     *
     * @retval bool
     *  true if the user is logged in, false otherwise.
     */
    public function is_logged_in()
    {
        return (isset($_SESSION['logged_in']) && $_SESSION['logged_in']);
    }

    /**
     * Logout the user by removing all session variables and destroying the
     * session.
     */
    public function logout()
    {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->init_session();
    }
}
?>
