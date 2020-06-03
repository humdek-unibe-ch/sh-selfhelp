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

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * The transaction instance that log to DB.
     */
    private $transaction;

    /**
     * A markdown parser with custom extensions.
     */
    private $parsedown = null;

    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db, $transaction, $user_input, $router)
    {
        $this->db = $db;
        $this->transaction = $transaction;        
        $this->parsedown = new ParsedownExtension($user_input, $router);
        $this->parsedown->setSafeMode(false);
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
            $subject = $mail_info['subject'];
            $msg = $mail_info['body'];
            $msg_html = $mail_info['is_html'] == 1 ? $this->parsedown->text($msg) : $msg;
            $replyTo = array('address' => $mail_info['reply_to']);
            $res = true;
            foreach ($mail_info_recipients as $mail) {
                $to['to'][] = array('address' => $mail, 'name' => $mail);
                $res = $res && $this->send_mail($from, $to, $subject, $msg, $msg_html, array(), $replyTo);
                $this->transaction->add_transaction(
                    $res ? $this->transaction::TRAN_TYPE_SEND_MAIL_OK : $this->transaction::TRAN_TYPE_SEND_MAIL_FAIL,
                    $sent_by,
                    $user_id,
                    $this->transaction::TABLE_MAILQUEUE,
                    $mail_queue_id,
                    false,
                    'Sending mail to ' . $mail
                );
            }
            $db_send_res = $this->db->update_by_ids(
                'mailQueue',
                array(
                    "date_sent" => date('Y-m-d H:i:s', time()),
                    "id_mailQueueStatus" => $this->db->get_lookup_id_by_value(Mailer::STATUS_LOOKUP_TYPE, $res ? Mailer::STATUS_SENT : Mailer::STATUS_FAILED)
                ),
                array(
                    "id" => $mail_queue_id
                )
            );
            return $res && ($db_send_res !== false);
        } else {
            return false;
        }
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function check_queue_and_send()
    {
        $this->transaction->add_transaction(
            $this->transaction::TRAN_TYPE_CHECK_MAILQUEUE,
            $this->transaction::TRAN_BY_MAIL_CRON
        );
        $sql = 'SELECT id
                FROM mailQueue
                WHERE date_to_be_sent <= NOW() AND id_mailQueueStatus = :status';
        $queue = $this->db->query_db($sql, array(
            "status" => $this->db->get_lookup_id_by_value(Mailer::STATUS_LOOKUP_TYPE, Mailer::STATUS_QUEUED)
        ));
        foreach ($queue as $mail_queue_id) {
            $this->send_mail_from_queue($mail_queue_id['id'], $this->transaction::TRAN_BY_MAIL_CRON);
        }
    }
}
?>
