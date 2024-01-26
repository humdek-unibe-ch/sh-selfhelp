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
require_once __DIR__ . "/../service/conditions/Condition.php";
require_once __DIR__ . "/../service/JobScheduler.php";
require_once __DIR__ . "/../service/Services.php";


spl_autoload_register(function ($class_name) {
    $folder = lcfirst(str_replace("Hooks", "", $class_name));
    $globals_file = __DIR__ . '/../plugins/' . $folder . '/server/service/globals.php';
    $hooks_file = __DIR__ . '/../plugins/' . $folder . '/server/component/' . $class_name . '.php';
    if (substr($class_name, -strlen('Hooks')) === 'Hooks') {
        // load only for hooks
        if (file_exists($globals_file)) {
            // load the global files in the hooks if exists
            require_once $globals_file;
        }
        if (file_exists($hooks_file)) {
            // load the hooks
            require_once $hooks_file;
        }
    }
});

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
     * An instance of the PHPMailer service to handle outgoing emails.
     */
    private $job_scheduler = null;

    private $services = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->services = new Services(false);
        $this->job_scheduler = $this->services->get_job_scheduler();
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
