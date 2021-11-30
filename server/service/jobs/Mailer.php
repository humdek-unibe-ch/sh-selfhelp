<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../globals_untracked.php";
require_once __DIR__ . "/../ParsedownExtension.php";
require_once __DIR__ . "/BasicJob.php";

/**
 * A wrapper class for PHPMailer. It provides a simple email sending method
 * which should be usable throughout this rpoject.
 */
class Mailer extends BasicJob
{
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
    public function __construct($db, $transaction, $user_input, $router, $condition)
    {
        $this->parsedown = new ParsedownExtension($user_input, $router);
        $this->parsedown->setSafeMode(false);
        $this->CharSet = 'UTF-8';
        $this->Encoding = 'base64';
        parent::__construct($db, $transaction, $condition);
    }

    /* Private Methods *********************************************************/
    

    /**
     * Send mail from the queue     
     * @param array $mail_info
     * Info for the mail queue entry
     * @param string  $sent_by  
     * the type which the email queue sent was triggered
     * @param int $user_id  
     * the user who sent the email, null if it was automated
     * @retval boolean
     *  return if mail was sent successfully
     */
    private function send_mail_single($mail_info, $sent_by, $condition, $user_id)
    {
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
        $attachments = array();
        $fetched_attachments = $this->db->query_db('SELECT attachment_name, attachment_path FROM mailAttachments WHERE id_mailQueue = :id_mailQueue;', array(
            ":id_mailQueue" => $mail_info['id_mailQueue']
        ));
        if ($fetched_attachments) {
            foreach ($fetched_attachments as $attachmnet) {
                $attachments[$attachmnet['attachment_name']] = $attachmnet['attachment_path'];
            }
        }
        foreach ($mail_info_recipients as $mail) {
            unset($to['to']);
            $to['to'][] = array('address' => $mail, 'name' => $mail);
            $user_info = $this->db->query_db_first('SELECT name, id FROM users WHERE email = :email', array(":email" => trim($mail)));
            $user_name = $user_info['name'];
            if ($this->check_condition($condition, $user_info['id'])) {
                $msg_send = str_replace('@user_name', $user_name, $msg);
                if ($msg_html) {
                    $msg_html_send = str_replace('@user_name', $user_name, $msg_html);
                }
                $res = $res && $this->send_mail($from, $to, $subject, $msg_send, $msg_html_send, $attachments, $replyTo);
                $this->transaction->add_transaction(
                    $res ? transactionTypes_send_mail_ok : transactionTypes_send_mail_fail,
                    $sent_by,
                    $user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $mail_info['id'],
                    false,
                    'Sending mail to ' . $mail
                );
            } else {
                $this->transaction->add_transaction(
                    transactionTypes_send_notification_fail,
                    $sent_by,
                    $user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $mail_info['id'],
                    false,
                    'Sending email to ' . $mail . ' failed because the condition was not meat'
                );
                $res = false;
            }
        }
        return $res;
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
    private function send_mail(
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

    /* Public Methods *********************************************************/

    /**
     * Insert mail record in the mailQueue table
     * @param array $data
     * @param array $attachments
     * array with name => path structure
     * @retval boolean
     *  return if the insert is successful
     */
    public function schedule($data, $attachments = array())
    {
        $mail_data = array(
            "from_email" => $data['from_email'],
            "from_name" => $data['from_name'],
            "reply_to" => $data['reply_to'],
            "recipient_emails" => $data['recipient_emails'],
            "cc_emails" => isset($data['cc_emails']) ? $data['cc_emails'] : "",
            "bcc_emails" => isset($data['bcc_emails']) ? $data['bcc_emails'] : "",
            "subject" => $data['subject'],
            "body" => $data['body'],
            "is_html" => isset($data['is_html']) ? $data['is_html'] : 1 //html by default if not set
        );
        $mail_queue_id = $this->db->insert('mailQueue', $mail_data);
        if ($mail_queue_id && count($attachments) > 0) {
            //insert attachments in the DB
            foreach ($attachments as $attachment) {
                $attachment["id_mailQueue"] = $mail_queue_id;
                $this->db->insert('mailAttachments', $attachment);
            }
        }
        return $mail_queue_id;
    }

    /**
     * Send mail from the queue
     * @param int $sj_id the scheduledJob id from where we will take the information for the fields that we will send
     * @param string  $sent_by  the type which the email queue sent was triggered
     * @param int $user_id  the user who sent the email, null if it was automated
     * @retval boolean
     *  return if mail was sent successfully
     */
    public function send_entry($sj_id, $sent_by, $condition, $user_id = null)
    {
        $mail_info = $this->db->select_by_uid('view_mailQueue', $sj_id);
        if ($mail_info) {
            return $this->send_mail_single($mail_info, $sent_by, $condition, $user_id);
        } else {
            return false;
        }
    }

    /**
     * Remove an email address from multi recipient email.
     * @retval boolean 
     * return the result
     */
    public function remove_email_from_queue_entry($mqid, $sjid, $tran_by, $recipients, $log)
    {
        try {
            $this->db->begin_transaction();
            $del_result = $this->db->update_by_ids(
                'mailQueue',
                array(
                    "recipient_emails" => $recipients
                ),
                array(
                    "id" => $mqid
                )
            );
            if ($del_result === false) {
                $this->db->rollback();
                return false;
            } else {
                if (!$this->transaction->add_transaction(
                    transactionTypes_update,
                    $tran_by,
                    $_SESSION['id_user'],
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $sjid,
                    true,
                    $log
                )) {
                    $this->db->rollback();
                    return false;
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
?>
