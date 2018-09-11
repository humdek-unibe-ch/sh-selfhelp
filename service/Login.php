<?php
/**
 * This class allows to check wheter a login is valid.
 */
class Login
{
    private $db;

    /**
     * Start the session.
     */
    public function __construct($db)
    {
        $this->init_session();
        $this->db = $db;
    }

    /**
     * Initialise the php session.
     */
    private function init_session()
    {
        session_start();
        $_SESSION['language'] = "de-CH";
        if(!isset($_SESSION['gender'])) $_SESSION['gender'] = "male";
        if(!$this->is_logged_in())
        {
            $_SESSION['logged_in'] = false;
            $_SESSION['id_user'] = GUEST_USER_ID;
        }
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
            WHERE email = :email AND password IS NOT NULL";
        $user = $this->db->query_db_first($sql, array(':email' => $email));
        if($user && password_verify($password, $user['password']))
        {
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['gender'] = $user['gender'];
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
