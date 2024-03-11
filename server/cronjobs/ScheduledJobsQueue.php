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

$plugins_folder = realpath(__DIR__ . '/../plugins/');

if ($plugins_folder !== false && is_dir($plugins_folder)) {
    if ($handle = opendir($plugins_folder)) {
        // Loop through the plugins folder
        while (false !== ($dir = readdir($handle))) {
            // Construct the path to the plugin directory
            $plugin_dir = $plugins_folder . DIRECTORY_SEPARATOR . $dir;
            // Check if it's a directory and not . or ..
            if (is_dir($plugin_dir) && $dir !== '.' && $dir !== '..') {
                // Construct path to the server/component directory
                $component_dir = $plugin_dir . '/server/component/';
                // Check if the directory exists
                if (is_dir($component_dir)) {
                    // Open the directory handle
                    if ($component_handle = opendir($component_dir)) {
                        // Loop through the PHP files in the component directory
                        while (false !== ($file = readdir($component_handle))) {
                            // Check if it's a PHP file
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                                // Extract the class name from the file
                                $class_name = pathinfo($file, PATHINFO_FILENAME);
                                // Check if the class name ends with "Hooks"
                                if (substr($class_name, -5) === 'Hooks') {
                                    // Construct path to the hooks file
                                    $hooks_file = $component_dir . $file;
                                    // Load the hooks file if it exists
                                    require_once $hooks_file;
                                    // Optionally, load the globals file
                                    $globals_file = $plugin_dir . '/server/service/globals.php';
                                    if (file_exists($globals_file)) {
                                        require_once $globals_file;
                                    }
                                }
                            }
                        }
                        // Close the component directory handle
                        closedir($component_handle);
                    }
                }
            }
        }
        // Close the directory handle
        closedir($handle);
    }
}


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
