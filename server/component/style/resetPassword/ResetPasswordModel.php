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
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all component related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section associated to the profile page.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
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
            // "from_email" => "noreply@" . $_SERVER['HTTP_HOST'],
            "from_email" => "noreply@" . PROJECT_NAME . '.unibe.ch',
            "from_name" => PROJECT_NAME,
            // "reply_to" => "noreply@" . $_SERVER['HTTP_HOST'],
            "reply_to" => "noreply@" . PROJECT_NAME . '.unibe.ch',
            "recipient_emails" => $email,
            "subject" => $_SESSION['project'] . " Password Reset",
            "body" => str_replace('@link', $url, $this->email_user),
            "description" => "Password reset email"
        );
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
}
?>
