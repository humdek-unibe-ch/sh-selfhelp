<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../service/globals.php";
require_once __DIR__ . "/../service/PageDb.php";
require_once __DIR__ . "/../service/Mailer.php";

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
     * The constructor.
     */
    public function __construct()
    {
        $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);
        $this->transaction = new Transaction($this->db);

        $this->mail = new Mailer($this->db, $this->transaction);
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function check_queue()
    {
        $this->mail->check_queue_and_send();
    }
}

$mailQueue = new MailQueue();
$mailQueue->check_queue();

?>
