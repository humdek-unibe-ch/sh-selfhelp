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
     * Start the session.
     */
    public function __construct($db)
    {
        $this->db = $db;
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
        if(!isset($_SESSION['cms_gender'])) $_SESSION['cms_gender'] = "male";
        if(!isset($_SESSION['language'])) $_SESSION['language'] = LANGUAGE;
        if(!isset($_SESSION['cms_language'])) $_SESSION['cms_language'] = LANGUAGE;
        $_SESSION['active_section_id'] = null;
        $_SESSION['project'] = $this->db->get_link_title("home");
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
            WHERE email = :email AND password IS NOT NULL AND blocked <> '1'";
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
     * Read the email content from the db.
     *
     * @param string $url
     *  The activation link that will be included into the mail content.
     * @param string $email_type
     *  The field name identifying which email will be loaded from the database.
     * @retval string
     *  The email content with replaced keywords.
     */
    public function email_get_content($url, $email_type)
    {
        $content = "";
        $sql = "SELECT content FROM pages_fields_translation AS pft
            LEFT JOIN pages AS p ON p.id = pft.id_pages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            WHERE p.keyword = 'email' AND f.name = :field
            AND l.locale = :lang";
        $res = $this->db->query_db_first($sql, array(
            ':lang' => $_SESSION['language'],
            ':field' => $email_type,
        ));
        if($res)
        {
            $content = $res['content'];
            $content = str_replace('@project', $_SESSION['project'], $content);
            $content = str_replace('@link', $url, $content);
        }
        return $content;
    }

    /**
     * Send activation email to new user.
     *
     * @param string $from
     *  The source of the email address.
     * @param string $to
     *  The email address of the new user.
     * @param string $subject
     *  The subject of the email.
     * @param string $msg
     *  The email message.
     * @retval bool
     *  True on success, false otherwise.
     */
    public function email_send($from, $to, $subject, $msg)
    {
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=utf-8";
        $headers[] = "From: {$from}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "X-Mailer: PHP/".phpversion();

        return mail($to, $subject, $msg , implode("\r\n", $headers));
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
