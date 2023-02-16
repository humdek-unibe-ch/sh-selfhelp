<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * The transaction instance.
     */
    private $transaction;

    /**
     * If true the current url is stored as last url in the db.
     */
    private $store_url;

    /**
     * If true the user is redirected to the current url after login.
     */
    private $redirect;

    /**
     * The JobSheduler service instance to handle jobs scheduling and execution.
     */
    private $job_scheduler;

    /**
     * Start the session.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $transaction
     *  The transaction instance.
     * @param object $job_scheduler
     *  The job_scheduler instance.
     * @param bool $store_url
     *  If true the current url is stored as last url in the db.
     * @param bool $redirect
     *  If true the user is redirected to the current url after login.
     */
    public function __construct($db, $transaction, $job_scheduler, $store_url=false, $redirect=false)
    {
        $this->db = $db;
        $this->store_url = $store_url;
        $this->redirect = $redirect;
        $this->transaction = $transaction;
        $this->job_scheduler = $job_scheduler;
        $this->init_session();
    }

    /**
     * Initialise the php session.
     */
    private function init_session()
    {
        if(PROJECT_NAME !== "") {
            if (DEBUG) {
                session_name(PROJECT_NAME);
            } else {
                // session_name('__Secure-' . PROJECT_NAME);
                session_name(PROJECT_NAME);
            }
        }
        if (isset($_POST['mobile_web']) && $_POST['mobile_web']) {
            // enable cross side cookies for mobile preview, only for specific addresses       
            if (PHP_VERSION_ID < 70300) {
                session_set_cookie_params(6000, '/; samesite=' . 'None', $_SERVER['HTTP_HOST'], true);
            } else {
                session_set_cookie_params(
                    [
                        'secure' => true,
                        'samesite' => 'None'
                    ]
                );
            }
        } else if (!isset($_POST['mobile']) || !$_POST['mobile']) {
            // web calls only
            // if (PHP_VERSION_ID < 70300) {
            //     session_set_cookie_params(6000, '/; samesite=' . 'strict', null, true);
            // } else {
            //     session_set_cookie_params(
            //         [
            //             'secure' => true,
            //             'samesite' => 'strict'
            //         ]
            //     );
            // }
        }
        session_start();
        if(!isset($_SESSION['gender'])) $_SESSION['gender'] = MALE_GENDER_ID;
        if(!isset($_SESSION['user_gender'])) $_SESSION['user_gender'] = MALE_GENDER_ID;
        if(!isset($_SESSION['cms_gender'])) $_SESSION['cms_gender'] = MALE_GENDER_ID;
        if(!isset($_SESSION['language'])) $_SESSION['language'] = $this->db->get_default_language();
        if(!isset($_SESSION['user_language'])) $_SESSION['user_language'] = LANGUAGE;
        if(!isset($_SESSION['cms_language'])) $_SESSION['cms_language'] = 2;
        if(!isset($_SESSION['cms_edit_url'])) $_SESSION['cms_edit_url'] = array(
            "pid" => null,
            "sid" => null,
            "ssid" => null
        );
        $_SESSION['active_section_id'] = null;
        $_SESSION['project'] = $this->db->get_link_title("home");
        $_SESSION['user_language_locale'] = $this->db->fetch_language($_SESSION['user_language'])['locale'];
        if(!array_key_exists('target_url', $_SESSION))
            $_SESSION['target_url'] = null;
        if($this->redirect)
            $_SESSION['target_url'] = $_SERVER['REQUEST_URI'];
        if(!$this->is_logged_in())
        {
            $_SESSION['logged_in'] = false;
            $_SESSION['id_user'] = GUEST_USER_ID;
        }
        else
        {
            if($this->store_url)
                $this->update_last_url($_SESSION['id_user'], $_SESSION['target_url']);
            else if($this->redirect)
                $this->update_last_url($_SESSION['id_user'], null);
        }
        // session_write_close(); // otherwise it blocks request, check later if session is uesed naywhere else to assgin data.
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
        $ui = new UserInput($this->db, $this->transaction);
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
     * Search scheduled emails for the selected email and delete them
     * @param string $email
     * the email of the user
     * @param string transaction_by
     * the default one is by the system
     */
    private function  delete_mails_for_user($email, $transaction_by = transactionBy_by_system)
    {
        //delete all mails which are scheduled only for the selected user without any othe recipients
        $sql = "SELECT *
                FROM view_mailQueue
                WHERE id_jobStatus = :id_jobStatus AND recipient_emails = :user_email";
        $scheduledMails = $this->db->query_db($sql, array(
            ":id_jobStatus" => $this->db->get_lookup_id_by_code(scheduledJobsStatus, scheduledJobsStatus_queued),
            ":user_email" => $email
        ));
        foreach ($scheduledMails as $key => $mail) {
            $this->job_scheduler->delete_job($mail['id'], $transaction_by);    
        }

        // *********************************************************
        // remove the user email from group scheduled emails
        $sql = "SELECT *
                FROM view_mailQueue
                WHERE id_jobStatus = :id_jobStatus AND recipient_emails LIKE (:user_email)";
        $groupScheduledMails = $this->db->query_db($sql, array(
            ":id_jobStatus" => $this->db->get_lookup_id_by_code(scheduledJobsStatus, scheduledJobsStatus_queued),
            ":user_email" => '%' . $email . '%'
        ));
        foreach ($groupScheduledMails as $key => $mail) {
            $recipients = array_map('trim',
                explode(MAIL_SEPARATOR, $mail['recipient_emails'])
            );
            if (($key = array_search($email, $recipients)) !== false) {
                unset($recipients[$key]);
            }
            $recipients = implode(MAIL_SEPARATOR, $recipients);
            $this->job_scheduler->remove_email_from_queue_entry($mail['id_mailQueue'], $mail['id'], $transaction_by, $recipients, 'Remove emails for: ' . $email);
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
        $sql = "SELECT u.id, u.password, g.name AS gender, g.id AS id_gender, id_languages FROM users AS u
            LEFT JOIN genders AS g ON g.id = u.id_genders
            WHERE email = :email AND password IS NOT NULL AND blocked <> '1'";
        $user = $this->db->query_db_first($sql, array(':email' => $email));
        if($user && password_verify($password, $user['password']))
        {
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['gender'] = $user['id_gender'];
            $_SESSION['user_gender'] = $user['id_gender'];
            if(isset($user['id_languages'])){
                 $_SESSION['user_language'] = $user['id_languages'];
            }
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
     * @param string transaction_by
     * the default one is by the system
     * @retval bool
     *  True if the deleting process was successful, false otherwise.
     */
    public function delete_user($uid, $email, $transaction_by = transactionBy_by_system)
    {
        $sql = "SELECT email FROM users WHERE id = :id";
        $user = $this->db->query_db_first($sql,
            array(':id' => $uid));
        if($email != $user['email']) return false;
        $res = $this->db->remove_by_fk("users", "id", $uid);
        if ($res) {
            $this->delete_mails_for_user($email, $transaction_by);
            // check for confirmation email and if it is set send it to the user
            $email_templates = $this->db->fetch_page_info(SH_EMAIL);
            if ($email_templates[PF_EMAIL_DELETE_PROFILE] != '' && $email_templates[PF_EMAIL_DELETE_PROFILE_SUBJECT] != '' && $email_templates[PF_EMAIL_DELETE_PROFILE_EMAIL_ADDRESS] != '') {
                $mail = array(
                    "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
                    "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
                    "date_to_be_executed" => date('Y-m-d H:i:s', time()),
                    "from_email" => $email_templates[PF_EMAIL_DELETE_PROFILE_EMAIL_ADDRESS],
                    "from_name" => $email_templates[PF_EMAIL_DELETE_PROFILE_EMAIL_ADDRESS],
                    "reply_to" => $email_templates[PF_EMAIL_DELETE_PROFILE_EMAIL_ADDRESS],
                    "recipient_emails" => $email,
                    "subject" => $email_templates[PF_EMAIL_DELETE_PROFILE_SUBJECT],
                    "body" => $email_templates[PF_EMAIL_DELETE_PROFILE],
                    "is_html" => 1,
                    "description" => "Email Notification - Delete Profile"
                );
                $this->job_scheduler->add_and_execute_job($mail, $transaction_by);
                if ($email_templates[PF_EMAIL_DELETE_PROFILE_EMAIL_ADDRESS_NOTIFICATION_COPY] != '') {
                    // send a copy of the  notification email to this email
                    $mail['recipient_emails'] = $email_templates[PF_EMAIL_DELETE_PROFILE_EMAIL_ADDRESS_NOTIFICATION_COPY];
                    $mail['body'] = 'User profile with email: ' . $email . ' was deleted!';
                    $mail['subject'] = 'Notification for a deleted profile';
                    $this->job_scheduler->add_and_execute_job($mail, $transaction_by);
                }
            }
        }
        return $res;
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
