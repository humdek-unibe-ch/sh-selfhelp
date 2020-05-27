<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/globals_untracked.php";
require_once __DIR__ . "/ext/PHPMailer.php";
require_once __DIR__ . "/ext/PHPMailer_Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * A wrapper class for PHPMailer. It provides a simple email sending method
 * which should be usable throughout this rpoject.
 */
class Mailer extends PHPMailer
{

    /* Constants ************************************************/

    /* Status */
    const STATUS_QUEUED = 'queued';
    const STATUS_DELETED = 'deleted';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_LOOKUP_TYPE = 'mailQueueStatus';

    /* Sent by */
    const SENT_BY_CRON = 'by_cron';
    const SENT_BY_USER = 'by_user';
    const SENT_BY_QUALTRICS_CALLBACK = 'by_qualtrics_callback';
    const SENT_BY_LOOKUP_TYPE = 'mailSentBy';

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
        return array(
            'to' => array(
                array('address' => $address, 'name' => $name)
            )
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
        if ($res) {
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
     *  The email plaintext body.
     * @param string $content_html
     *  The email HTML body.
     * @param array $attachments
     *  A list of attachment paths.
     * @param $replyto
     *  The reply-to address as an array with the keys 'address' and 'name'.
     * @return bool
     *  True on success, false on failure.
     */
    public function send_mail(
        $from,
        $to,
        $subject,
        $content,
        $content_html = null,
        $attachments = array(),
        $replyto = null
    ) {
        $this->setFrom($from['address'], $from['name'] ?? '');
        foreach ($to as $key => $recepients) {
            if ($key === 'to')
                foreach ($recepients as $to)
                    $this->addAddress($to['address'], $to['name'] ?? '');
            else if ($key === 'cc')
                foreach ($recepients as $to)
                    $this->addCC($to['address'], $to['name'] ?? '');
            else if ($key === 'bcc')
                foreach ($recepients as $to)
                    $this->addBCC($to['address'], $to['name'] ?? '');
        }
        $this->Subject = $subject;
        if ($content_html) {
            $this->msgHTML($content_html);
            $this->AltBody = $content;
        } else
            $this->Body = $content;

        foreach ($attachments as $attachment)
            $this->addAttachment($attachment);

        if ($replyto)
            $this->addReplyTo($replyto['address'], $replyto['name'] ?? '');

        $res = $this->send();
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        return $res;
    }

    /**
     * Insert mail record in the mailQueue table
     * @param array $data
     * @retval boolean
     *  return if the insert is successful
     */
    public function add_mail_to_queue($data)
    {
        return $this->db->insert('mailQueue', $data);
    }

    /**
     * Send mail from the queue
     * @param int $mail_queue_id the mail queeue id from where we will take the information for the fields that we will send
     * @param string  $sent_by  the type which the email queue sent was triggered
     * @param int $user_id  the user who sent the email, null if it was automated
     * @retval boolean
     *  return if mail was sent successfully
     */
    public function send_mail_from_queue($mail_queue_id, $sent_by, $user_id = null)
    {        
        $mail_info = $this->db->select_by_uid('mailQueue', $mail_queue_id);
        if ($mail_info) {
            $from = array(
                'address' => $mail_info['from_email'],
                'name' => $mail_info['from_name'],
                );
            $to = array();
            $mail_info_recipients = explode(MAIL_SEPARATOR, $mail_info['recipient_emails']);
            $mails = array();
            foreach ($mail_info_recipients as $mail) {

                $mails[] = array('address' => $mail, 'name' => $mail);
            }
            $to['to'] = $mails;
            //$to = $this->create_single_to($mail_info['recipient_emails']);
            $subject = $mail_info['subject'];
            $msg = $mail_info['body'];
            $msg_html = $mail_info['is_html'] === 2 ? $this->parsedown->text($msg) : $msg;
            $replyTo = array('address' => $mail_info['reply_to']);

            $res = $this->send_mail($from, $to, $subject, $msg);
            if ($res) {
                return $this->db->update_by_ids(
                    'mailQueue',
                    array(
                        "id_users" => $user_id,
                        "date_sent" => date('Y-m-d H:i:s', time()),
                        "id_mailSentBy" => $this->db->get_lookup_id_by_value(Mailer::SENT_BY_LOOKUP_TYPE, $sent_by),
                        "id_mailQueueStatus" => $this->db->get_lookup_id_by_value(Mailer::STATUS_LOOKUP_TYPE, $res ? Mailer::STATUS_SENT : Mailer::STATUS_FAILED)
                    ),
                    array(
                        "id" => $mail_queue_id
                    )
                );
            } else {
                return false;
            }
        } else {
            return false;
        }  
    }
}
?>
