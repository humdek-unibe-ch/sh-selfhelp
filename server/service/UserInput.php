<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * Class to deal with user inputs.
 */
class UserInput
{
    /* Private Properties *****************************************************/

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * The transaction instance that log to DB.
     */
    private $transaction;

    /**
     * Array that contains the ui preference settings for the user
     */
    private $ui_pref;

    /**
     * The condition service instance to handle conditional logic.
     */
    private $condition;

    /**
     * The JobScheduler service instance to handle jobs scheduling and execution.
     */
    private $job_scheduler;

    /* Constructors ***********************************************************/

    /**
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db, $transaction)
    {
        $this->db = $db;
        $this->transaction = $transaction;
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT);
    }

    /* Private Methods ********************************************************/


    /**
     * Fetch the page name to which the given navigation section belongs.
     *
     * @param int $id_section
     *  The id of the section
     * @return string
     *  The page name or null if the name could not be found.
     */
    private function fetch_nav_section_page($id_section)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_PAGES, $id_section, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT p.keyword FROM sections_navigation AS sn
                    LEFT JOIN pages AS p ON p.id = sn.id_pages
                    WHERE sn.child = :id";
            $page = $this->db->query_db_first($sql, array(":id" => $id_section));
            $res = $page ? $page["keyword"] : null;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the name of a section.
     *
     * @param int $id
     *  The id of the section
     * @return string
     *  The section name or null if the name could not be found.
     */
    private function fetch_section_name($id)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT `name` FROM sections WHERE id = :id";
            $parent = $this->db->query_db_first($sql, array(":id" => $id));
            $res = $parent ? $parent["name"] : null;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the job type
     * @param object $job
     * The job info
     * @return string
     * The job type
     */
    private function get_job_type($job)
    {
        if ($job['job_type'] == ACTION_JOB_TYPE_ADD_GROUP || $job['job_type'] == ACTION_JOB_TYPE_REMOVE_GROUP) {
            return jobTypes_task;
        } else if (
            $job['job_type'] == ACTION_JOB_TYPE_NOTIFICATION ||
            $job['job_type'] == ACTION_JOB_TYPE_NOTIFICATION_WITH_REMINDER ||
            $job['job_type'] == ACTION_JOB_TYPE_NOTIFICATION_WITH_REMINDER_FOR_DIARY
        ) {
            return jobTypes_notification;
        } else {
            return '';
        }
    }

    /**
     * Fetch the page name to which the given section belongs.
     *
     * @param int $id_section
     *  The id of the section
     * @return string
     *  The page name or null if the name could not be found.
     */
    private function fetch_section_page($id_section)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_PAGES, $id_section, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT p.keyword FROM pages_sections AS ps
                    LEFT JOIN pages AS p ON p.id = ps.id_pages
                    WHERE ps.id_sections = :id";
            $page = $this->db->query_db_first($sql, array(":id" => $id_section));
            $res = $page ? $page["keyword"] : null;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the id of the parent section.
     *
     * @param int $id_child
     *  The id of the child section.
     * @retval int
     *  The id of the parent section or null if no parent could be found.
     */
    private function fetch_section_parent($id_child)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id_child, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT parent FROM sections_hierarchy WHERE child = :id";
            $parent = $this->db->query_db_first($sql, array(":id" => $id_child));
            $res = $parent ? $parent["parent"] : null;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Find the page name and navigation section name of a given child section.
     *
     * @param int $id_section
     *  The id of the child section.
     * @retval array
     *  An array with the keys "page" and "nav" where the former holds the name
     *  of the parent page and the latter the name of the parent navigation
     *  section.
     */
    private function find_section_page($id_section)
    {
        $page = null;
        $nav = null;
        $parent_it = $this->fetch_section_parent($id_section);
        $parent = $parent_it;
        while ($parent_it !== null) {
            $parent = $parent_it;
            $parent_it = $this->fetch_section_parent($parent_it);
        }
        if ($parent !== null) {
            $page = $this->fetch_section_page($parent);
            if ($page === null) {
                $page = $this->fetch_nav_section_page($parent);
                $nav = $this->fetch_section_name($parent);
            }
        }
        return array("page" => $page, "nav" => $nav);
    }

    /**
     * Get all users for selected groups
     *
     * @param array $groups
     *  Array with group ids
     *  @return array
     * return all users for the selected groups or false
     */
    private function get_users_from_groups($groups)
    {
        foreach ($groups as &$value) {
            $value = "'" . $value . "'";
        }
        $sql = "SELECT u.id
                    FROM users u
                    INNER JOIN users_groups ug ON (u.id = ug.id_users)
                    INNER JOIN `groups` g ON (g.id = ug.id_groups)
                    WHERE u.id_status = 3 AND g.name IN (" . implode(",", $groups) . ");";
        return $this->db->query_db($sql);
    }

    /**
     * Get all actions for a data table and a trigger_type
     *
     * @param string $id_dataTables
     *  data table id
     * @param string $trigger_type
     *  trigger type
     *  @return array
     * return all actions for that survey with this trigger_type
     */
    private function get_actions($id_dataTables, $trigger_type)
    {
        $sqlGetActions = "SELECT * 
                          FROM view_formActions
                          WHERE id_dataTables = :id_dataTables AND trigger_type = :trigger_type;";
        return $this->db->query_db(
            $sqlGetActions,
            array(
                "id_dataTables" => $id_dataTables,
                "trigger_type" => $trigger_type
            )
        );
    }

    /**
     * Check config field for extra modifications
     * @param array $config
     * the config info
     * @param array $fields
     * the form fields and their vlaues
     * @return array 
     * return the info from the check
     */
    private function check_config($config, $fields)
    {
        $result = array();
        $form_values = $this->get_form_values($fields);
        // replace overwrite variables
        if (isset($config[ACTION_SELECTED_OVERWRITE_VARIABLES])) {
            foreach ($config[ACTION_SELECTED_OVERWRITE_VARIABLES] as $var_index => $variable) {
                foreach ($config['blocks'] as $block_index => $block) {
                    foreach ($block['jobs'] as $job_index => $job) {
                        if (isset($form_values[$variable])) {
                            $config['blocks'][$block_index]['jobs'][$job_index][ACTION_JOB_SCHEDULE_TIME][$variable] = $form_values[$variable];
                            $result[] = 'Overwrite variable `' . $variable . '` with value ' . $form_values[$variable];
                        }
                    }
                }
            }
        }

        $config = $this->db->replace_calced_values($config, $form_values); //replace {{vars}}

        return array(
            "result" => $result,
            "config" => $config
        );
    }

    /**
     * Increment the randomization_count on the executed blocks in the action config
     * @param object $action
     * The action row
     * @param object $not_modified_action
     * The original action row, which the dynamic data check is not applied
     * @param array $blocks
     * The selected blocks which will be executed
     * @return array;
     * Return the update blocks with the new counters for the action and not modified action
     */
    private function update_randomization_count($action, $not_modified_action, $blocks)
    {
        foreach ($blocks as $block_index => $block) {
            $action['config']['blocks'][$block['index']][ACTION_BLOCK_RANDOMIZATION_COUNT]++; //this one will be returned
            $not_modified_action['config']['blocks'][$block['index']][ACTION_BLOCK_RANDOMIZATION_COUNT]++; // this one will be used to update the DB and if there were variable replacement in the dynamic data check it will not be saved in the DB
        }
        $this->db->update_by_ids("formActions", array(
            "config" => json_encode($not_modified_action['config'])
        ), array('id' => $not_modified_action['id']));
        return array(
            "action" => $action,
            "not_modified_action" => $not_modified_action
        );
    }

