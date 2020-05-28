<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../service/globals.php";
require_once __DIR__ . "/../service/PageDb.php";
require_once __DIR__ . "/../service/Mailer.php";
require_once __DIR__ . "/../service/ParsedownExtension.php";

/**
 * MailQueue class. It is scheduled on a cronjob and it is executed on given time. It checks for mails
 * that should be send within the time and schedule events for them.
 */
class MailQueue
{    

    /**
     * The db instance which grants access to the DB.
     */
    private $db = null;

    /**
     * The transaction instance which logs to the DB.
     */
    private $transaction = null;

    /**
     * An instance of the PHPMailer service to handle outgoing emails.
     */
    private $mail = null;

    /**
     * A markdown parser with custom extensions.
     */
    private $parsedown = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);
        $this->transaction = new Transaction($this->db);

        $this->mail = new Mailer($this->db, $this->transaction);

        $this->parsedown = new ParsedownExtension();
        $this->parsedown->setSafeMode(false);
        
    }

    /**
     * Test function that print the time
     */
    private function printTime($val = null)
    {
        $fh = fopen('C:\Users\Stefan\Desktop\test.txt', 'a');
        echo $fh;
        if ($val) {
            echo fwrite($fh, '[' . date('Y-m-d H:i:s') . ']' . json_encode($val) . "\r\n");
        } else {
            echo fwrite($fh, date('Y-m-d H:i:s') . "\r\n");
        }
        fclose($fh);
    }

    /**
     * Check if any mail should be queued
     *
     * @param array $mail_info
     *  a row from mailQueue table that contains all the information that we need to send the mail
     * @retval string
     *  log text what actions was done;
     */
    private function send_mail($mail_queue_id)
    {
        //$res = $this->mail->send_mail_from_queue($mail_queue_id);
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function check_queue()
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
            $this->printTime($mail_queue_id);
            $this->mail->send_mail_from_queue($mail_queue_id['id'], Mailer::SENT_BY_CRON);
        }
    }
}

$mailQueue = new MailQueue();
$mailQueue->check_queue();

?>
