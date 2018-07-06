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
        session_start();
        $this->db = $db;
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
        $sql = "SELECT id, password FROM users WHERE email = :email";
        $user = $this->db->query_db_first($sql, array(':email' => $email));
        if(($user != false) && password_verify($password, $user['password']))
        {
            $_SESSION['id_user'] = $user['id'];
            return true;
        }
        else
        {
            $this->logout();
            return false;
        }
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
        return isset($_SESSION['id_user']);
    }

    /**
     * Logout the user by removing all session variables and destroying the
     * session.
     */
    public function logout()
    {
        $_SESSION = array();
        session_destroy();
    }
}
?>