    /**
     * Get the task config
     * @param object $job
     * The job data
     * @param $form_data
     * The form data
     * @return array
     * Return the task config
     */
    private function get_task_config($job, $form_data)
    {
        $task_config = array(
            "type" => $job[ACTION_JOB_TYPE],
            "description" => isset($job['job_name']) ? $job['job_name'] : "Schedule task by form: " . $form_data['form_name'],
            "group" => $job[ACTION_JOB_ADD_REMOVE_GROUPS]
        );
        return $task_config;
    }

    /**
     * Queue task
     * @param array $users
     * user id arrays
     * @param array $action
     * the action information
     * @param array $form_data
     * The form data
     * @return array[]
     *  log text what actions was done;
     */
    private function queue_task($users, $job, $action, $form_data)
    {
        $result = array();
        $config = $this->get_task_config($job, $form_data); //extra config for condition
        $config['condition'] = isset($job['on_job_execute']['condition']['jsonLogic']) ? $job['on_job_execute']['condition']['jsonLogic'] : null;
        $task = array(
            'id_jobTypes' => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_task),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => $this->set_execution_date($action, $job),
            "id_users" => $users,
            "config" => $config,
            "description" => isset($job['job_name']) ? $job['job_name'] : "Schedule task by form: " . $form_data['form_name'],
            "condition" =>  isset($job['on_job_execute']['condition']['jsonLogic']) ? $job['on_job_execute']['condition']['jsonLogic'] : null,
        );
        $sj_id = $this->job_scheduler->schedule_job($task, transactionBy_by_system);
        if ($sj_id > 0) {
            $result[] = 'Task was queued for user: ' . $_SESSION['id_user'] . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
            $execution_date = new DateTime($task['date_to_be_executed']);
            $now = new DateTime();
            if (($job[ACTION_JOB_SCHEDULE_TIME][ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_immediately) && $now->getTimestamp() >= $execution_date->getTimestamp()) {
                $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sj_id));
                if (($this->job_scheduler->execute_job($job_entry, transactionBy_by_system))) {
                    $result[] = 'Task was executed for user: ' . $_SESSION['id_user'] . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                } else {
                    $result[] = 'ERROR! Task was not executed for user: ' . $_SESSION['id_user'] . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                }
            }
        } else {
            $result[] = 'ERROR! Task was not queued for user: ' . $_SESSION['id_user'] . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
        }
        return array(
            $sj_id => array(
                "job_type" => jobTypes_task,
                "result" => $result
            )
        );
    }

    /**
     * Sends a reminder to a user based on provided parameters.
     *
     * @param string $parent_date_to_be_executed The date/time of the parent job to which the reminder is associated.
     * @param array $user An array containing user information.
     * @param array $reminder An array containing reminder information.
     * @param array $action An array containing action information.
     * @param array $form_data An array containing form data associated with the reminder.
     * @return array An array containing the result of the reminder sending operation.
     */
    private function send_reminder($parent_date_to_be_executed, $user, $reminder, $action, $form_data)
    {
        $result = array();
        if (isset($reminder['condition']["jsonLogic"]) && !$this->condition->compute_condition($reminder['condition']["jsonLogic"], $user)['result']) {
            $result['condition'] = "Reminder action condition is not met";
            return $result;
        }
        $reminder_dates = null;
        $date_to_be_executed = date('Y-m-d H:i:s', strtotime('+' . $reminder[ACTION_JOB_SCHEDULE_TIME]['send_after'] . ' ' . $reminder[ACTION_JOB_SCHEDULE_TIME]['send_after_type'], strtotime($parent_date_to_be_executed)));
        if ($reminder[ACTION_JOB_SCHEDULE_TIME]['parent_job_type_hidden'] == ACTION_JOB_TYPE_NOTIFICATION_WITH_REMINDER_FOR_DIARY) {
            $reminder_dates = array(
                "session_start_date" => $parent_date_to_be_executed, // parent notification schedule time
                "session_end_date" => date('Y-m-d H:i:s', strtotime('+' . $reminder[ACTION_JOB_SCHEDULE_TIME]['valid'] . ' ' . $reminder[ACTION_JOB_SCHEDULE_TIME]['valid_type'], strtotime($date_to_be_executed)))
            );
        }
        $users_arr = is_array($user) ? $user : array($user); // check if the users are array, if they are not make it array
        if ($reminder['notification']['notification_types'] == notificationTypes_email) {
            return  $this->queue_mail($users_arr, $reminder, $action, $form_data, $date_to_be_executed, $reminder_dates);
        } else if ($reminder['notification']['notification_types'] == notificationTypes_push_notification) {
            return  $this->queue_notification($users_arr, $reminder, $action, $form_data, $date_to_be_executed, $reminder_dates);
        }
        return $result;
    }

    /**
     * Queue mail
     * @param array $users
     * user id arrays
     * @param array $action
     * the action information
     * @param array $form_data
     * The form data
     * @return array[]
     *  log text what actions was done;
     */
    private function queue_mail($users, $job, $action, $form_data, $date_to_be_executed = null, $reminder_dates = null)
    {
        $result = array();
        $attachments = array();
        foreach ($job['notification']['attachments'] as $idx => $attachment) {
            $attachments[] = array(
                "attachment_name" => $attachment,
                "attachment_path" => ASSET_SERVER_PATH . "/" . $attachment,
                "attachment_url" => ASSET_PATH . "/" . $attachment
            );
        }
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => $date_to_be_executed ? $date_to_be_executed : $this->set_execution_date($action, $job),
            "from_email" => $job['notification']['from_email'],
            "from_name" => $job['notification']['from_name'],
            "reply_to" => $job['notification']['reply_to'],
            "subject" => $job['notification']['subject'],
            "description" => "Schedule email by form: " . $form_data['form_name'],
            "condition" =>  isset($job['on_job_execute']['condition']['jsonLogic']) ? $job['on_job_execute']['condition']['jsonLogic'] : null,
            "attachments" => $attachments
        );
        foreach ($users as $key => $user_id) {
            $mail['id_users'][] = $user_id;
            $user_info =  $this->db->select_by_uid('users', $user_id);
            // replace dynamically the email
            if ($action['config'][ACTION_TARGET_GROUPS]) {
                // it is sent to the whole group
                $mail['recipient_emails'] = $user_info['email'];
            } else {
                $mail['recipient_emails'] = str_replace(
                    '@user',
                    $user_info['email'],
                    $job['notification']['recipient']
                );
            }
            // replace dynamically the @user_name if used
            $mail['body'] = str_replace(
                '@user_name',
                $user_info['name'],
                $job['notification']['body']
            );

            $sj_id = $this->job_scheduler->schedule_job($mail, transactionBy_by_system);
            if (isset($job['form_id'])) {
                // it is a reminder
                $reminder_data = array(
                    "id_scheduledJobs" => $sj_id
                );
                if ($reminder_dates) {
                    $reminder_data['session_start_date'] = $reminder_dates['session_start_date'];
                    $reminder_data['session_end_date'] = $reminder_dates['session_end_date'];
                }
                $form = explode('-', $job['form_id']);
                if ($form[1] == FORM_INTERNAL) {
                    $reminder_data['id_forms_INTERNAL'] = $form[0];
                } else if ($form[1] == FORM_EXTERNAL) {
                    $reminder_data['id_forms_EXTERNAL'] = $form[0];
                }
                $this->db->insert('scheduledJobs_reminders', $reminder_data);
                $result[] = "Insert reminders for formId: " . $job['form_id'];
            }
            if ($sj_id > 0) {
                if (isset($job['reminders'])) {
                    $reminders_result = array();
                    foreach ($job['reminders'] as $reminder_idx => $reminder) {
                        $reminders_result[] = $this->send_reminder($mail['date_to_be_executed'], $user_id, $reminder, $action, $form_data);
                    }
                }
                $result[] = 'Mail was queued for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                $execution_date = new DateTime($mail['date_to_be_executed']);
                $now = new DateTime();
                if (isset($job[ACTION_JOB_SCHEDULE_TIME][ACTION_JOB_SCHEDULE_TYPES]) && ($job[ACTION_JOB_SCHEDULE_TIME][ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_immediately) && $now->getTimestamp() >= $execution_date->getTimestamp()) {
                    $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sj_id));
                    if ($this->job_scheduler->execute_job($job_entry, transactionBy_by_system)) {
                        $result[] = 'Mail was sent for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                    } else {
                        $result[] = 'ERROR! Mail was not sent for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                    }
                }
            } else {
                $result[] = 'ERROR! Mail was not queued for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
            }
        }
        return array(
            $sj_id => array(
                "job_type" => jobTypes_email,
                "result" => $result,
                "reminders" => isset($reminders_result) ? $reminders_result : array()
            )
        );
    }

    /**
     * Queue notification
     * @param array $users
     * user id arrays
     * @param array $action
     * the action information
     * @param array $form_data
     * The form data
     * @return array[]
     *  log text what actions was done;
     */
    private function queue_notification($users, $job, $action, $form_data, $date_to_be_executed = null, $reminder_dates = null)
    {
        $result = array();
        $notification = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_notification),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => $date_to_be_executed ? $date_to_be_executed : $this->set_execution_date($action, $job),
            "subject" => $job['notification']['subject'],
            "url" => isset($job['notification']['redirect_url']) ? $job['notification']['redirect_url'] : null,
            "condition" =>  isset($job['on_job_execute']['condition']['jsonLogic']) ? $job['on_job_execute']['condition']['jsonLogic'] : null,
            "description" => "Schedule notification by form: " . $form_data['form_name'],
        );
        foreach ($users as $key => $user_id) {
            $notification['recipients'] = array($user_id);
            $user_info =  $this->db->select_by_uid('users', $user_id);
            // replace dynamically the @user_name if used
            $notification['body'] = str_replace(
                '@user_name',
                $user_info['name'],
                $job['notification']['body']
            );
            $sj_id = $this->job_scheduler->schedule_job($notification, transactionBy_by_system);
            if (isset($job['form_id'])) {
                // it is a reminder
                $reminder_data = array(
                    "id_scheduledJobs" => $sj_id
                );
                if ($reminder_dates) {
                    $reminder_data['session_start_date'] = $reminder_dates['session_start_date'];
                    $reminder_data['session_end_date'] = $reminder_dates['session_end_date'];
                }
                $form = explode('-', $job['form_id']);
                if ($form[1] == FORM_INTERNAL) {
                    $reminder_data['id_forms_INTERNAL'] = $form[0];
                } else if ($form[1] == FORM_EXTERNAL) {
                    $reminder_data['id_forms_EXTERNAL'] = $form[0];
                }
                $this->db->insert('scheduledJobs_reminders', $reminder_data);
                $result[] = "Insert reminders for formId: " . $job['form_id'];
            }
            if ($sj_id > 0) {
                if (isset($job['reminders'])) {
                    $reminders_result = array();
                    foreach ($job['reminders'] as $reminder_idx => $reminder) {
                        $reminders_result[] = $this->send_reminder($notification['date_to_be_executed'], $users, $reminder, $action, $form_data);
                    }
                }
                $result[] = 'Notification was queued for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                $execution_date = new DateTime($notification['date_to_be_executed']);
                $now = new DateTime();
                if (isset($job[ACTION_JOB_SCHEDULE_TIME][ACTION_JOB_SCHEDULE_TYPES]) && ($job[ACTION_JOB_SCHEDULE_TIME][ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_immediately) && $now->getTimestamp() >= $execution_date->getTimestamp()) {
                    $job_entry = $this->db->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sj_id));
                    if (($this->job_scheduler->execute_job($job_entry, transactionBy_by_system))) {
                        $result[] = 'Notification was sent for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                    } else {
                        $result[] = 'ERROR! Notification was not sent for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
                    }
                }
            } else {
                $result[] = 'ERROR! Notification was not queued for user: ' . $user_id . ' when form: ' . $form_data['form_name'] . ' ' . $action['trigger_type'];
            }
        }
        return array(
            $sj_id => array(
                "job_type" => jobTypes_notification,
                "result" => $result,
                "reminders" => isset($reminders_result) ? $reminders_result : array()
            )
        );
    }

    /**
     * Calculate the execution date. If there is a repeater it takes it into account and 
     * recalculate the date properly
     * @param array $action
     * The action
     * @param array $job
     * The selected job
     * @return string
     * Return the execution date in string format
     */
    private function set_execution_date($action, $job)
    {
        $config = $action['config'];
        if (isset($config[ACTION_REPEAT]) && $config[ACTION_REPEAT]) {
            $repeater = $config[ACTION_REPEATER];
            // Define the start date of the event
            $start_date = $this->calc_date_to_be_sent($job[ACTION_JOB_SCHEDULE_TIME]);
            $start_date_time = date('H:i:s', strtotime($start_date));
            $current_date = $start_date;
            $schedule_dates = array();
            // Loop as many occurrences we have 
            while (count($schedule_dates) < $repeater[ACTION_REPEATER_OCCURRENCES]) {
                if (($repeater[ACTION_REPEATER_FREQUENCY] == 'week' && count($repeater[ACTION_REPEATER_DAYS_OF_WEEK]) == 0)
                    || ($repeater[ACTION_REPEATER_FREQUENCY] == 'month' && count($repeater[ACTION_REPEATER_DAYS_OF_MONTH]) == 0)
                ) {
                    // set the current day of the week or the month depending on the type
                    if ($repeater[ACTION_REPEATER_FREQUENCY] == 'week') {
                        $repeater[ACTION_REPEATER_DAYS_OF_WEEK][] = date('l'); //crurent day of the week
                    } else if ($repeater[ACTION_REPEATER_FREQUENCY] == 'month') {
                        $repeater[ACTION_REPEATER_DAYS_OF_MONTH][] = date('d'); // current day of the month
                    }
                }
                if (($repeater[ACTION_REPEATER_FREQUENCY] == 'day')
                    || ($repeater[ACTION_REPEATER_FREQUENCY] == 'week' && count($repeater[ACTION_REPEATER_DAYS_OF_WEEK]) > 0 && in_array(date('l', strtotime($current_date)), $repeater[ACTION_REPEATER_DAYS_OF_WEEK]))
                    || ($repeater[ACTION_REPEATER_FREQUENCY] == 'month' && count($repeater[ACTION_REPEATER_DAYS_OF_MONTH]) > 0 && in_array(date('d', strtotime($current_date)), $repeater[ACTION_REPEATER_DAYS_OF_MONTH]))
                ) {
                    // Event occurs on this day, schedule it                    
                    $schedule_dates[] = date('Y-m-d H:i:s', strtotime($start_date_time, strtotime($current_date))); // add the new date with the time that we already calculated in the start date
                }
                // Increment the current date with one day
                $current_date = date('Y-m-d', strtotime("+1 day", strtotime($current_date)));
            }
            return $schedule_dates[$action["repeat_index"]];
        } else if (isset($config[ACTION_REPEAT_UNTIL_DATE]) && $config[ACTION_REPEAT_UNTIL_DATE]) {
            $dates_to_be_scheduled = $this->calculateScheduledDatesRepeaterUntil($config[ACTION_REPEATER_UNTIL_DATE]);
            return $dates_to_be_scheduled[$action["repeat_index"]];
        } else {
            return $this->calc_date_to_be_sent($job[ACTION_JOB_SCHEDULE_TIME]);
        }
    }

    /**
     * Calculate the date when the email should be sent
     * @param array $schedule_time
     * Schedule info from the action
     * @return string
     * the date in sting format for MySQL
     */
    private function calc_date_to_be_sent($schedule_time)
    {
        $date_to_be_sent = 'undefined';
        if ($schedule_time[ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_immediately) {
            // send immediately
            $date_to_be_sent = date('Y-m-d H:i:s', time());
        } else if ($schedule_time[ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_on_fixed_datetime) {
            // send on specific date
            // $date_to_be_sent = date('Y-m-d H:i:s', DateTime::createFromFormat('d-m-Y H:i', $schedule_time['custom_time'])->getTimestamp());
            $date_to_be_sent = date('Y-m-d H:i:s', strtotime($schedule_time['custom_time']));
        } else if ($schedule_time[ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_after_period) {
            // send after time period 
            $now = date('Y-m-d H:i:s', time());
            $date_to_be_sent = date('Y-m-d H:i:s', strtotime('+' . $schedule_time['send_after'] . ' ' . $schedule_time['send_after_type'], strtotime($now)));
            if ($schedule_time['send_on_day_at']) {
                try {
                    $at_time = explode(':', $schedule_time['send_on_day_at']);
                    if (count($at_time) == 2) {
                        $d = new DateTime();
                        $date_to_be_sent = $d->setTimestamp(strtotime($date_to_be_sent));
                        $date_to_be_sent = $date_to_be_sent->setTime($at_time[0], $at_time[1]);
                        $date_to_be_sent = date('Y-m-d H:i:s', $date_to_be_sent->getTimestamp());
                    } else {
                        $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_system, null, null, null, false, array(
                            "data" => $schedule_time,
                            "text" => "The time is not set correctly"
                        ));
                    }
                } catch (Exception $e) {
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_system, null, null, null, false, array(
                        "error" => $e,
                        "data" => $schedule_time,
                        "text" => "error while calculating the time"
                    ));
                }
            }
        } else if ($schedule_time[ACTION_JOB_SCHEDULE_TYPES] == actionScheduleTypes_after_period_on_day_at_time) {
            // send on specific weekday after 1,2,3, or more weeks at specific time
            $date_to_be_sent = $this->calc_date_on_weekday($schedule_time);
        }
        return $date_to_be_sent;
    }

    /**
     * Calculate the date when the email should be sent when it is on weekday type
     * @param array $schedule_info
     * Schedule info from the action
     * @return string
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
     * Get the scheduled reminders for the user and this survey
     * @return array
     * all scheduled reminders
     */
    private function get_scheduled_reminders_for_delete($form_data)
    {
        $sql = 'SELECT id_scheduledJobs 
                FROM view_scheduledJobs_reminders 
                WHERE `id_users` = :uid AND id_dataTables = :form_id AND job_status_code = :status
                AND (session_end_date IS NULL OR (NOW() BETWEEN session_start_date AND session_end_date))';
        $id_users = isset($form_data['form_fields']['id_users']) ? $form_data['form_fields']['id_users'] : $_SESSION['id_user']; // the user could be set from the form, this happens with external forms
        return $this->db->query_db(
            $sql,
            array(
                ":uid" => $id_users,
                ":form_id" => $form_data['form_id'],
                ":status" => scheduledJobsStatus_queued
            )
        );
    }

    /**
     * Get the column id if the column already exists in the table
     * @param string $col_name
     * the column name 
     * @param int $table_id
     * the id of the table
     * @return int
     * Return the id of the column if it is found
     */
    private function get_dataTable_columnId($col_name, $table_id)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, $col_name, [__FUNCTION__, $table_id]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $res = $this->db->query_db_first("SELECT id FROM dataCols WHERE `name` = :col_name AND id_dataTables = :table_id", array(":col_name" => $col_name, ":table_id" => $table_id));
            $res = $res ? $res['id'] : '';
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the columns needed for the data table and insert them if they do not exists
     * @param int $id_table
     * The dataTable id
     * @param array $data
     * The data for the row. Based on it we will get the needed columns
     * @return array
     * Return array with the column name and the column id
     */
    private function get_columns_for_data_table($id_table, $data)
    {
        $col_ids = array();
        foreach ($data as $col_name => $value) {
            $id_col = $this->get_dataTable_columnId($col_name, $id_table);
            if (!$id_col) {
                // it does not exist, create it
                $id_col = $this->db->insert("dataCols", array(
                    "name" => $col_name,
                    "id_dataTables" => $id_table
                ));
            }
            $col_ids[$col_name] = $id_col;
        }
        return $col_ids;
    }

    /**
     * Check if an array is associative 
     * @param array
     * The array that we want to check
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Update the record in the data table
     * @param int $id_table
     * The data table id
     * @param array $record
     * The record that already exists
     * @param string $transaction_by
     * Who initiated the update
     * @param array $data
     * the new data that will update the old one
     * @param int $id_triggerType_id
     * The ID corresponding to the trigger type.
     * @return bool
     * Return the success of the update
     */
    private function update_data($id_table, $record, $transaction_by, $data, $id_triggerType_id)
    {
        $col_ids = $this->get_columns_for_data_table($id_table, $data);
        $res = $this->db->execute_update_db(
            "UPDATE dataRows SET `timestamp` = NOW(), id_users = :id_users, id_actionTriggerTypes = :id_actionTriggerTypes WHERE id = :id;",
            array(
                ':id' => $record[ENTRY_RECORD_ID],
                ":id_users" => $data['id_users'],
                ":id_actionTriggerTypes" => $id_triggerType_id
            )
        ) !== false; //update the timestamp of the row
        unset($data['id_users']); //once used - remove it
        foreach ($data as $key => $value) {
            // if it has a value it will be updated if it is not created yet it will be inserted
            $current_res = $this->db->insert('dataCells', array('id_dataRows' => $record[ENTRY_RECORD_ID], "id_dataCols" => $col_ids[$key], "value" => $value), array("value" => $value));
            $res = $res && $current_res;
        }
        if ($res) {
            $this->transaction->add_transaction(transactionTypes_update, $transaction_by, null, $this->transaction::TABLE_dataTables, $id_table);
        }
        return $res;
    }

    /**
     * @param string $transaction_by
     * Save  data in the dataTable structure
     * What initialized the transaction
     * @param string $table_name
     * The table name where we want to save the data
     * @param array $data
     * The data that we want to save - associative array which contains "name of the column" => "value of the column"
     * @param int $id_triggerType_id
     * The ID corresponding to the trigger type.
     * @param array|null $updateBasedOn
     * Optional parameter to specify the field name for updating the record instead of inserting.
     * @return bool
     */
    private function save_row($transaction_by, $table_name, $data, $id_triggerType_id, $updateBasedOn = null)
    {
        unset($data['trigger_type']); // do not save trigger_type as string, now is saved with id in the row
        if (!isset($data['id_users'])) {
            $data['id_users'] = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 1; // if not set in the session use the guest user
        }

        /******************* SET TABLE *********************************/
        $id_table = $this->get_dataTable_id($table_name);
        if (!$id_table) {
            // does not exists yet; try to create it
            $id_table = $this->db->insert("dataTables", array(
                "name" => $table_name
            ));
        } else if ($updateBasedOn) {
            $filter = '';
            foreach ($updateBasedOn as $key => $value) {
                $filter = $filter . ' AND ' . $key . ' = "' . $value . '"';
            }
            $record = $this->get_data(
                $id_table,
                $filter,
                ($data['id_users'] > 1), // if there is user we update only own data
                $data['id_users'],
                true
            );
            if ($record) {
                // the record exists, do not insert it, update it
                $res = $this->update_data($id_table, $record, $transaction_by, $data, $id_triggerType_id);
                return $res ? $record[ENTRY_RECORD_ID] : $res;
            } else if(count($updateBasedOn) > 0){
                // try to update or delete a record that does not exist;
                return false;
            }
        }
        /******************* SET TABLE *********************************/
        if (!$id_table) {
            return false;
        } else {
            if ($this->transaction->add_transaction(transactionTypes_insert, $transaction_by, null, $this->transaction::TABLE_dataTables, $id_table) === false) {
                return false;
            }

            /******************* SET COLUMNS *********************************/

            $col_ids = $this->get_columns_for_data_table($id_table, $data);

            /******************* SET COLUMNS *********************************/

            /******************* SET ROW     *********************************/
            $id_row = $this->db->insert("dataRows", array(
                "id_dataTables" => $id_table,
                "id_users" => $data['id_users'],
                "id_actionTriggerTypes" => $id_triggerType_id
            ));
            if (!$id_row) {
                return false;
            }
            unset($data['id_users']); //once used - remove it
            /******************* SET ROW     *********************************/

            /******************* SET CELLS   *********************************/
            $db_cells = array();
            foreach ($data as $col_name => $col_val) {
                array_push($db_cells, array($id_row, $col_ids[$col_name], $col_val ? $col_val : ''));
            }
            $res = $this->db->insert_mult(
                "dataCells",
                array(
                    "id_dataRows",
                    "id_dataCols",
                    "value"
                ),
                $db_cells
            );
            if (!$res) {
                return false;
            }

            /******************* SET CELLS   *********************************/
        }
        return isset($id_row) ? $id_row : $res;
    }

    /**
     * Change the status of the queued mails to deleted
     * @param array $scheduled_reminders
     * Array with reminders that should be deleted
     */
    public function delete_reminders($scheduled_reminders)
    {
        foreach ($scheduled_reminders as $reminder) {
            $this->job_scheduler->delete_job($reminder['id_scheduledJobs'], transactionBy_by_system);
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Convert a string to HTML valid id
     *
     * @param string $string
     *  the string value that we want to convert to a valid HTML id
     * @retval string
     * the converted string which will be used as ID
     */
    public function convert_to_valid_html_id($string)
    {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    /**
     * Get the UI preferences row for the user. If it is not set returns false
     * @return array or false
     * return the UI preferences row or false if it is not set
     */
    public function get_ui_preferences()
    {
        if (!isset($this->ui_pref)) {
            // check the database only once. If it is already assigned do not make a query and just returned the already assigned value
            $form_id = $this->get_dataTable_id_by_displayName('ui-preferences');
            if ($form_id) {
                $ui_pref = $this->get_data($form_id, '');
                $this->ui_pref = $ui_pref ? $ui_pref[0] : array();
            } else {
                $this->ui_pref = false;
            }
        }
        return $this->ui_pref;
    }

    /**
     * Get the notification settings
     * @param int $id_users
     * The user for who we check the settings. If not set we use the session user
     * @return array | false
     * return the UI preferences row or false if it is not set
     */
    public function get_user_notification_settings($id_users = null)
    {
        if ($id_users == null) {
            $id_users = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : -1;
        }
        $form_id = $this->get_dataTable_id('notification');
        if ($form_id) {
            $res = $this->get_data($form_id, '', true, $id_users);
            return $res ? $res[0] : false;
        }
        return false;
    }


    /**
     * Check if we should load the new UI or load the old UI
     */
    public function is_new_ui_enabled()
    {
        $ui_pref = $this->get_ui_preferences();
        if (!$ui_pref || (isset($ui_pref['old_ui'])  && $ui_pref['old_ui'] != 1)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the id of the dataTable
     * @param string $name
     * The name of the table     
     * @return int | false
     * False or the id of the dataTable
     */
    public function get_dataTable_id($name)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $name, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT id 
                FROM dataTables
                WHERE `name` = :name';
            $res = $this->db->query_db_first($sql, array(":name" => $name));
            $res = $res ? $res['id'] : false;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the id of the dataTable by displayName
     * @param string $displayName
     * The displayName of the table     
     * @return int | false
     * False or the id of the dataTable
     */
    public function get_dataTable_id_by_displayName($displayName)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $displayName, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT id 
                FROM view_dataTables
                WHERE `name` = :displayName';
            $res = $this->db->query_db_first($sql, array(":displayName" => $displayName));
            $res = $res ? $res['id'] : false;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the record data
     * @param int $form_id
     * the form id of the form that we want to fetcht
     * @param string $filter
     * filter string that is added to the having clause
     * @param boolean $own_entries_only
     * Load only own entries
     * @param int $user_id
     * Show the data for that user
     * @param boolean $db_first
     * If true it returns the first row. 
     * @return array
     * the result of the fetched data
     */
    public function get_data($form_id, $filter, $own_entries_only = true, $user_id = null, $db_first = false)
    {
        if (strpos($filter, '{{') !== false) {
            $filter = ''; // filter is not correct, tried to be set dynamically but failed
        }
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, $form_id, [__FUNCTION__, $filter, $own_entries_only, $user_id, $db_first]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            if (!$user_id) {
                $user_id =  isset($_SESSION['id_user']) ? $_SESSION['id_user'] : -1; // if the user is not defined we set the session user if needed
            }
            $params = array(
                ":form_id" => $form_id,
                ":user_id" => $user_id,
                ":filter" => $filter
            );
            $sql = 'CALL get_dataTable_with_filter(:form_id, :user_id, :filter, true)';
            if ($db_first) {
                $res = $this->db->query_db_first($sql, $params);
            } else {
                $res = $this->db->query_db($sql, $params);
            }
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the record data for a given user
     * @param int $form_id
     * the form id of the form that we want to fetcht
     * @param int $user_id
     * Show the data for that user
     * @param string $filter
     * filter string that is added to the having clause     
     * @param boolean $db_first
     * If true it returns the first row. 
     * @return array
     * the result of the fetched data
     */
    public function get_data_for_user($form_id, $user_id, $filter, $db_first = false)
    {
        return $this->get_data($form_id, $filter, true, $user_id, $db_first);
    }

    /**
     * Get the avatar of the current user
     *
     * @param int $user_id
     * 
     * @retval string
     *  The avatar image of the current user or empty string.
     */
    public function get_avatar($user_id)
    {
        $form_id = $this->get_dataTable_id_by_displayName('avatar');
        if ($form_id) {
            $avatar = $this->get_data_for_user($form_id, $user_id, '', true);
            return $avatar ? $avatar['avatar'] : '';
        } else {
            return '';
        }
    }

    /**
     * Save data in dataTables THE UPDATED DATA CAN BE ONLY OWN DATA.
     * @param string $transaction_by
     * What initialized the transaction
     * @param string $table_name
     * The table name where we want to save the data
     * @param array $data
     * The data that we want to save - associative array which contains "name of the column" => "value of the column"
     * @param array|null $updateBasedOn
     * Optional parameter to specify the field name for updating the record instead of inserting.     
     * @return array | false
     * return array with the result containing result and message
     */
    public function save_data($transaction_by, $table_name, $data, $updateBasedOn = null)
    {
        try {
            $this->db->begin_transaction();
            $res = true;
            $id_users = null;
            if (!$this->isAssoc($data)) {
                foreach ($data as $key => $row) {
                    if (isset($row['id_users'])) {
                        if ($id_users == null) {
                            $id_users = $row['id_users'];
                        } else if ($id_users != $row['id_users']) {
                            // different users in this data set
                            $id_users = 'different_users';
                        }
                    }
                    $id_actionTriggerTypes =  $this->get_trigger_type_id($row);
                    $res = $res && $this->save_row($transaction_by, $table_name, $row, $id_actionTriggerTypes);
                }
            } else {
                $id_actionTriggerTypes =  $this->get_trigger_type_id($data);
                $res = $this->save_row($transaction_by, $table_name, $data, $id_actionTriggerTypes, $updateBasedOn);
            }

            /**************** Check jobs ***************************************/
            $form_fields = array();
            if ($this->isAssoc($data)) {
                $form_fields = $data;
            } else if ($id_users && $id_users != 'different_users') {
                // if it is not associative array then it is a multi insert, just notify and dont send all rows, we cannot use them
                // but if all rows are for the same user we can send the user
                $form_fields['id_users']  = $id_users;
            }
            $form_fields[ENTRY_RECORD_ID] = $res;
            $form_data = array(
                "trigger_type" => isset($data['trigger_type']) ? $data['trigger_type'] : actionTriggerTypes_finished,
                "form_name" => $table_name,
                "form_id" => $this->get_dataTable_id($table_name),
                "form_fields" => $form_fields
            );
            /**************** Check jobs ***************************************/
            $this->db->commit();
            $this->queue_job_from_actions($form_data);
            return $res;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Exception caught: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get the form field id
     * @param int $field_id
     * the section_id of the field
     * @return string the field name
     */
    public function get_form_field_name($field_id)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $field_id, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT content
                FROM sections s
                INNER JOIN sections_fields_translation sft ON (s.id = sft.id_sections)
                WHERE sft.id_fields = 57 AND id = :id";
            $res = $this->db->query_db_first($sql, array(
                ":id" => $field_id,
            ));
            $res = $res ? $res["content"] : false;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the label of a form field from the database if available.
     *
     * @param int $id_section
     *  The section id of the form field from which the label will be fetched.
     * @return string
     *  The label of the form field or the empty string if the label is not
     *  available.
     */
    public function get_field_label($id_section)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id_section, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT sft.content
                    FROM sections_fields_translation AS sft
                    LEFT JOIN fields AS f ON f.id = sft.id_fields
                    WHERE f.name = 'label' AND sft.id_sections = :id";
            $label = $this->db->query_db_first(
                $sql,
                array(":id" => $id_section)
            );
            $res = $label ? $label["content"] : "";
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the style of a form field from the database if available.
     *
     * @param int $id_section
     *  The section id of the form field from which the style will be fetched.
     * @return string
     *  The style of the form field or the empty string if the style is not
     *  available.
     */
    public function get_field_style($id_section)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id_section, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT st.name FROM styles AS st
            LEFT JOIN sections AS s ON s.id_styles = st.id
            WHERE s.id = :id";
            $style = $this->db->query_db_first(
                $sql,
                array(":id" => $id_section)
            );
            $res = $style ? $style["name"] : "";
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Check if any job should be queued based on the registered actions
     * @param array $form_data
     * The form data
     * @return string
     *  log text what jobs were scheduled;
     */
    public function queue_job_from_actions($form_data)
    {
        try {
            $this->db->begin_transaction();
            $result = array(
                "form_data" => $form_data
            );
            //get all actions for this form and trigger type
            $start_time = microtime(true);
            $start_date = date("Y-m-d H:i:s");
            $actions = $this->get_actions($form_data['form_id'], $form_data['trigger_type']);
            $id_users = isset($form_data['form_fields']['id_users']) ? $form_data['form_fields']['id_users'] : $_SESSION['id_user']; // the user could be set from the form, this happens with external forms
            foreach ($actions as $action) {
                $not_modified_action = array_slice($action, 0); //create a copy of the original action
                $not_modified_action['config'] = json_decode($not_modified_action['config'], true);

                /*************************  CHECK DYNAMIC DATA *********************************************/
                if ($form_data['trigger_type'] == actionTriggerTypes_finished) {
                    // when the trigger is finished, we have data and we can use it
                    $check_config = $this->check_config($action['config'], $form_data['form_fields']);
                    $result['check_config'] = $check_config['result'];
                    $action['config'] = $check_config['config'];
                }
                /*************************  CHECK DYNAMIC DATA *********************************************/

                //check action condition
                $action['config'] = json_decode($action['config'], true);
                if (isset($action['config']['condition']["jsonLogic"]) && !$this->condition->compute_condition($action['config']['condition']["jsonLogic"], $id_users)['result']) {
                    $result['condition'] = "Action condition is not met";
                    continue;
                }

                if (
                    $form_data['trigger_type'] == actionTriggerTypes_finished && isset($form_data['form_fields'][ENTRY_RECORD_ID]) &&
                    isset($action['config'][ACTION_DELETE_SCHEDULED]) && $action['config'][ACTION_DELETE_SCHEDULED]
                ) {
                    // if the trigger type is finished and the record id exists, check for scheduled jobs with that record and move them to status deleted
                    $result['deleted_jobs'] = $this->delete_jobs_for_record_and_action($action['id'], $form_data['form_fields'][ENTRY_RECORD_ID]);
                }


                $users = array();

                /*************************  TARGET_GROUPS **************************************************/
                if (isset($action['config'][ACTION_TARGET_GROUPS]) && $action['config'][ACTION_TARGET_GROUPS]) {
                    // the jobs will be for groups, we have to add all the users from these groups
                    $users_from_groups = $this->get_users_from_groups($action['config'][ACTION_SELECTED_TARGET_GROUPS]);
                    if ($users_from_groups) {
                        foreach ($users_from_groups as $key => $user) {
                            array_push($users, $user['id']);
                        }
                        $users = array_unique($users);
                    }
                } else {
                    array_push($users, $id_users);
                }
                /*************************  TARGET_GROUPS **************************************************/

                /*************************  REPEAT *********************************************************/

                $repeat = isset($action['config'][ACTION_REPEATER][ACTION_REPEATER_OCCURRENCES]) ? $action['config'][ACTION_REPEATER][ACTION_REPEATER_OCCURRENCES] : 1;
                $executed_blocks = array();
                $blocks_not_executed_yet = $action['config']['blocks'];
                if (isset($action['config'][ACTION_REPEATER_UNTIL_DATE])) {
                    /*************************  REPEAT UNTIL *********************************************************/
                    // calculate how many repetitions can be scheduled based on the config
                    $repeat = $this->calculateScheduledDatesRepeaterUntil($action['config'][ACTION_REPEATER_UNTIL_DATE]);
                    $repeat = count($repeat);
                    /*************************  REPEAT UNTIL *********************************************************/
                }
                for ($repeat_index = 0; $repeat_index < $repeat; $repeat_index++) {
                    $action['repeat_index'] = $repeat_index;

                    /*************************  RANDOMIZE ******************************************************/
                    if (isset($action['config'][ACTION_RANDOMIZE]) && $action['config'][ACTION_RANDOMIZE]) {
                        if ($action['config'][ACTION_RANDOMIZER][ACTION_RANDOMIZER_EVEN_PRESENTATION]) {
                            // Filter the blocks that should be executed in order that their count is even
                            $min_randomization_count = PHP_INT_MAX;
                            $blocks_not_executed_yet = array();
                            $index = 0;
                            foreach ($action['config']['blocks'] as $key => $block) {
                                $action['config']['blocks'][$key]['index'] = $index;
                                if ($block[ACTION_BLOCK_RANDOMIZATION_COUNT] < $min_randomization_count) {
                                    // new minimum count found, clear previous objects
                                    $min_randomization_count = $block[ACTION_BLOCK_RANDOMIZATION_COUNT];
                                    $blocks_not_executed_yet = array($action['config']['blocks'][$key]);
                                } elseif ($block[ACTION_BLOCK_RANDOMIZATION_COUNT] == $min_randomization_count) {
                                    // same minimum count, add to list of objects
                                    $blocks_not_executed_yet[] = $action['config']['blocks'][$key];
                                }
                                $index++;
                            }
                        }
                        shuffle($blocks_not_executed_yet); // randomize the blocks
                        array_splice($blocks_not_executed_yet, $action['config'][ACTION_RANDOMIZER][ACTION_RANDOMIZER_RANDOM_ELEMENTS]); // keep only the number of the elements that we want to present
                        $updated_result = $this->update_randomization_count($action, $not_modified_action, $blocks_not_executed_yet);
                        $action = $updated_result['action'];
                        $not_modified_action = $updated_result['not_modified_action'];
                    }

                    /*************************  RANDOMIZE ******************************************************/

                    foreach ($blocks_not_executed_yet as $block_index => $block) {
                        $curr_block = array(
                            "block_name" => $block['block_name'],
                            "jobs" => array()
                        );

                        if (isset($block['condition']["jsonLogic"]) && !$this->condition->compute_condition($block['condition']["jsonLogic"], $id_users)['result']) {
                            $curr_block['condition'] = "Block condition is not met";
                        } else {

                            foreach ($block['jobs'] as $job_index => $job) {
                                if (isset($job['condition']["jsonLogic"]) && !$this->condition->compute_condition($job['condition']["jsonLogic"], $id_users)['result']) {
                                    $curr_block['jobs'][] = array(
                                        "job_name" => $job['job_name'],
                                        "condition" => 'Job condition is not met'
                                    );
                                } else {
                                    $scheduling_result = null;
                                    $job_type = $this->get_job_type($job);
                                    if ($job_type == jobTypes_task) {
                                        $scheduling_result = $this->queue_task($users, $job, $action, $form_data);
                                    } else if ($job_type == jobTypes_notification) {
                                        if ($job['notification']['notification_types'] == notificationTypes_email) {
                                            // the notification type is email                        
                                            $scheduling_result = $this->queue_mail($users, $job, $action, $form_data);
                                        } else if ($job['notification']['notification_types'] == notificationTypes_push_notification) {
                                            // the notification type is push notification                        
                                            $scheduling_result = $this->queue_notification($users, $job, $action, $form_data);
                                        }
                                    }
                                    $curr_block['jobs'][] = $scheduling_result;
                                    $scheduling_keys = array_keys($scheduling_result);
                                    if (reset($scheduling_keys)) {
                                        $scheduledJobData = array(
                                            "id_scheduledJobs" => reset($scheduling_keys),
                                            "id_formActions" => $action['id'],
                                            "id_dataRows" => $form_data['form_fields'][ENTRY_RECORD_ID]
                                        );
                                        $this->db->insert('scheduledJobs_formActions', $scheduledJobData);
                                    }
                                }
                            }
                        }
                        $executed_blocks[] = $curr_block;
                    }
                    $result['executed_blocks'] = array_merge(isset($result['executed_blocks']) ? $result['executed_blocks'] : array(),  $executed_blocks);
                }
            }

            if ($form_data['trigger_type'] == actionTriggerTypes_finished) {
                // only check to delete the reminder if the survey is finished
                $scheduled_reminders = $this->get_scheduled_reminders_for_delete($form_data);
                if ($scheduled_reminders && count($scheduled_reminders) > 0) {
                    $this->delete_reminders($scheduled_reminders);
                    $result['scheduled_reminders_for_delete'] = $scheduled_reminders;
                }
            }

            if ($form_data['trigger_type'] == actionTriggerTypes_deleted) {
                // if the trigger type is deleted and the record id exists, check for scheduled jobs with that record and move them to status deleted
                $result['deleted_jobs'] = $this->delete_jobs_for_record($form_data['form_fields'][ENTRY_RECORD_ID]);
            }

            $end_time = microtime(true);
            $result['time'] = array(
                "start_date" => $start_date,
                "exec_time" => $end_time - $start_time
            );
            $this->transaction->add_transaction(
                transactionTypes_insert,
                transactionBy_by_user,
                $id_users,
                $this->transaction::TABLE_dataTables,
                $form_data['form_id'],
                false,
                $result
            );
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Set the condition service
     */
    public function setConditionService($condition)
    {
        $this->condition = $condition;
    }

    /**
     * Set the job_scheduler service
     */
    public function setJobSchedulerService($job_scheduler)
    {
        $this->job_scheduler = $job_scheduler;
    }

    /**
     * Get form values in an associative array with the key the name of the field
     * @param array $fields
     * the fields fo the form
     * @return array
     * Return an associative array with the form values 
     */
    public function get_form_values($fields)
    {
        $form_values = array();
        //prepare the values based on what type of form they come
        foreach ($fields as $field_name => $field) {
            if (isset($field['value'])) {
                // it is a form field
                $form_values[$field_name] = $field['value'];
            } else {
                // it is from external
                $form_values[$field_name] = $field;
            }
        }
        return $form_values;
    }

    /**
     * Delete scheduled jobs associated with a record.
     *     
     * @param int $action_id The ID of the action.
     * @param int $record_id The ID of the record.
     * @return array Array with the ids of the deleted jobs
     */
    private function delete_jobs_for_record_and_action($action_id, $record_id)
    {
        $job_status_deleted = $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_deleted);
        $job_status_queued = $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued);
        $sql = 'SELECT id
        FROM scheduledJobs sj
        INNER JOIN scheduledJobs_formActions sjfa ON (sj.id = sjfa.id_scheduledJobs)
        WHERE sjfa.id_formActions = :action_id AND id_jobStatus = :job_status_queued AND sjfa.id_dataRows = :record_id';
        $jobs_ids = $this->db->query_db($sql, array(
            ":action_id" => $action_id,
            ":job_status_queued" => $job_status_queued,
            ":record_id" => $record_id
        ));
        foreach ($jobs_ids as $key => $value) {
            $this->transaction->add_transaction(
                transactionTypes_delete,
                transactionBy_by_system,
                $_SESSION['id_user'],
                $this->transaction::TABLE_SCHEDULED_JOBS,
                $value['id'],
                false
            );
            $this->db->update_by_ids(
                "scheduledJobs",
                array(
                    "id_jobStatus" => $job_status_deleted
                ),
                array('id' => $value['id'])
            );
        }
        return $jobs_ids;
    }

    /**
     * Delete scheduled jobs associated with a record.
     *
     * @param int $record_id The ID of the record.
     * @return array Array with the ids of the deleted jobs
     */
    private function delete_jobs_for_record($record_id)
    {
        $job_status_deleted = $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_deleted);
        $job_status_queued = $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued);
        $sql = 'SELECT id
        FROM scheduledJobs sj
        INNER JOIN scheduledJobs_formActions sjfa ON (sj.id = sjfa.id_scheduledJobs)
        WHERE id_jobStatus = :job_status_queued AND sjfa.id_dataRows = :record_id';
        $jobs_ids = $this->db->query_db($sql, array(
            ":job_status_queued" => $job_status_queued,
            ":record_id" => $record_id
        ));
        foreach ($jobs_ids as $key => $value) {
            $this->transaction->add_transaction(
                transactionTypes_delete,
                transactionBy_by_system,
                $_SESSION['id_user'],
                $this->transaction::TABLE_SCHEDULED_JOBS,
                $value['id'],
                false
            );
            $this->db->update_by_ids(
                "scheduledJobs",
                array(
                    "id_jobStatus" => $job_status_deleted
                ),
                array('id' => $value['id'])
            );
        }
        return $jobs_ids;
    }

    /**
     * Calculate scheduled dates based on the provided repeater_until_date settings.
     *
     * This function calculates the scheduled dates for recurring events or jobs based on the provided settings.
     * The settings include the deadline date, the repeat interval, the frequency (day, week, or month), 
     * and optional parameters for specific days of the week or month. The calculated dates are stored 
     * in an array and returned.
     *
     * @param array $repeater_until_date The repeater_until_date settings containing the deadline, repeat interval,
     *                                   frequency, days of the week, and days of the month.
     *                                   Example:
     *                                   [
     *                                       'deadline' => '2024-05-25 19:00',
     *                                       'repeat_every' => 1,
     *                                       'frequency' => 'month',
     *                                       'days_of_month' => [6, 16, 17, 22, 30]
     *                                   ]
     * @return array An array containing the scheduled dates in the format 'Y-m-d H:i:s'.
     */
    function calculateScheduledDatesRepeaterUntil($repeater_until_date)
    {
        // Extract information from the repeater_until_date object
        $deadline = new DateTime($repeater_until_date[ACTION_REPEATER_UNTIL_DATE_DEADLINE]);
        $repeat_every = $repeater_until_date[ACTION_REPEATER_UNTIL_DATE_REPEAT_EVERY];
        $frequency = $repeater_until_date[ACTION_REPEATER_FREQUENCY];
        $days_of_week = $repeater_until_date[ACTION_REPEATER_DAYS_OF_WEEK] ?? [];
        $days_of_month = $repeater_until_date[ACTION_REPEATER_DAYS_OF_MONTH] ?? [];

        // Initialize an array to store scheduled dates
        $scheduled_dates = array();

        // Calculate the number of days between now and the deadline
        $current_date = new DateTime();
        $current_date =  new DateTime($current_date->format('Y-m-d H:i:s'));
        $schedule_at  = $repeater_until_date[ACTION_REPEATER_UNTIL_DATE_SCHEDULE_AT] && $repeater_until_date[ACTION_REPEATER_UNTIL_DATE_SCHEDULE_AT] != '' ? $repeater_until_date[ACTION_REPEATER_UNTIL_DATE_SCHEDULE_AT] : ($current_date->format('H:i:s'));
        $interval = $current_date->diff($deadline);


        // Calculate the scheduled dates based on the repeat_every and frequency
        switch ($frequency) {
            case 'day':
                $interval = new DateInterval("P{$repeat_every}D");
                $next_date = new DateTime();
                $next_date = new DateTime($next_date->format('Y-m-d') . ' ' . $schedule_at);
                if ($next_date >= $current_date) {
                    // Add only if the time has not passed for the first date
                    $scheduled_dates[] = $next_date->format('Y-m-d') . ' ' . $schedule_at;
                }
                $next_date->add($interval);
                while ($next_date <= $deadline) {
                    $scheduled_dates[] = $next_date->format('Y-m-d') . ' ' . $schedule_at;
                    $next_date->add($interval);
                }
                break;
            case 'week':
                // Check if daysOfWeek is empty, use current day of the week if so
                if (empty($days_of_week)) {
                    $days_of_week = array($current_date->format('l'));
                }
                // Convert days of week to lowercase for case-insensitive comparison
                $days_of_week = array_map('strtolower', $days_of_week);
                // Calculate the scheduled dates based on the specified weekdays
                $next_date = clone $current_date;
                $next_date = new DateTime($next_date->format('Y-m-d') . ' ' . $schedule_at);
                while ($next_date <= $deadline) {
                    // Find the next occurrence of any of the specified weekdays
                    foreach ($days_of_week as $day) {
                        if (strtolower($next_date->format('l')) === strtolower($day)) {
                            // Add the weekday if it's within the deadline
                            if ($next_date <= $deadline && $next_date >= $current_date) {
                                $scheduled_dates[] = $next_date->format('Y-m-d') . ' ' . $schedule_at;
                            }
                            break;
                        }
                    }
                    $next_date->modify('+1 day'); // Move to the next day
                }
                break;
            case 'month':
                // Check if daysOfMonth is empty, use current day of the month if so
                if (empty($days_of_month)) {
                    $days_of_month = array((int)$current_date->format('j'));
                }
                foreach ($days_of_month as $day) {
                    $next_date = new DateTime($deadline->format('Y-m-') . sprintf('%02d', $day));
                    $next_date = new DateTime($next_date->format('Y-m-d') . ' ' . $schedule_at);
                    while ($next_date <= $deadline) {
                        if ($next_date >= $current_date) {
                            $scheduled_dates[] = $next_date->format('Y-m-d H:i:s');
                        }
                        $next_date->modify('+1 month');
                    }
                }
                break;
        }

        return $scheduled_dates;
    }

    /**
     * Get all the dataTables
     * @return array
     *  As array of items where each item has the following keys:
     *   - 'id':    dataTable id
     *   - 'name':  dataTable name
     *   - 'timestamp': when the table was created
     *   - 'value': same as id, used for dropdowns
     *   - 'text': same as name, used for dropdowns 
     */
    public function get_dataTables()
    {
        return $this->db->select_table('view_dataTables');
    }

    /**
     * Retrieves the trigger type ID based on the provided form data.
     *
     * This function checks the 'trigger_type' field in the provided form data array.
     * If the field is not set, it defaults to `actionTriggerTypes_finished`. It also 
     * validates that the trigger type is one of the supported types (`actionTriggerTypes_started`, 
     * `actionTriggerTypes_updated`, `actionTriggerTypes_deleted`, `actionTriggerTypes_finished`). 
     * If the trigger type is not supported, it defaults to `actionTriggerTypes_finished`.
     *
     * @param array $formData
     *  The form data array containing the 'trigger_type' field.
     *
     * @return int
     *  The ID corresponding to the trigger type.
     */
    public function get_trigger_type_id($formData)
    {
        $trigger_type = isset($formData['trigger_type']) ? $formData['trigger_type'] : actionTriggerTypes_finished; // if no trigger type is set, set the finished one
        if (!in_array($formData['trigger_type'], [actionTriggerTypes_started, actionTriggerTypes_updated, actionTriggerTypes_deleted, actionTriggerTypes_finished])) {
            $trigger_type = actionTriggerTypes_finished; // if the trigger_type is not in the supported list set it to finished
        }
        return $this->db->get_lookup_id_by_value(actionTriggerTypes, $trigger_type);
    }

    /**
     * Get the the name of the table
     * @param int $id
     * The id of the table     
     * @return string | false
     * False or the name of the table
     */
    public function get_dataTable_name($id)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT `name`
                FROM dataTables
                WHERE `id` = :id';
            $res = $this->db->query_db_first($sql, array(":id" => $id));
            $res = $res ? $res['name'] : false;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the the display name of the table
     * @param int $id
     * The id of the table     
     * @return string | false
     * False or the name of the table
     */
    public function get_dataTable_displayName($id)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT `name`
                FROM view_dataTables
                WHERE `id` = :id';
            $res = $this->db->query_db_first($sql, array(":id" => $id));
            $res = $res ? $res['name'] : false;
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }
}
?>
