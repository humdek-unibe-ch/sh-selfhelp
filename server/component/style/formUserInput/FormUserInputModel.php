<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";

/**
 * This class is used to prepare all data related to the form style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class FormUserInputModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * Entry data if the style is used in entry visualization
     */
    private $entry_data = null;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
    }

    /* Private Methods ********************************************************/

    private function get_form_id()
    {
        return $this->get_db_field("id");
    }

    /**
     * Calculate the date when the email should be sent when it is on weekday type
     * @param array $schedule_info
     * Schedule info from the action
     * @retval string
     * the date in sting format for MySQL
     */
    private function calc_date_on_weekday($schedule_info)
    {
        $now = date('Y-m-d H:i:s', time());
        $next_weekday = strtotime('next ' . $schedule_info['send_on_day'], strtotime($now));
        $d = new DateTime();
        $next_weekday = $d->setTimestamp($next_weekday);
        $at_time = explode(':', $schedule_info['send_on_day_at']);
        $next_weekday = $next_weekday->setTime($at_time[0], $at_time[1]);
        if ($schedule_info['send_on'] > 1) {
            return date('Y-m-d H:i:s', strtotime('+' . $schedule_info['send_on'] - 1 . ' weeks', $next_weekday->getTimestamp()));
        } else {
            $next_weekday = $next_weekday->getTimestamp();
            return date('Y-m-d H:i:s', $next_weekday);
        }
    }

    /**
     * Calculate the date when the email should be sent
     * @param array $schedule_info
     * Schedule info from the action
     * @param string $action_schedule_type_code
     * type notification or reminder
     * @retval string
     * the date in sting format for MySQL
     */
    private function calc_date_to_be_sent($schedule_info, $action_schedule_type_code)
    {
        $date_to_be_sent = 'undefined';
        if ($schedule_info[actionScheduleTypes] == actionScheduleTypes_immediately) {
            // send immediately
            $date_to_be_sent = date('Y-m-d H:i:s', time());
        } else if ($schedule_info[actionScheduleTypes] == actionScheduleTypes_on_fixed_datetime) {
            // send on specific date
            // $date_to_be_sent = date('Y-m-d H:i:s', DateTime::createFromFormat('d-m-Y H:i', $schedule_info['custom_time'])->getTimestamp());
            $date_to_be_sent = date('Y-m-d H:i:s', strtotime($schedule_info['custom_time']));
        } else if ($schedule_info[actionScheduleTypes] == actionScheduleTypes_after_period) {
            // send after time period 
            $now = date('Y-m-d H:i:s', time());
            $date_to_be_sent = date('Y-m-d H:i:s', strtotime('+' . $schedule_info['send_after'] . ' ' . $schedule_info['send_after_type'], strtotime($now)));
            if ($schedule_info['send_on_day_at']) {
                $at_time = explode(':', $schedule_info['send_on_day_at']);
                $d = new DateTime();
                $date_to_be_sent = $d->setTimestamp(strtotime($date_to_be_sent));
                $date_to_be_sent = $date_to_be_sent->setTime($at_time[0], $at_time[1]);
                $date_to_be_sent = date('Y-m-d H:i:s', $date_to_be_sent->getTimestamp());
            }
        } else if ($schedule_info[actionScheduleTypes] == actionScheduleTypes_after_period_on_day_at_time) {
            // send on specific weekday after 1,2,3, or more weeks at specific time
            $date_to_be_sent = $this->calc_date_on_weekday($schedule_info);
            if ($action_schedule_type_code == actionScheduleJobs_reminder) {
                // we have to check the linked notification and schedule the reminder always after the notification
                $schedule_info_notification = json_decode($this->db->query_db_first('SELECT schedule_info FROM formActions WHERE id = :id', array(':id' => $schedule_info['linked_action']))['schedule_info'], true);
                $base_schedule_info = $schedule_info;
                // $base_schedule_info['send_on'] = 1;
                // $schedule_info_notification['send_on'] = 1;
                $base_reminder_day = $this->calc_date_on_weekday($base_schedule_info);
                $base_notification_day = $this->calc_date_on_weekday($schedule_info_notification);
                if ($base_notification_day > $base_reminder_day) {
                    //reminder will be scheduled before the notification; it should be adjusted to 1 week later
                    $date_to_be_sent = date('Y-m-d H:i:s', strtotime('+1 weeks', strtotime($date_to_be_sent)));
                }
            }
        }
        return $date_to_be_sent;
    }

    /**
     * Check if the user belongs in group(s)
     * @param int $uid
     * user  id
     * @param string $id_groups
     * the grousp in coma separated string
     * @retval bool 
     * true if the user is in the group(s) or false if not
     */
    private function is_user_in_group($uid, $id_groups)
    {
        $sql = 'SELECT DISTINCT u.id
                FROM users AS u
                INNER JOIN users_groups AS ug ON ug.id_users = u.id
                INNER JOIN `groups` g ON g.id = ug.id_groups
                WHERE u.id = :uid and g.id in (' . $id_groups . ');';
        $user = $this->db->query_db_first(
            $sql,
            array(
                ":uid" => $uid
            )
        );
        return isset($user['id']);
    }

    /**
     * Get all users for selected groups
     *
     * @param array $groups
     *  Array with group ids
     *  @retval array
     * return all users for the selected groups or false
     */
    private function get_users_from_groups($groups)
    {
        $sql = "SELECT u.id
                    FROM users u
                    INNER JOIN users_groups g ON (u.id = g.id_users)
                    WHERE u.id_status = 3 AND g.id_groups IN (" . implode(",", $groups) . ");";
        return $this->db->query_db($sql);
    }

    /**
     * Queue task
     * @param array $users
     * user id arrays
     * @param array $action
     * the action information
     * @retval string
     *  log text what actions was done;
     */
    private function queue_task($users, $action)
    {
        $schedule_info = json_decode($action['schedule_info'], true);
        $result = array();
        $check_config = $this->check_config($schedule_info);
        $schedule_info = $check_config['schedule_info'];
        $result = $check_config['result'];
        $task = array(
            'id_jobTypes' => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_task),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => $this->calc_date_to_be_sent($schedule_info, $action['action_schedule_type_code']),
            "id_users" => $users,
            "config" => $schedule_info['config'],
            "description" => isset($schedule_info['config']['description']) ? $schedule_info['config']['description'] : "Schedule task by form: " . $this->get_db_field("name"),
        );
        $sj_id = $this->job_scheduler->schedule_job($task, transactionBy_by_system);
        if ($sj_id > 0) {
            $result[] = 'Task was queued for user: ' . $_SESSION['id_user'] . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
            if (($schedule_info[actionScheduleTypes] == actionScheduleTypes_immediately)) {
                $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sj_id));
                if (($this->job_scheduler->execute_job($job_entry, transactionBy_by_system))) {
                    $result[] = 'Task was executed for user: ' . $_SESSION['id_user'] . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
                } else {
                    $result[] = 'ERROR! Task was not executed for user: ' . $_SESSION['id_user'] . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
                }
            }
        } else {
            $result[] = 'ERROR! Task was not queued for user: ' . $_SESSION['id_user'] . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
        }
        return array(
            "result" => $result,
            "sj_id" => $sj_id
        );
    }

    /**
     * Queue mail
     *
     * @param int $user_id
     * user id
     * @param array $action
     * the action information
     * @retval string
     *  log text what actions was done;
     */
    private function queue_mail($user_id, $action)
    {
        //  {
        // 	"type": "overwrite_variable",
        // 	"variable": ["send_on_day_at", "var2"]    
        // }

        $schedule_info = json_decode($action['schedule_info'], true);
        $result = array();
        $check_config = $this->check_config($schedule_info);
        $schedule_info = $check_config['schedule_info'];
        $attachments = array();
        $mail_attachments_from_config = array();
        if(isset($schedule_info['attachments']) && $schedule_info['attachments']){
            $mail_attachments_from_config = json_decode($schedule_info['attachments'], true);
        }
        foreach ($mail_attachments_from_config as $idx => $attachment) {
            $attachments[] = array(
                "attachment_name" => $attachment,
                "attachment_path" => ASSET_SERVER_PATH . "/" . $attachment,
                "attachment_url" => ASSET_PATH . "/" . $attachment
           );
        }
        $result = $check_config['result'];
        $mail = array();        
        $body = str_replace('@user_name', $this->db->select_by_uid('users', $user_id)['name'], $schedule_info['body']);
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => $this->calc_date_to_be_sent($schedule_info, $action['action_schedule_type_code']),
            "from_email" => $schedule_info['from_email'],
            "from_name" => $schedule_info['from_name'],
            "reply_to" => $schedule_info['reply_to'],
            "recipient_emails" =>  str_replace('@user', $this->db->select_by_uid('users', $user_id)['email'], $schedule_info['recipient']),
            "subject" => $schedule_info['subject'],
            "body" => $body,
            "description" => "Schedule email by form: " . $this->get_db_field("name"),
            "condition" =>  isset($schedule_info['config']) && isset($schedule_info['config']['condition']) ? $schedule_info['config']['condition'] : null,
            "attachments" => $attachments
        );
        $mail['id_users'][] = $user_id;
        $sj_id = $this->job_scheduler->schedule_job($mail, transactionBy_by_system);
        if ($sj_id > 0) {
            if ($action['action_schedule_type_code'] == actionScheduleJobs_reminder) {
                $this->add_reminder($sj_id, $user_id, $action);
            }
            $result[] = 'Mail was queued for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
            if (($schedule_info[actionScheduleTypes] == actionScheduleTypes_immediately)) {
                $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sj_id));
                if ($this->job_scheduler->execute_job($job_entry, transactionBy_by_system)) {
                    $result[] = 'Mail was sent for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
                } else {
                    $result[] = 'ERROR! Mail was not sent for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
                }
            }
        } else {
            $result[] = 'ERROR! Mail was not queued for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
        }
        return array(
            "result" => $result,
            "sj_id" => $sj_id
        );
    }

    /**
     * Queue notification
     *
     * @param int $user_id
     * user id
     * @param array $action
     * the action information
     * @retval string
     *  log text what actions was done;
     */
    private function queue_notification($user_id, $action)
    {
        //  {
        // 	"type": "overwrite_variable",
        // 	"variable": ["send_on_day_at", "var2"]    
        // }

        $schedule_info = json_decode($action['schedule_info'], true);
        $result = array();
        $check_config = $this->check_config($schedule_info);
        $schedule_info = $check_config['schedule_info'];
        $result = $check_config['result'];

        $body = str_replace('@user_name', $this->db->select_by_uid('users', $user_id)['name'], $schedule_info['body']);
        $notification = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_notification),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => $this->calc_date_to_be_sent($schedule_info, $action['action_schedule_type_code']),
            "recipients" => array($user_id),
            "subject" => $schedule_info['subject'],
            "url" => isset($schedule_info['url']) ? $schedule_info['url'] : null,
            "condition" =>  isset($schedule_info['config']) && isset($schedule_info['config']['condition']) ? $schedule_info['config']['condition'] : null,
            "body" => $body,
            "description" => "Schedule notification by form: " . $this->get_db_field("name"),
        );
        $sj_id = $this->job_scheduler->schedule_job($notification, transactionBy_by_system);
        if ($sj_id > 0) {
            if ($action['action_schedule_type_code'] == actionScheduleJobs_reminder) {
                $this->add_reminder($sj_id, $user_id, $action);
            }
            $result[] = 'Notification was queued for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
            if (($schedule_info[actionScheduleTypes] == actionScheduleTypes_immediately)) {
                $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sj_id));
                if (($this->job_scheduler->execute_job($job_entry, transactionBy_by_system))) {
                    $result[] = 'Notification was sent for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
                } else {
                    $result[] = 'ERROR! Notification was not sent for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
                }
            }
        } else {
            $result[] = 'ERROR! Notificaton was not queued for user: ' . $user_id . ' when form: ' . $this->get_db_field("name") . ' ' . $action['trigger_type'];
        }
        return array(
            "result" => $result,
            "sj_id" => $sj_id
        );
    }

    /**
     * Add a reminder in formActionsReminders
     *
     * @param int $sj_id
     *  the scheduled job id
     * @param int $uid
     * user id
     * @param array $action
     * the action info
     * @retval int
     *  The id of the new record.
     */
    private function add_reminder($sj_id, $uid, $action)
    {
        $res = $this->db->insert("formActionsReminders", array(
            "id_users" => $uid,
            "id_forms" => $action['id_forms_reminder'],
            "id_scheduledJobs" => $sj_id
        ));
        return $res;
    }

    /**
     * Check config field for extra modifications
     * @param array $schedule_info
     * the schedule info
     * @retval array 
     * return the info from the check
     */
    private function check_config($schedule_info)
    {
        $result = array();
        if (isset($schedule_info['config']['type']) && $schedule_info['config']['type'] == "overwrite_variable") {
            // check form for more groups comming as embeded data
            if (isset($schedule_info['config']['variable'])) {
                foreach ($schedule_info['config']['variable'] as $key => $variable) {
                    if (isset($_POST[$variable])) {
                        $result[] = 'Overwrite variable `' . $variable . '` from ' . $schedule_info[$variable] . ' to ' . $_POST[$variable]['value'];
                        $schedule_info[$variable] = $_POST[$variable]['value'];
                    }
                }
            }
        }
        if (isset($schedule_info['config']['overwrite_variables']) && count($schedule_info['config']['overwrite_variables']) > 0) {
            // check form for custom varaibles that overwrite some data
            foreach ($schedule_info['config']['overwrite_variables'] as $key => $var_pairs) {
                if (isset($_POST[$var_pairs['embeded_variable']])) {
                    $result[] = 'Overwrite variable `' . $var_pairs['scheduled_variable'] . '` from ' . $schedule_info[$var_pairs['scheduled_variable']] . ' to ' . $_POST[$var_pairs['embeded_variable']]['value'];
                    $schedule_info[$var_pairs['scheduled_variable']] = $_POST[$var_pairs['embeded_variable']]['value'];
                }
            }
        }
        return array(
            "result" => $result,
            "schedule_info" => $schedule_info
        );
    }

    /**
     * Insert a new form field entry to the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @param string $id_record
     *  The id of user input record. This serves to group a set of input data
     * @param int $id_users
     * The user who create the record
     *  fields.
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function insert_new_entry($id, $value, $id_record, $id_users)
    {
        $res = $this->db->insert("user_input", array(
            "id_users" => $id_users,
            "id_sections" => $id,
            "id_section_form" => $this->get_form_id(),
            "value" => $value,
            "id_user_input_record" => $id_record,
        ));
        $this->transaction->add_transaction(
            transactionTypes_insert,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $this->transaction::TABLE_USER_INPUT,
            $id_record
        );
        return $res;
    }

    /**
     * Update a form field entry in the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @param string $id_record
     *  The id of user input record. This serves to group a set of input data
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function update_entry($id, $value)
    {

        $sql = "UPDATE user_input SET `value` = :value
            WHERE id_users = :id_users AND id_sections = :id_sections AND id_section_form = :id_section_form
            ORDER BY id DESC LIMIT 1;";
        return $this->db->execute_update_db($sql, array(
            "value" => $value,
            "id_users" => intval($_SESSION['id_user']),
            "id_sections" => $id,
            "id_section_form" => $this->get_form_id()
        ));
    }

    /**
     * Update a form field entry in the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @param int $record_id
     * The id_user_input_record from the table user_input
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function update_entry_with_record_id($id, $value, $record_id)
    {        
        $own_entries_only = $this->get_db_field("own_entries_only", "1");
        $filter = " AND deleted = 0 AND record_id = " . $record_id;
        $entry_record = $this->user_input->get_data($this->get_form_id(), $filter, $own_entries_only, FORM_INTERNAL, null, true);
        $field_name = $this->get_form_field_name($id);
        $res = false;
        $tran_type = '';
        if (isset($entry_record[$field_name])) {
            // field exists update it
            $res = $this->db->update_by_ids(
                "user_input",
                array(
                    "value" => $value,
                ),
                array(
                    "id_sections" => $id,
                    "id_section_form" => $this->get_form_id(),
                    "id_user_input_record" => $record_id
                )
            );
            $tran_type = transactionTypes_update;
        } else {
            // the field is new and does not exist
            // insert it
            // insert it with user_id of the creator - otherwise the row cannot be grouped
            // add transaction
            $res = $this->insert_new_entry($id, $value, $record_id, $entry_record['user_id']);
            $tran_type = transactionTypes_insert;
        }
        $this->transaction->add_transaction(
            $tran_type,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $this->transaction::TABLE_USER_INPUT,
            $record_id
        );
        return $res;
    }

    /**
     * Get all actions for a form and a trigger_type
     *
     * @param string $id_forms
     *  form id
     * @param string $trigger_type
     *  trigger type
     *  @retval array
     * return all actions for that survey with this trigger_type
     */
    private function get_actions($id_forms, $trigger_type)
    {
        $sqlGetActions = "SELECT fa.id as id, fa.name as action_name, fa.id_forms as id_forms, f.form_name,
                        fa.id_formProjectActionTriggerTypes, trig.lookup_value as trigger_type, trig.lookup_code as trigger_type_code,
                        GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS `groups`, 
                        GROUP_CONCAT(DISTINCT g.id*1 SEPARATOR ', ') AS id_groups, 
                        schedule_info, fa.id_formActionScheduleTypes, action_type.lookup_code as action_schedule_type_code, action_type.lookup_value as action_schedule_type, id_forms_reminder, 
                        CASE 
                            WHEN action_type.lookup_value = 'Reminder' THEN f_reminder.form_name 
                            ELSE NULL
                        END as form_reminder_name, fa.id_formActions
                        FROM formActions fa 
                        INNER JOIN view_form f ON (fa.id_forms = f.form_id)
                        INNER JOIN lookups trig ON (trig.id = fa.id_formProjectActionTriggerTypes)
                        INNER JOIN lookups action_type ON (action_type.id = fa.id_formActionScheduleTypes)
                        LEFT JOIN view_form f_reminder ON (fa.id_forms_reminder = f_reminder.form_id)
                        LEFT JOIN formActions_groups fg on (fg.id_formActions = fa.id)
                        LEFT JOIN `groups` g on (fg.id_groups = g.id)
                WHERE fa.id_forms = :id_forms AND trig.lookup_code = :trigger_type 
                AND action_type.lookup_value <> 'Nothing'
                GROUP BY fa.id, fa.name, fa.id_forms, fa.id_formProjectActionTriggerTypes, trig.lookup_value, f.form_name, form_reminder_name;";
        return $this->db->query_db(
            $sqlGetActions,
            array(
                "id_forms" => $id_forms,
                "trigger_type" => $trigger_type
            )
        );
    }

    /* Public Methods *********************************************************/

    /**
     * Fetch the label of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the label will be fetched.
     * @retval string
     *  The label of the form field or the empty string if the label is not
     *  available.
     */
    public function get_field_label($id_section)
    {
        return $this->user_input->get_field_label($id_section);
    }

    /**
     * Fetch the style of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the style will be fetched.
     * @retval string
     *  The style of the form field or the empty string if the style is not
     *  available.
     */
    public function get_field_style($id_section)
    {
        $this->user_input->get_field_style($id_section);
    }

    /**
     * Fetch the type of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the type will be fetched.
     * @retval string
     *  The type of the form field or the empty string if the type is not
     *  available.
     */
    public function get_field_type($id_section)
    {
        $sql = "SELECT sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            WHERE f.name = 'type_input' AND sft.id_sections = :id";
        $type = $this->db->query_db_first(
            $sql,
            array(":id" => $id_section)
        );
        if ($type) return $type["content"];
        return "";
    }

    /**
     * Check whether user has already submitted data to this form field.
     *
     * @param int $id
     *  The section id of the field to check for.
     * @retval bool
     *  True if data exists, false otherwise.
     */
    public function has_field_data($id)
    {
        // $sql = "SELECT * FROM user_input
        //     WHERE id_sections = :id AND id_section_form = :fid
        //     AND id_users = :uid";
        // $res = $this->db->query_db($sql, array(
        //     ":id" => $id,
        //     ":fid" => $this->get_form_id(),
        //     ":uid" => $_SESSION['id_user'],
        // ));
        $res = $this->user_input->get_input_fields(array(
            "id_section" => $id,
            "id_user" => $_SESSION['id_user'],
            "id_section_form" => $this->get_form_id()
        ));
        if ($res) return true;
        else return false;
    }

    /**
     * Check whether user has already submitted data to this form.
     *
     * @retval bool
     *  True if data exists, false otherwise.
     */
    public function has_form_data()
    {
        $res = $this->user_input->get_input_fields(array(
            "id_user" => $_SESSION['id_user'],
            "id_section_form" => $this->get_form_id()
        ));
        if ($res) return true;
        else return false;
    }

    /**
     * Check the last record_id for the form. Used for the update form which is not is_log
     * @retval int
     *  return record_id, if not return false
     */
    public function get_id_record()
    {
        $own_entries_only = $this->get_db_field("own_entries_only", "1");
        $res = $this->user_input->get_data($this->get_form_id(),'ORDER BY record_id DESC',$own_entries_only, FORM_INTERNAL, null, true);
        if ($res) return $res['record_id'];
        else return false;
    }

    /**
     * Checks whether the form is a logging or a documentation form.
     *
     * @retval bool
     *  True if the form is a log form, false otherwise.
     */
    public function is_log()
    {
        return $this->get_db_field("is_log", false);
    }

    /**
     * Save the user input to the database.
     *
     * @param array $user_input
     *  The array of input key => value pairs where the key is the name of the
     *  input field.
     * @retval int
     *  The number of affected rows in the database or false if an error
     *  ocurred.
     */
    public function save_user_input($user_input)
    {
        $count = 0;
        $id_record = null;
        if ($this->is_log() || !$this->has_form_data()) {
            $id_record = $this->db->insert("user_input_record", array());
        }
        $this->db->begin_transaction();
        foreach ($user_input as $id => $value) {
            if ($this->is_log() || !$this->has_field_data($id))
                $res = $this->insert_new_entry($id, $value, $id_record, intval($_SESSION['id_user']));
            else {
                if ($id_record == null) {
                    $id_record = $this->get_id_record();
                }
                $res = $this->update_entry_with_record_id($id, $value, $id_record);
            }

            if ($res === false)
                return false;
            else
                $count += $res;
        }
        $this->db->commit();
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT); // clear the cache we did changes
        // Once data is entered to the uiser input database the attributes in
        // the user_input service needs to be updated.
        $this->user_input->set_field_attrs();
        return $count;
    }

    /**
     * Update the user input to the database.
     *
     * @param array $user_input
     *  The array of input key => value pairs where the key is the name of the
     *  input field.
     * @param int $record_id
     * The record id
     * @retval int
     *  The number of affected rows in the database or false if an error
     *  ocurred.
     */
    public function update_user_input($user_input, $record_id)
    {
        $count = 0;
        foreach ($user_input as $id => $value) {
            $res = $this->update_entry_with_record_id($id, $value, $record_id);
            if ($res === false) {
                return false;
            } else {
                $count += $res;
            }
        }
        // Once data is entered to the uiser input database the attributes in
        // the user_input service needs to be updated.
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT); // clear the cache we did changes
        $this->user_input->set_field_attrs();
        return $count;
    }

    /**
     * Send feedback email to the user after the data is saved.
     * If there is data_config we retreieve the data base don the config
     */
    public function send_feedback_email()
    {
        $entry_data = $this->entry_data;
        $data_config = $this->get_db_field("data_config", '');
        $subject = $this->get_db_field("email_subject", '');
        $body = $this->get_db_field("email_body", '');
        $email_address = $this->get_db_field("email_address", '');
        if ($entry_data) {
            // $entry_data = json_decode($entry_data, true);            
            $body = $this->get_entry_value($entry_data, $body);
            $subject = $this->get_entry_value($entry_data, $subject);
            $data_config = $this->get_entry_value($entry_data, $data_config);
            $email_address = $this->get_entry_value($entry_data, $email_address);
        }
        $email_address = str_replace('@email_user', $this->db->select_by_uid('users', $_SESSION['id_user'])['email'], $email_address);
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', time()),
            "from_email" => PROJECT_NAME . '@unibe.ch',
            "from_name" => PROJECT_NAME,
            "reply_to" => PROJECT_NAME . '@unibe.ch',
            "recipient_emails" => $email_address,
            "subject" => $subject,
            "body" => $body,
            "description" => "FormUserInput Feedback email"
        );
        $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
    }

    /**
     * Get form user input record row
     * @param string $form_name
     * the name of the form
     * @param int $record_id
     * the record id
     * * @param int $own_entries_only
     * If true it loads only records created by the same user
     * @retval @array
     * the record row
     */
    public function get_form_entry_record($form_name, $record_id, $own_entries_only)
    {
        $form_id = $this->user_input->get_form_id($form_name);
        $filter = " AND deleted = 0 AND record_id = " . $record_id;
        return $this->$this->user_input->get_data($form_id, $filter, $own_entries_only);
    }

    /**
     * Mark this user input as removed in the database.
     *
     * @param int $record_id
     *  The record_id of the fields to be marked as removed.
     */
    public function delete_user_input($record_id)
    {
        $this->db->begin_transaction();
        $res = $this->db->update_by_ids('user_input', array('removed' => 1), array('id_user_input_record' => $record_id));
        $this->transaction->add_transaction(
            transactionTypes_delete,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $this->transaction::TABLE_USER_INPUT,
            $record_id
        );
        $this->db->commit();
        return $res;
    }

    /**
     * Get the form field id
     * @param int $field_id
     * the section_id of the field
     * @retval string the fiedl name
     */
    public function get_form_field_name($field_id)
    {
        return $this->user_input->get_form_field_name($field_id);
    }

    public function set_entry_data($entry_data)
    {
        $this->entry_data = $entry_data;
    }

    /**
     * Check if any event should be queued based on the actions
     *
     * @retval string
     *  log text what actions was done;
     */
    public function queue_event_from_actions($trigger_type)
    {
        $result = array();
        //get all actions for this form and trigger type
        $actions = $this->get_actions($this->section_id, $trigger_type);
        foreach ($actions as $action) {
            //clear the mail generation data
            if ($this->is_user_in_group($_SESSION['id_user'], $action['id_groups'])) {
                $schedule_info = json_decode($action['schedule_info'], true);
                $res = array();
                if ($action['action_schedule_type_code'] == actionScheduleJobs_task) {
                    $users = array();
                    if (isset($schedule_info['target_groups'])) {
                        $users_from_groups = $this->get_users_from_groups($schedule_info['target_groups']);
                        if ($users_from_groups) {
                            foreach ($users_from_groups as $key => $user) {
                                array_push($users, $user['id']);
                            }
                            $users = array_unique($users);
                        }
                    } else {
                        array_push($users, $_SESSION['id_user']);
                    }
                    $start_time = microtime(true);
                    $start_date = date("Y-m-d H:i:s");
                    $res = $this->queue_task($users, $action);
                    $res['time'] = [];
                    $end_time = microtime(true);
                    $res['time']['exec_time'] = $end_time - $start_time;
                    $res['time']['start_date'] = $start_date;
                    array_push($result, $res);
                } else if (
                    $action['action_schedule_type_code'] == actionScheduleJobs_notification ||
                    $action['action_schedule_type_code'] == actionScheduleJobs_reminder
                ) {
                    if ($schedule_info['notificationTypes'] == notificationTypes_email) {
                        // the notification type is email                        
                        $start_time = microtime(true);
                        $start_date = date("Y-m-d H:i:s");
                        $res = $this->queue_mail($_SESSION['id_user'], $action);
                        $res['time'] = [];
                        $end_time = microtime(true);
                        $res['time']['exec_time'] = $end_time - $start_time;
                        $res['time']['start_date'] = $start_date;
                        array_push($result, $res);
                    } else if ($schedule_info['notificationTypes'] == notificationTypes_push_notification) {
                        // the notification type is push notification                        
                        $start_time = microtime(true);
                        $start_date = date("Y-m-d H:i:s");
                        $res = $this->queue_notification($_SESSION['id_user'], $action);
                        $res['time'] = [];
                        $end_time = microtime(true);
                        $res['time']['exec_time'] = $end_time - $start_time;
                        $res['time']['start_date'] = $start_date;
                        array_push($result, $res);
                    }
                }
                if (isset($res['sj_id'])) {
                    $this->db->insert('scheduledJobs_formActions', array(
                        "id_scheduledJobs" => $res['sj_id'],
                        "id_formActions" => $action['id'],
                    ));
                }
            }
        }

        if (count($result) == 0) {
            $result[] = "no event";
        }
        return $result;
    }

    /**
     * Change the status of the queueud mails to deleted
     * @param array $scheduled_reminders
     * Arra with reminders that should be deleted
     */
    public function delete_reminders($scheduled_reminders)
    {
        foreach ($scheduled_reminders as $reminder) {
            $this->job_scheduler->delete_job($reminder['id_scheduledJobs'], transactionBy_by_system);
        }
    }

    /**
     * Get the scheduled reminders for the user and this survey
     * @retval array
     * all scheduled reminders
     */
    public function get_scheduled_reminders()
    {
        return $this->db->query_db(
            'SELECT id_scheduledJobs 
            FROM view_formActionsReminders 
            WHERE `user_id` = :uid AND id_forms = :sid AND status_code = :status
            AND (valid_till IS NULL OR (NOW() BETWEEN session_start_date AND valid_till))',
            array(
                ":uid" => $_SESSION['id_user'],
                ":sid" => $this->section_id,
                ":status" => scheduledJobsStatus_queued
            )
        );
    }
}
?>
