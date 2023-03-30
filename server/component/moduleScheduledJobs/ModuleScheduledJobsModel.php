<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ModuleScheduledJobsModel extends BaseModel
{

    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/
    /**
     * schedule job id,
     */
    private $sjid;

    /**
     * date from,
     */
    private $date_from;

    /**
     * date to,
     */
    private $date_to;

    /**
     * date type,
     */
    private $date_type;

    /**
     * job type
     */
    private $type;

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $sjid, $type = null)
    {
        parent::__construct($services);
        $this->sjid = $sjid;
        $this->type = $type;
    }

    /**
     * Return the scheduledJobs queue records for the selected period over the selcted date type
     * @retval array
     * The list of the mail queue entries that should be returned
     */
    public function get_scheduledJobs_queue()
    {
        $sql = "SELECT sj.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS status, l_types.lookup_code AS type_code, l_types.lookup_value AS type, sj.config,
                sj.date_create, date_to_be_executed, date_executed, description, 
                CASE
                    WHEN l_types.lookup_code = 'email' THEN mq.recipient_emails
                    WHEN l_types.lookup_code = 'notification' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
                    WHEN l_types.lookup_code = 'task' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
                    ELSE ''
                END AS recipient,
                CASE
                    WHEN l_types.lookup_code = 'email' THEN mq.subject
                    WHEN l_types.lookup_code = 'notification' THEN n.subject
                    ELSE ''
                END AS title,
                CASE
                    WHEN l_types.lookup_code = 'email' THEN mq.body
                    WHEN l_types.lookup_code = 'notification' THEN n.body
                    ELSE ''
                END AS message,
                sj_mq.id_mailQueue,
                id_jobTypes,
                id_jobStatus
                FROM scheduledJobs sj
                INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
                INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
                LEFT JOIN scheduledJobs_mailQueue sj_mq on (sj_mq.id_scheduledJobs = sj.id)
                LEFT JOIN mailQueue mq on (mq.id = sj_mq.id_mailQueue)
                LEFT JOIN scheduledJobs_notifications sj_n on (sj_n.id_scheduledJobs = sj.id)
                LEFT JOIN notifications n on (n.id = sj_n.id_notifications) 
                WHERE CAST(" . $this->date_type . " AS DATE) BETWEEN STR_TO_DATE(:date_from,'%d-%m-%Y') AND STR_TO_DATE(:date_to,'%d-%m-%Y');";
        return $this->db->query_db($sql, array(
            ":date_from" => $this->date_from,
            ":date_to" => $this->date_to
        ));
    }

    /**
     * Return the scheduledJobs queue transaction records for the selected period over the selcted date type
     * @retval array
     * The list of the scheduledJobs queue transaction entries that should be returned
     */
    public function get_scheduledJobs_queue_transactions()
    {
        $sql = "SELECT *
                FROM view_scheduledJobs_transactions 
                WHERE CAST(" . $this->date_type . " AS DATE) BETWEEN STR_TO_DATE(:date_from,'%d-%m-%Y') AND STR_TO_DATE(:date_to,'%d-%m-%Y');";
        return $this->db->query_db($sql, array(
            ":date_from" => $this->date_from,
            ":date_to" => $this->date_to
        ));
    }

    public function set_date_from($date_from)
    {
        $this->date_from = $date_from;
    }

    public function set_date_to($date_to)
    {
        $this->date_to = $date_to;
    }

    public function set_date_type($date_type)
    {
        $this->date_type = $date_type;
    }

    public function get_date_from()
    {
        return $this->date_from;
    }

    public function get_date_to()
    {
        return $this->date_to;
    }

    public function get_date_type()
    {
        return $this->date_type;
    }

    public function get_sjid()
    {
        return $this->sjid;
    }

    public function get_type()
    {
        return $this->type;
    }


    /**
     * execute the selected job entry
     * @retval boolean
     * return the result
     */
    public function execute_selected_job_entry()
    {
        $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $this->sjid));
        if ($job_entry) {
            return $this->job_scheduler->execute_job($job_entry, transactionBy_by_user);
        } else {
            return false;
        }
    }

    /**
     * Get all active users;
     * @retval array
     * array used for select dropdown
     */
    public function get_users()
    {
        $arr = array();
        $sql = "SELECT id, email, code, name 
                FROM users u 
                LEFT JOIN validation_codes c on (c.id_users = u.id)
                WHERE id_status = :active_status";
        $users = $this->db->query_db($sql, array(':active_status' => USER_STATUS_ACTIVE));
        foreach ($users as $val) {
            array_push($arr, array("value" => ('user_' . intval($val['id'])), "text" => ("[" . $val['code'] . '] ' . $val['email']) . ' - ' . $val['name']));
        }
        return $arr;
    }

    /**
     * Get all groups;
     * @retval array
     * array used for select dropdown
     */
    public function get_groups()
    {
        $arr = array();
        $sql = "SELECT id, `name` 
                FROM `groups`;";
        $users = $this->db->query_db($sql);
        foreach ($users as $val) {
            array_push($arr, array("value" => ('group_' . intval($val['id'])), "text" => $val['name']));
        }
        return $arr;
    }

    /**
     * Compose email and add it to mailQueue
     * @param array $data
     * the mailQueue data
     * @retval boolean true if succeded and false if not
     */
    public function compose_email($data)
    {
        $recipients = [];
        $uids = [];
        $gids = [];
        $emails = [];
        foreach ($data['recipients'] as $key => $value) {
            if (substr($value, 0, strlen('user_')) === 'user_') {
                $uids[] = str_replace('user_', '', $value);
            } else if (substr($value, 0, strlen('group_')) === 'group_') {
                $gids[] = str_replace('group_', '', $value);
            }
        }
        if (count($gids) > 0) {
            $sql = "SELECT email
                    FROM users u
                    INNER JOIN users_groups g on (u.id = g.id_users)
                    WHERE g.id_groups IN (" . implode(",", $gids) . ") AND email NOT IN ('admin','sysadmin','tpf');";
            $emails = $this->db->query_db($sql);
        }
        if (count($uids) > 0) {
            $sql = "SELECT email
                    FROM users u
                    WHERE id IN (" . implode(",", $uids) . ") AND email NOT IN ('admin','sysadmin','tpf');";
            $emails = array_merge($emails, $this->db->query_db($sql));
        }
        foreach ($emails as $key => $email) {
            $recipients[] = $email['email'];
        }
        $recipients = array_unique($recipients);
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', DateTime::createFromFormat('d-m-Y H:i', $data['time_to_be_sent'])->getTimestamp()),
            "from_email" => $data['from_email'],
            "from_name" => $data['from_name'],
            "reply_to" => $data['reply_to'],
            "recipient_emails" => implode(MAIL_SEPARATOR . ' ', $recipients),
            "subject" => $data['subject'],
            "body" => $data['body'],
            "id_notificationTypes" => $this->db->get_lookup_id_by_value(notificationTypes, notificationTypes_email),
            "description" => "Compose Email"
        );
        return $this->job_scheduler->schedule_job($mail, transactionBy_by_user);
    }

    /**
     * Compose email and add it to mailQueue
     * @param array $data
     * the mailQueue data
     * @retval boolean true if succeded and false if not
     */
    public function compose_notification($data)
    {
        $recipients = [];
        $uids = [];
        $gids = [];
        $users_from_groups = [];
        foreach ($data['recipients'] as $key => $value) {
            if (substr($value, 0, strlen('user_')) === 'user_') {
                $uids[] = intval(str_replace('user_', '', $value));
            } else if (substr($value, 0, strlen('group_')) === 'group_') {
                $gids[] = str_replace('group_', '', $value);
            }
        }
        if (count($gids) > 0) {
            $sql = "SELECT u.id
                    FROM users u
                    INNER JOIN users_groups g on (u.id = g.id_users)
                    WHERE g.id_groups IN (" . implode(",", $gids) . ") AND email NOT IN ('admin','sysadmin','tpf');";
            $users_from_groups = $this->db->query_db($sql);
        }
        foreach ($users_from_groups as $key => $user) {
            $uids[] = intval($user['id']);
        }
        $uids = array_unique($uids);
        $notification = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_notification),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', DateTime::createFromFormat('d-m-Y H:i', $data['time_to_be_sent'])->getTimestamp()),
            "recipients" => $uids,
            "url" => isset($data['url']) ? $data['url'] : null,
            "subject" => $data['subject'],
            "body" => $data['body'],
            "description" => "Compose Notification"
        );
        return $this->job_scheduler->schedule_job($notification, transactionBy_by_user);
    }

    public function get_attachments($mqid)
    {
        $attachments = array();
        $fetched_attachments = $this->db->query_db('SELECT attachment_name, attachment_path, attachment_url FROM mailAttachments WHERE id_mailQueue = :id_mailQueue;', array(
            ":id_mailQueue" => $mqid
        ));
        if ($fetched_attachments) {
            foreach ($fetched_attachments as $attachmnet) {
                $attachments[$attachmnet['attachment_name']] = $attachmnet['attachment_path'];
                $attachments[] = array(
                    "id" => $attachmnet['attachment_name'],
                    "title" => $attachmnet['attachment_name'],
                    "url" => $attachmnet['attachment_url']
                );
            }
        }
        return $attachments;
    }
}
