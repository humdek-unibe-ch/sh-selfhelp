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
        $subject = $_SESSION['project'] . " Password Reset";
        $from = array('address' => "noreply@" . $_SERVER['HTTP_HOST']);
        $to = $this->mail->create_single_to($email);
        $msg = $this->mail->get_content($url, 'email_reset');
        return $this->mail->send_mail($from, $to, $subject, $msg);
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
