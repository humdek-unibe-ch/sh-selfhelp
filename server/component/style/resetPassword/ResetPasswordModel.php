<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../emailFormBase/EmailFormBaseModel.php";
/**
 * This class is used to prepare all data related to the ResetPasswordComponent
 * such that the data can easily be displayed in the view of the component.
 */
class ResetPasswordModel extends EmailFormBaseModel
{
    /* Private Properties******************************************************/

    /**
     * Reset user name
     */
    private $reset_user_name = false;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all component related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section associated to the profile page.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
    }

    /* Private Methods ********************************************************/

    /**
     * Create a new activation token for a user and send an email with the new
     * token to the user. This will allow the user to set a new password.
     *
     * @return bool
     *  True on success, false otherwise.
     */
    private function user_set_new_token($email)
    {
        $sql = "SELECT id FROM users WHERE email = :email";
        $uid = $this->db->query_db_first($sql, array(":email" => $email));
        if(!$uid) return false;
        $token = $this->login->create_token();
        $res = $this->db->update_by_ids("users", array("token" => $token),
            array("email" => $email));
        if(!$res) return false;
        $url = $this->get_link_url("validate", array(
            "uid" => intval($uid['id']),
            "token" => $token,
            "mode" => "reset",
        ));
        $url = "https://" . $_SERVER['HTTP_HOST'] . $url;
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', time()),
            "from_email" => "noreply@" . $_SERVER['HTTP_HOST'],
            "from_name" => PROJECT_NAME,
            "reply_to" => "noreply@" . $_SERVER['HTTP_HOST'],
            "recipient_emails" => $email,
            "subject" => $_SESSION['project'] . " Password Reset",
            "body" => str_replace('@link', $url, $this->email_user),
            "is_html" => 1,
            "description" => "Password reset email"
        );
        $mail['id_users'][] = intval($uid['id']);
        return $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
    }

    /* Public Methods *********************************************************/

    /**
     * Implementation of the parent abstract method:
     *  1. Add a new validation togen to the DB
     *  2. Send an email to the user
     *
     * @param string $mail
     *  The email address of the user.
     */
    public function perform_email_actions($mail)
    {
        return $this->user_set_new_token($mail);
    }

    /**
     * Check if the settings are for anonymous_users
     * @return bool
     * Return the result
     */
    public function is_anonymous_users()
    {
        return $this->db->is_anonymous_users();
    }

    /**
     * Check if enable reset password is active for anonymous users
     * @return boolean
     * Return the result of the check
     */
    public function is_reset_password_enabled()
    {
        return $this->db->fetch_page_info(SH_SECURITY_QUESTIONS)['enable_reset_password'];
    }

    /**
     * Get reset user name if set from controller
     * @return string 
     * User name
     */
    public function get_reset_user_name(){
        return $this->reset_user_name;
    }

    /**
     * Set reset user name
     * @param string $user_name
     * The user name that is requested to be reset
     */
    public function set_reset_user_name($user_name){
        $this->reset_user_name = $user_name;
    }

    /**
     * Get the security questions for the user_name that should be reset
     * @return string
     * Return json with the security questions
     */
    public function get_user_security_questions()
    {
        $sql = "SELECT security_questions FROM users WHERE user_name = :user_name";
        return $this->db->query_db_first($sql, array(":user_name" => $this->reset_user_name));
    }

    /**
     * Get security questions
     * @return array 
     * Array with the security questions - labels
     */
    public function get_security_questions()
    {
        return $this->db->fetch_page_info(SH_SECURITY_QUESTIONS);
    }    

    /**
     * Generate user token and return the reset url. It is used for anonymous user reset
     * @return string
     * The reset url
     */
    public function get_reset_url_for_anonymous_user(){
        $token = $this->login->create_token();
        $res = $this->db->update_by_ids("users", array("token" => $token),
            array("user_name" => $this->get_reset_user_name()));
        if(!$res) return false;
        $sql = "SELECT id FROM users WHERE user_name = :user_name";
        $user = $this->db->query_db_first($sql, array(":user_name" => $this->reset_user_name));
        if(!$user || !isset($user['id'])){
            return false;
        }
        $url = $this->get_link_url("validate", array(
            "uid" => intval($user ['id']),
            "token" => $token,
            "mode" => "reset",
        ));
        $url = "https://" . $_SERVER['HTTP_HOST'] . $url;
        return $url;
    }
}
?>
