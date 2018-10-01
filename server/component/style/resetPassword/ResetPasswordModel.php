<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the ResetPasswordComponent
 * such that the data can easily be displayed in the view of the component.
 */
class ResetPasswordModel extends StyleModel
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
     * Read the email content from a php file and assign it to a string.
     *
     * @param string $url
     *  The activation link that will be included into the mail content.
     * @retval string
     *  The email content with evaluated php statements.
     */
    private function email_get_content($url)
    {
        ob_start();
        include(EMAIL_PATH . "/resetPassword_" . $_SESSION['language'] . ".php");
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /* Public Methods *********************************************************/

    /**
     * Create a new activation token for a user and send an email with the new
     * token to the user. This will allow the user to set a new password.
     *
     * @return bool
     *  True on success, false otherwise.
     */
    public function user_set_new_token($email)
    {
        $sql = "SELECT id FROM users WHERE email = :email";
        $uid = $this->db->query_db_first($sql, array(":email" => $email));
        if(!$uid) return false;
        $token = $this->login->create_token();
        $res = $this->db->update_by_ids("users", array("token" => $token),
            array("email" => $email));
        if(!$res) return false;
        $url = $this->get_link_url("validate", array(
            "uid" => $uid['id'],
            "token" => $token,
            "mode" => "reset",
        ));
        $url = "https://" . $_SERVER['HTTP_HOST'] . $url;
        $subject = $_SESSION['project'] . " Password Reset";
        $from = "noreply@" . $_SERVER['HTTP_HOST'];
        return $this->login->email_send($from, $email, $subject,
            $this->email_get_content($url));
    }
}
?>
