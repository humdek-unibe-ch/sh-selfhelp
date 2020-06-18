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
class ModuleMailModel extends BaseModel
{

    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/
    /**
     * mail queue id,
     */
    private $mqid;

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
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $mqid)
    {
        parent::__construct($services);
        $this->mqid = $mqid;
    }

    /**
     * Return the mail queue records for the selected period over the selcted date type
     * @retval array
     * The list of the mail queue entries that should be returned
     */
    public function get_mail_queue()
    {
        $sql = "SELECT *
                FROM view_mailQueue 
                WHERE CAST(" . $this->date_type . " AS DATE) BETWEEN STR_TO_DATE(:date_from,'%d-%m-%Y') AND STR_TO_DATE(:date_to,'%d-%m-%Y');";
        return $this->db->query_db($sql, array(
            ":date_from" => $this->date_from,
            ":date_to" => $this->date_to
        ));
    }

    /**
     * Return the mail queue transaction records for the selected period over the selcted date type
     * @retval array
     * The list of the mail queue transaction entries that should be returned
     */
    public function get_mail_queue_transactions()
    {
        $sql = "SELECT *
                FROM view_mailQueue_transactions 
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

    public function get_mqid()
    {
        return $this->mqid;
    }

    

    /**
     * send the selected queue entry
     * @retval boolean
     * return the result
     */
    public function send_selected_queue_entry()
    {
        return $this->mail->send_mail_from_queue($this->mqid, $this->transaction::TRAN_BY_USER, $_SESSION['id_user']) !== false;
    }

    /**
     * Check the queue and send the mails which should be sent
     * @retval array
     * return result array
     */
    public function check_queue_and_send()
    {
        return $this->mail->check_queue_and_send();
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
        $sql = "SELECT id, name 
                FROM groups;";
        $users = $this->db->query_db($sql);
        foreach ($users as $val) {
            array_push($arr, array("value" => ('group_' . intval($val['id'])), "text" => $val['name']));
        }
        return $arr;
    }

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
        try {
            $this->db->begin_transaction();
            $mail = array(
                "id_mailQueueStatus" => $this->db->get_lookup_id_by_value(mailQueueStatus, mailQueueStatus_queued),
                "date_to_be_sent" => date('Y-m-d H:i:s', DateTime::createFromFormat('d-m-Y H:i', $data['time_to_be_sent'])->getTimestamp()),
                "from_email" => $data['from_email'],
                "from_name" => $data['from_name'],
                "reply_to" => $data['reply_to'],
                "recipient_emails" => implode(MAIL_SEPARATOR . ' ', $recipients),
                "subject" => $data['subject'],
                "body" => $data['body']
            );
            $mq_id = $this->mail->add_mail_to_queue($mail);
            if ($this->transaction->add_transaction(
                $this->transaction::TRAN_TYPE_INSERT,
                $this->transaction::TRAN_BY_USER,
                $_SESSION['id_user'],
                $this->transaction::TABLE_MAILQUEUE,
                $mq_id
            )) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
