<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/jobs/Notificationer.php";
require_once __DIR__ . "/jobs/Task.php";

/**
 * A wrapper class for PHPMailer. It provides a simple email sending method
 * which should be usable throughout this rpoject.
 */
class JobScheduler
{

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * The transaction instance that log to DB.
     */
    private $transaction;

    /**
     * The mailer service.
     */
    private $mail;

    /**
     * The notification service.
     */
    private $notification;

    /**
     * The condition service.
     */
    private $condition;

    /**
     * The task service.
     */
    private $task;

    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db, $transaction, $mail, $condition)
    {
        $this->db = $db;
        $this->transaction = $transaction;
        $this->mail = $mail;
        $this->notification = new Notificaitoner($db, $transaction, $condition);
        $this->task = new Task($db, $transaction, $condition);
        $this->condition = $condition;
    }

    /* Private Methods *********************************************************/

    private function schedule_mail($job_id, $data)
    {
        $attachments = isset($data['attachments']) ? $data['attachments'] : array();
        $mq_id = $this->mail->schedule($job_id, $data, $attachments);
        if (!$mq_id) {
            throw new Exception('Error in fucntion: schedule()');
        }
        return $this->db->insert('scheduledJobs_mailQueue', array(
            "id_scheduledJobs" => $job_id,
            "id_mailQueue" => $mq_id
        ));
    }

    private function schedule_notification($job_id, $data)
    {
        $id_notifications = $this->notification->schedule($job_id, $data);
        if (!$id_notifications) {
            throw new Exception('Error in fucntion: notification->schedule()');
        }
        return $this->db->insert('scheduledJobs_notifications', array(
            "id_scheduledJobs" => $job_id,
            "id_notifications" => $id_notifications
        ));
    }

    private function schedule_task($job_id, $data)
    {
        $id_tasks = $this->task->schedule($job_id, $data);
        if (!$id_tasks) {
            throw new Exception('Error in fucntion: task->schedule()');
        }
        return $this->db->insert('scheduledJobs_tasks', array(
            "id_scheduledJobs" => $job_id,
            "id_tasks" => $id_tasks
        ));
    }

    private function init_job($data)
    {
        $schedule_data = array(
            "id_jobTypes" => $data['id_jobTypes'],
            "id_jobStatus" => $data['id_jobStatus'],
            "description" => isset($data['description']) ? $data['description'] : "",
            "config" => isset($data['condition']) ? json_encode($data['condition']) : "",
            "date_to_be_executed" => $data['date_to_be_executed']
        );
        return $this->db->insert('scheduledJobs', $schedule_data);
    }

    private function set_status($sjid, $status)
    {
        $res = $this->db->update_by_ids(
            'scheduledJobs',
            array(
                "date_executed" => date('Y-m-d H:i:s', time()),
                "id_jobStatus" => $status
            ),
            array(
                "id" => $sjid
            )
        );
        if (!$res) {
            throw new Exception('Error! Job status cannot be set');
        }
    }       

    /* Public Methods *********************************************************/

    /**
     * Schedule jobs
     * @param array $data
     * all the information required for the job
     * @param string $action_by
     * who triggered the action
     * @retval int job_id if successfull otherwise false
     */
    public function schedule_job($data, $tran_by)
    {
        try {
            $this->db->begin_transaction();
            $job_id = $this->init_job($data);
            if ($data['id_jobTypes'] == $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email)) {
                if (!$this->schedule_mail($job_id, $data)) {
                    throw new Exception('Error while scheduling the email');
                }
            } else if ($data['id_jobTypes'] == $this->db->get_lookup_id_by_value(jobTypes, jobTypes_notification)) {
                if (!$this->schedule_notification($job_id, $data)) {
                    throw new Exception('Error while scheduling the notification');
                }
            } else if ($data['id_jobTypes'] == $this->db->get_lookup_id_by_value(jobTypes, jobTypes_task)) {
                if (!$this->schedule_task($job_id, $data)) {
                    throw new Exception('Error while scheduling the task');
                }
            }   
            $this->transaction->add_transaction(
                transactionTypes_insert,
                $tran_by,
                $tran_by == transactionBy_by_user ? $_SESSION['id_user'] : null,
                $this->transaction::TABLE_SCHEDULED_JOBS,
                $job_id
            );         
            $this->db->commit();
            return $job_id;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Delete scheduledJobs entry
     * @param int $sjid 
     * scheduledJobs id
     * @param string $tran_by
     * Who did the transacation
     * @retval boolean 
     * return the result
     */
    public function delete_job($sjid, $tran_by)
    {
        try {
            $this->db->begin_transaction();
            $del_result = $this->db->update_by_ids(
                'scheduledJobs',
                array(
                    "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_deleted)
                ),
                array(
                    "id" => $sjid
                )
            );
            if ($del_result === false) {
                $this->db->rollback();
                return false;
            } else {
                if (!$this->transaction->add_transaction(
                    transactionTypes_delete,
                    $tran_by,
                    $tran_by == transactionBy_by_user ? $_SESSION['id_user'] : null,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $sjid,
                    true
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

    /**
     * Schedule jobs
     * @param array $data
     * all the information required for the job
     * @param string $action_by
     * who triggered the action
     * @retval int job_id if successfull otherwise false
     */
    public function execute_job($data, $tran_by)
    {
        try {
            $this->db->begin_transaction();
            $execution_reult = true;
            $id_users = $tran_by == transactionBy_by_user ? $_SESSION['id_user'] : null;
            $data['config'] = isset($data['config']) ? $data['config'] : '';
            if ($data['id_jobTypes'] == $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email)) {
                // send email
                $execution_reult = $this->mail->send_entry($data['id'], $tran_by, $data['config'], $id_users);
            } else if ($data['id_jobTypes'] == $this->db->get_lookup_id_by_value(jobTypes, jobTypes_notification)) {
                // send notificaiton
                $execution_reult = $this->notification->send_entry($data['id'], $tran_by, $data['config'], $id_users);
            } else if ($data['id_jobTypes'] == $this->db->get_lookup_id_by_value(jobTypes, jobTypes_task)) {
                // execute task
                $execution_reult = $this->task->execute_entry($data['id'], $tran_by, $data['config'], $id_users);
            }
            $status = $this->db->get_lookup_id_by_value(scheduledJobsStatus, $execution_reult ? scheduledJobsStatus_done : scheduledJobsStatus_failed);
            $this->set_status($data['id'], $status);
            $this->transaction->add_transaction(
                transactionTypes_status_change,
                $tran_by,
                $id_users,
                $this->transaction::TABLE_SCHEDULED_JOBS,
                $data['id'],
                false,
                "Status changed to " . ($execution_reult ? "done" : "failed")
            );
            $this->db->commit();
            return $execution_reult;
        } catch (Exception $e) {
            $this->db->rollback();
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
        return $this->mail->remove_email_from_queue_entry($mqid, $sjid, $tran_by, $recipients, $log);
    }

    public function add_and_execute_job($data, $tran_by)
    {
        $sj_id = $this->schedule_job($data, $tran_by);
        if ($sj_id) {
            $data['id'] = $sj_id;
            return $this->execute_job($data, $tran_by);
        } else {
            return false;
        }
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function check_queue_and_execute($tran_by)
    {
        $id_users = $tran_by == transactionBy_by_user ? $_SESSION['id_user'] : null;
        $this->transaction->add_transaction(transactionTypes_check_scheduledJobse, $tran_by, $id_users);
        $sql = 'SELECT *
                FROM view_scheduledJobs
                WHERE date_to_be_executed <= NOW() AND id_jobStatus = :status';
        $queue = $this->db->query_db($sql, array(
            "status" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued)
        ));
        foreach ($queue as $job) {
            $this->execute_job($job, $tran_by);
        }
    }
}
?>
