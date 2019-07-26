<?php
require_once __DIR__ . "/../emailFormBase/EmailFormBaseModel.php";
require_once __DIR__ . "/../StyleComponent.php";
require_once __DIR__ . "/../../user/UserModel.php";

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
            $sql = "SELECT * FROM users WHERE email = :email";
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
        $to = $this->mail->create_single_to($address);
        $msg_html = $this->is_html ? $this->parsedown->text($this->email_user) : null;
        foreach($this->attachments_user as $idx => $attachment)
            $this->attachments_user[$idx] = ASSET_SERVER_PATH . "/" . $attachment;

        return $this->mail->send_mail($this->from, $to, $this->subject_user,
            $this->email_user, $msg_html, $this->attachments_user);
    }

    /**
     * Send the email EmailFormModel::email_admins to * EmailFormModel::admins.
     */
    private function send_emails_admin()
    {
        $msg = str_replace('@email', $address, $this->email_admins);
        $msg_html = $this->is_html ? $this->parsedown->text($msg) : null;
        $subject = "Interested User for Platform " . $_SESSION['project'];
        foreach($this->admins as $admin)
        {
            $to = $this->mail->create_single_to($admin);
            $this->mail->send_mail($this->from, $to, $subject, $msg, $msg_html);
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
            $res = $this->send_email_user($mail);
        if($res)
            $this->send_emails_admin();
        return $res;
    }
}
?>
