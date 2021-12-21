<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../service/globals.php";
require_once __DIR__ . "/../service/PageDb.php";
require_once __DIR__ . "/../service/jobs/Mailer.php";
require_once __DIR__ . "/../service/Transaction.php";
require_once __DIR__ . "/../service/Router.php";
require_once __DIR__ . "/../service/UserInput.php";
require_once __DIR__ . "/../service/Condition.php";
require_once __DIR__ . "/../service/JobScheduler.php";

/**
 * SETUP
 * Make the script executable:  chmod +x
 * Cronjob (Chek mail Scheduled Jobs every minutes and execute them if there any) * * * * * php /home/bashev/selfhelpQualtrics/server/cronjobs/ScheduledJobsQueue.php
 * TIP:
 * Chekc the time zone in mysql
 * sudo dpkg-reconfigure tzdata
 * /etc/init.d/mysql restart
 */

/**
 * ScheduledJobsQueue class. It is scheduled on a cronjob and it is executed on given time. It checks for mails
 * that should be send within the time and schedule events for them.
 */
class ScheduledJobsQueue
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
    private $job_scheduler = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);
        $this->transaction = new Transaction($this->db);
        $router = new Router($this->db, BASE_PATH);
        $router->addMatchTypes(array('v' => '[A-Za-z_]+[A-Za-z_0-9]*'));
        $user_input = new UserInput($this->db);
        $condition = new Condition($this->db, $user_input);
        $mail = new Mailer($this->db, $this->transaction, $user_input, $router, $condition);
        $this->job_scheduler = new JobScheduler($this->db, $this->transaction, $mail, $condition);
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function check_queue()
    {
        $this->job_scheduler->check_queue_and_execute(transactionBy_by_cron_job);
    }
}

$scheduledJobsQueue = new ScheduledJobsQueue();
$scheduledJobsQueue->check_queue();

?>
