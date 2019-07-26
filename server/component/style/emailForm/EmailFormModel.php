<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";
require_once __DIR__ . "/../../user/UserModel.php";

/**
 * This class is used to prepare all data related to the emailForm style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class EmailFormModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'do_store' (false)
     * If set to true the entered email address will be stored in the database.
     */
    private $do_store;

    /**
     * DB field 'is_html' (false)
     * If set to true the email will be sent with an html body.
     */
    private $is_html;

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
     * DB field 'email_user' (empty string)
     * The email to be sent to the email address that was entered to the form.
     */
    private $email_user;

    /**
     * DB field 'subject_user' (empty string)
     * The subject of the email to be sent to the email address that was
     * entered to the form.
     */
    private $subject_user;

    /**
     * DB field 'attachments_user' (empty string)
     * The assets to be attached to the email that will be sent to the address
     * entered to the form.
     */
    private $attachments_user;

    /**
     * The instance of the user model.
     */
    private $user;

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
        $this->email_user = $this->get_db_field("email_user");
        $this->subject_user = $this->get_db_field("subject_user");
        $this->attachments_user = $this->get_db_field(
            "attachments_user", array());
        $this->do_store = $this->get_db_field('do_store', false);
        $this->is_html = $this->get_db_field('is_html', false);
        $this->user = new UserModel($services);
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

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
    public function add_email($address)
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
     * Send the email IntrestedUserFormModel::email_user to the specified
     * address and send the email IntrestedUserFormModel::email_admins to
     * IntrestedUserFormModel::admins.
     *
     * @param string $address
     *  The address entered through the input form.
     */
    public function send_emails($address)
    {
        // send mail to user
        $from = array('address' => "noreply@" . $_SERVER['HTTP_HOST']);
        $url = "https://" . $_SERVER['HTTP_HOST'] . $this->get_link_url('login');
        $to = $this->mail->create_single_to($address);
        $msg_html = $this->is_html ? $this->parsedown->text($this->email_user) : null;
        foreach($this->attachments_user as $idx => $attachment)
            $this->attachments_user[$idx] = ASSET_SERVER_PATH . "/" . $attachment;

        $this->mail->send_mail($from, $to, $this->subject_user,
            $this->email_user, $msg_html, $this->attachments_user);

        // send mail to admins
        $url = "https://" . $_SERVER['HTTP_HOST'] . $this->get_link_url('home');
        $msg = str_replace('@email', $address, $this->email_admins);
        $msg_html = $this->is_html ? $this->parsedown->text($msg) : null;
        $subject = "Interested User for Platform " . $_SESSION['project'];
        foreach($this->admins as $admin)
        {
            $to = $this->mail->create_single_to($admin);
            $this->mail->send_mail($from, $to, $subject, $msg, $msg_html);
        }
    }
}
?>
