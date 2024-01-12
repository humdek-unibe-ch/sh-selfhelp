<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../emailFormBase/EmailFormBaseModel.php";

/**
 * This class is used to prepare all data related to the emailForm style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class EmailFormModel extends EmailFormBaseModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'do_store' (false)
     * If set to true the entered email address will be stored in the database.
     */
    private $do_store;

    /**
     * DB field 'admins' (empty string)
     * The admins to receive an automatically sent email.
     */
    private $admins;

    /**
     * DB field 'email_admins' (empty string)
     * The email to be sent to the admins.
     */
    private $email_admins;

    /**
     * The commonly used sender address.
     */
    private $from;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->admins = $this->get_db_field("admins", array());
        $this->email_admins = $this->get_db_field("email_admins");
        $this->do_store = $this->get_db_field('do_store', false);
        $this->from = array('address' => "noreply@" . $_SERVER['HTTP_HOST']);
    }

    /* Private Methods ********************************************************/

    /**
     * Add the email address to the database.
     *
     * @param string $address
     *  The email address to be stored.
     * @retval bool
     *  Returns true on success and false on failure. If
     *  IntrestedUserFormModel::do_store is set to false the function returns
     *  true immediately. If the email is already present in the DB the
     *  function also returns true.
     */
    private function add_email($address)
    {
        if(!$this->do_store)
            return true;

        $uid = $this->user->insert_new_user($address, NULL, 1);
        if($uid === false)
        {
            $sql = "SELECT * FROM `users` WHERE email = :email";
            $res = $this->db->query_db_first($sql, array(':email' => $address));
            if(!$res)
                return false;
        }
        return true;
    }

    /**
     * Send the email EmailForm::email_user to the specified address.
     *
     * @param string $address
     *  The address entered through the input form.
     * @retval bool
     *  True if the email was sent successfully to the user, False otherwise.
     */
    private function send_email_user($address)
    {
        $attachments = array();
        foreach ($this->attachments_user as $idx => $attachment) {
            $attachments[] = array(
                "attachment_name" => $attachment,
                "attachment_path" => ASSET_SERVER_PATH . "/" . $attachment,
                "attachment_url" => ASSET_PATH . "/" . $attachment
            );
        }
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', time()),
            "from_email" => $this->from['address'],
            "from_name" => PROJECT_NAME,
            "reply_to" => $this->from['address'],
            "recipient_emails" => $address,
            "subject" => $this->subject_user,
            "body" => $this->email_user,
            "is_html" => $this->is_html,
            "description" => "Emai from style EmailForm to the user",
            "attachments" => $attachments
        );
        $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_anonymous_user);
    }

    /**
     * Send the email EmailFormModel::email_admins to * EmailFormModel::admins.
     *
     * @param string $address
     *  The address entered through the input form.
     */
    private function send_emails_admin($address)
    {
        $msg = str_replace('@email', $address, $this->email_admins);
        $subject = "Interested User for Platform " . $_SESSION['project'];
        foreach($this->admins as $admin)
        {
            $mail = array(
                "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
                "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
                "date_to_be_executed" => date('Y-m-d H:i:s', time()),
                "from_email" => $this->from['address'],
                "from_name" => PROJECT_NAME,
                "reply_to" => $this->from['address'],
                "recipient_emails" => $admin,
                "subject" => $subject,
                "body" => $msg,
                "is_html" => $this->is_html,
                "description" => "Emai from style EmailForm to the admins"
            );
            $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_anonymous_user);
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Implementation of the parent abstract method:
     *  1. Add the email to the DB (if required)
     *  2. Send an email to the user
     *  3. Send an email to each admin
     *
     * @param string $mail
     *  The email address of the user.
     */
    public function perform_email_actions($mail)
    {
        $res = $this->add_email($mail);
        if($res)
            $this->send_email_user($mail);
        if($res)
            $this->send_emails_admin($mail);
        return $res;
    }
}
?>
