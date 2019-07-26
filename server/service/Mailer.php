<?php
require_once __DIR__ . "/globals_untracked.php";
require_once __DIR__ . "/ext/PHPMailer.php";

use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends PHPMailer
{
    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->CharSet = 'UTF-8';
        $this->Encoding = 'base64';
        parent::__construct(false);
    }

    /* Public Methods *********************************************************/

    /**
     * Create the receiver array with a single to address.
     *
     * @param string $address
     *  The receiver email address.
     * @param string $name
     *  The receiver name.
     * @retval array
     *  The receiver array to be passed to Mailer::send().
     */
    public function create_single_to($address, $name = '')
    {
        return array('to' => array(
            array('address' => $address, 'name' => $name))
        );
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
    public function get_content($url, $email_type)
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
     * Send an email with PHPMailer (using php mail function).
     *
     * @param array $from
     *  The sender as an array with the keys 'address' and 'name'.
     * @param array $to
     *  All receivers as an array with the keys 'to', 'cc', and 'bcc' where
     *  each key holds a list of receiver arrays with the keys 'address' and
     *  'name'.
     * @param string $subject
     *  The email subject.
     * @param string $content
     *  The email body.
     * @param array $attachments
     *  A list of attachment paths.
     * @param $replyto
     *  The reply-to address as an array with the keys 'address' and 'name'.
     * @return bool
     *  True on success, false on failure.
     */
    public function send_mail($from, $to, $subject, $content, $content_html = null,
        $attachments = array(), $replyto = null)
    {
        $this->setFrom($from['address'], $from['name'] ?? '');
        foreach($to as $key => $recepients)
        {
            if($key === 'to')
                foreach($recepients as $to)
                    $this->addAddress($to['address'], $to['name'] ?? '');
            else if($key === 'cc')
                foreach($recepients as $to)
                    $this->addCC($to['address'], $to['name'] ?? '');
            else if($key === 'bcc')
                foreach($recepients as $to)
                    $this->addBCC($to['address'], $to['name'] ?? '');
        }
        $this->Subject = $subject;
        if($content_html)
        {
            $this->msgHTML($content_html);
            $this->AltBody = $content;
        }
        else
            $this->Body = $content;

        foreach($attachments as $attachment)
            $this->addAttachment($attachment);

        if($replyto)
            $this->addReplyTo($replyto['address'], $replyto['name'] ?? '');

        $res = $this->send();
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        return $res;
    }
}
?>
