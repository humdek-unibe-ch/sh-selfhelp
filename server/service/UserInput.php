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
     * The collection of input field attributes. See UserInput::set_field_attrs.
     */
    private $field_attrs = NULL;

    /**
     * Array that contains the ui preference settings for the user
     */
    private $ui_pref;

    /**
     * The condition service instance to handle conditional logic.
     */
    private $condition;

    /**
     * The JobSheduler service instance to handle jobs scheduling and execution.
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
     * Fetches all user input fields from the database given certain conditions.
     *
     * @param array $conds
     *  A key => value array of db conditions where the key corresponds to the
     *  db column and the value to the db value.
     * @param boolean $get_page_info
     * If true it fetch the info for the page and nav 
     * @return array
     *  An array of field items where eeach item has the following keys:
     *  - 'id'            A unique id of the field
     *  - 'user_code'     A unique string that connects values to a user without
     *                    revealing the identity of the user.
     *  - 'user_gender'   The gender of the user.
     *  - 'page'          The keyword of the page where the data was entered.
     *  - 'nav'           The name of the navigation section where the data was
     *                    entered.
     *  - 'field_name'    The name of the input field.
     *  - 'field_label'   The label of the input field.
     *  - 'field_type'    The type of the input field. This is either the name
     *                    of the form field style or if the style is 'input' the
     *                    input type.
     *  - 'value'         The value that was entered by the user.
     *  - 'timestamp'     The date and time when the value was entered.
     *  - 'id_user_input_record' The new field that keep the rows
     */
    private function fetch_input_fields($conds = array(), $get_page_info = false)
    {
        // rework
        if (!isset($conds['record.id_sections']))
            $field_attrs = $this->get_field_attrs(-1, $get_page_info);
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, json_encode($conds), [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        $fields_db = array();
        $gender = $this->db->query_db_first('SELECT `name` FROM genders WHERE id = :id', array(":id" => $_SESSION['gender']))['name'];
        $language = $this->db->query_db_first('SELECT locale FROM languages WHERE id = :id', array(":id" => $_SESSION['language']))['locale'];
        if ($get_result !== false) {
            $fields_db = $get_result;
        } else {
            $sql = "SELECT ui.id, ui.id_users, ui.value, ui.edit_time, ui.id_sections,
                    g.`name` AS gender, vc.code, id_user_input_record
                    FROM user_input AS ui
                    LEFT JOIN user_input_record record  ON (ui.id_user_input_record = record.id)
                    LEFT JOIN users AS u ON u.id = ui.id_users
                    LEFT JOIN genders AS g ON g.id = u.id_genders
                    LEFT JOIN validation_codes AS vc on vc.id_users = ui.id_users
                    WHERE 1";
            foreach ($conds as $k => $value) {
                if ($k === "g.name") {
                    $gender = $value;
                };
                $sql .= " AND " . $k . " = '" . $value . "'";
            }
            $fields_db = $this->db->query_db($sql);
            $this->db->get_cache()->set($key, $fields_db);
        }

        $fields = array();
        foreach ($fields_db as $field) {
            $id = intval($field["id_sections"]);
            if (isset($conds['record.id_sections'])) {
                $field_attrs = $this->get_field_attrs($id, $get_page_info);
            }
            if (!isset($field_attrs[$id])) continue;
            $field_label = $field_attrs[$id]["label"][$gender][$language] ?? "";
            if ($gender === "female" && $field_label === "")
                $field_label = $field_attrs[$id]["label"]["male"][$language] ?? "";
            $fields[] = array(
                "id" => $field['id'],
                "user_code" => $field['code'],
                "user_gender" => $field['gender'],
                "page" => $field_attrs[$id]["page"],
                "nav" => $field_attrs[$id]["nav"],
                "field_name" => $field_attrs[$id]["name"],
                "field_label" => $field_label,
                "field_type" => $field_attrs[$id]["type"],
                "form_name" => $field_attrs[$id]["form_name"],
                "value" => $field["value"],
                "timestamp" => $field["edit_time"],
                "id_user_input_record" => $field["id_user_input_record"],
            );
        }
        return $fields;
    }

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
     * Get all actions for a form and a trigger_type
     *
     * @param string $id_forms
     *  form id
     * @param string $form_type
     * Internal or external
     * @param string $trigger_type
     *  trigger type
     *  @retval array
     * return all actions for that survey with this trigger_type
     */
    private function get_actions($id_forms, $form_type, $trigger_type)
    {
        $sqlGetActions = "SELECT * 
                          FROM view_formActions
                          WHERE id_forms = :id_forms AND trigger_type = :trigger_type;";
        return $this->db->query_db(
            $sqlGetActions,
            array(
                "id_forms" => (int)$id_forms . '-' . $form_type,
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
        if ($reminder['schedule_time']['parent_job_type_hidden'] == ACTION_JOB_TYPE_NOTIFICATION_WITH_REMINDER_FOR_DIARY) {
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
            $start_date = $this->calc_date_to_be_sent($job['schedule_time']);
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
        } else {
            return $this->calc_date_to_be_sent($job['schedule_time']);
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
        $sql = '';
        if ($form_data['form_type'] == FORM_INTERNAL) {
            $sql = 'SELECT id_scheduledJobs 
                    FROM view_scheduledJobs_reminders 
                    WHERE `id_users` = :uid AND id_forms_INTERNAL = :form_id AND job_status_code = :status
                    AND (session_end_date IS NULL OR (NOW() BETWEEN session_start_date AND session_end_date))';
        } else if ($form_data['form_type'] == FORM_EXTERNAL) {
            $sql = 'SELECT id_scheduledJobs 
                    FROM view_scheduledJobs_reminders 
                    WHERE `id_users` = :uid AND id_forms_EXTERNAL = :form_id AND job_status_code = :status
                    AND (session_end_date IS NULL OR (NOW() BETWEEN session_start_date AND session_end_date))';
        }
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
    private function get_uploadTable_columnId($col_name, $table_id)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, $col_name, [__FUNCTION__, $table_id]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $res = $this->db->query_db_first("SELECT id FROM uploadCols WHERE `name` = :col_name AND id_uploadTables = :table_id", array(":col_name" => $col_name, ":table_id" => $table_id));
            $res = $res ? $res['id'] : '';
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the columns needed for the upload table and insert them if they do not exists
     * @param int $id_table
     * The uploadTable id
     * @param object $data
     * The data for the row. Based on it we will get the needed columns
     * @return array
     * Return array with the column name and the column id
     */
    private function get_columns_for_upload_table($id_table, $data)
    {
        $col_ids = array();
        foreach ($data as $col_name => $value) {
            $id_col = $this->get_uploadTable_columnId($col_name, $id_table);
            if (!$id_col) {
                // it does not exist, create it
                $id_col = $this->db->insert("uploadCols", array(
                    "name" => $col_name,
                    "id_uploadTables" => $id_table
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
     * Update the record in the upload table
     * @param int $id_table
     * The upload table id
     * @param array $record
     * The record that already exists
     * @param string $transaction_by
     * Who initiated the update
     * @param array $data
     * the new data that will update the old one
     * @return bool
     * Return the success of the update
     */
    private function update_external_data($id_table, $record, $transaction_by, $data)
    {
        $col_ids = $this->get_columns_for_upload_table($id_table, $data);
        $res = $this->db->execute_update_db(
            "UPDATE uploadRows SET `timestamp` = NOW(), id_users = :id_users WHERE id = :id;",
            array(
                ':id' => $record[ENTRY_RECORD_ID],
                ":id_users" => $data['id_users']
            )
        ) !== false; //update the timestamp of the row
        unset($data['id_users']); //once used - remove it
        foreach ($data as $key => $value) {
            // if it has a value it will be updated if it is not created yet it will be inserted
            $current_res = $this->db->insert('uploadCells', array('id_uploadRows' => $record[ENTRY_RECORD_ID], "id_uploadCols" => $col_ids[$key], "value" => $value), array("value" => $value));
            $res = $res && $current_res;
        }
        if ($res) {
            $this->transaction->add_transaction(transactionTypes_update, $transaction_by, null, $this->transaction::TABLE_uploadTables, $id_table);
        }
        return $res;
    }

    /**
     * @param string $transaction_by
     * Save static data in the upload_tables structure
     * What initialized the transaction
     * @param string $table_name
     * The table name where we want to save the data
     * @param array $data
     * The data that we want to save - associative array which contains "name of the column" => "value of the column"
     * @return bool
     */
    private function save_external_row($transaction_by, $table_name, $data, $updateBasedOn = null)
    {
        if (!isset($data['id_users'])) {
            $data['id_users'] = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 1; // if not set in the session use the guest user
        }

        /******************* SET TABLE *********************************/
        $id_table = $this->get_form_id($table_name, FORM_EXTERNAL);
        if (!$id_table) {
            // does not exists yet; try to create it
            $id_table = $this->db->insert("uploadTables", array(
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
                FORM_EXTERNAL,
                $data['id_users'],
                true
            );
            if ($record) {
                // the record exists, do not insert it, update it
                $res = $this->update_external_data($id_table, $record, $transaction_by, $data);
                return $res ? $record[ENTRY_RECORD_ID] : $res;
            }
        }
        /******************* SET TABLE *********************************/
        if (!$id_table) {
            return false;
        } else {
            if ($this->transaction->add_transaction(transactionTypes_insert, $transaction_by, null, $this->transaction::TABLE_uploadTables, $id_table) === false) {
                return false;
            }

            /******************* SET COLUMNS *********************************/

            $col_ids = $this->get_columns_for_upload_table($id_table, $data);

            /******************* SET COLUMNS *********************************/

            /******************* SET ROW     *********************************/
            $id_row = $this->db->insert("uploadRows", array(
                "id_uploadTables" => $id_table,
                "id_users" => $data['id_users']
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
                "uploadCells",
                array(
                    "id_uploadRows",
                    "id_uploadCols",
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
     * Get all input fields given a filter
     *
     * @param array $filter
     *  The filter array can be empty or have any of the following keys:
     *   - 'id'           Selects a field with a given id.
     *   - 'gender'       This can either be set to 'male' or 'female'.
     *   - 'field_name'   Selects all fields with the given name.
     *   - 'form_name'    Selects all fields from the given form name.
     *   - 'page'         Selects all fields on a given page.
     *   - 'nav'          Selects all fields in a given navigation sections.
     *   - 'id_section'   Selects all fields with given section id.
     *   - 'id_user'      Selects all fields from a given user id.
     *   - 'removed'      Selects all fields matching the removed flag
     *   - 'form_id'      Filter the form id
     * @param boolean $get_page_info
     * If true it fetch the info for the page and nav 
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields($filter = array(), $get_page_info = false)
    {
        // rework
        $db_cond = array();
        if (isset($filter["gender"]))
            $db_cond["g.name"] = $filter["gender"];
        if (isset($filter["id_section"]))
            $db_cond["ui.id_sections"] = $filter["id_section"];
        if (isset($filter["form_id"])) {
            $db_cond["record.id_sections"] = $filter["form_id"];
        }
        if (isset($filter["id_user"]))
            $db_cond["ui.id_users"] = $filter["id_user"];
        if (isset($filter["id"]))
            $db_cond["ui.id"] = $filter["id"];
        if (isset($filter["removed"]))
            $db_cond["ui.removed"] = $filter["removed"] ? '1' : '0';
        if (isset($filter["form_name"]))
            $db_cond["record.id_sections"] = $this->get_form_id($filter["form_name"]);
        $fields_all = $this->fetch_input_fields($db_cond, $get_page_info);
        $fields = array();
        foreach ($fields_all as $field)
            if ((!isset($filter["field_name"]) || (isset($filter["field_name"])
                    && $field['field_name'] === $filter["field_name"]))
                && (!isset($filter["page"]) || (isset($filter["page"])
                    && $field['page'] === $filter["page"]))
                && (!isset($filter["nav"]) || (isset($filter["nav"])
                    && strpos($field['nav'], $filter["nav"]) !== false))
            )
                $fields[] = $field;
        return $fields;
    }

    /**
     * Get all input fields submitted by male users.
     *
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_gender_male()
    {
        return $this->get_input_fields(array("gender" => "male"));
    }

    /**
     * Get all input fields submitted by female users.
     *
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_gender_female()
    {
        return $this->get_input_fields(array("gender" => "female"));
    }

    /**
     * Get all input fields that match a field section id.
     *
     * @param int $field_id
     *  The field_id to match.
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_field_id($field_id)
    {
        return $this->get_input_fields(array("id_sections" => $field_id));
    }

    /**
     * Get all input fields that match a field name.
     *
     * @param string $field_name
     *  The field_name to match.
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_field_name($field_name)
    {
        return $this->get_input_fields(array("field_name" => $field_name));
    }

    /**
     * Get all input fields that are placed on a given page.
     *
     * @param string $keyword
     *  The page keyword to match.
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_page($keyword)
    {
        return $this->get_input_fields(array("page" => $keyword));
    }

    /**
     * Get all input fields that are placed on a given navigation section.
     *
     * @param string $name
     *  The navigation section name to match. All navigation sections containing
     *  the given name are considered.
     * @return array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_nav($name)
    {
        return $this->get_input_fields(array("nav" => $name));
    }

    /**
     * Get the user input value of an input field specified by a pattern.
     *
     * @param string $pattern
     *  A field identifier of the form `@<form_name>#<field_name>`.
     * @param int $uid
     *  The id of a user.
     * @return mixed
     *  On success, the value corresponding to the requested form field, null in
     *  case of a bad pattern syntax, and the empty string if no value was found.
     */
    public function get_input_value_by_pattern($pattern, $uid)
    {
        $names = explode('#', $pattern);
        if (count($names) !== 2)
            return null;

        $form = substr($names[0], 1);
        $field = $names[1];
        $vals = $this->get_input_fields(array(
            "form_name" => $form,
            "field_name" => $field,
            "id_user" => $uid
        ));
        if (count($vals) > 0)
            return end($vals)['value'];

        return "";
    }

    /**
     * Returns the regular expression to find a form field
     *
     * @return string the regular expression that finds a field identifier of
     * the form `@<form_name>#<field_name>`.
     */
    static public function get_input_value_pattern()
    {
        return '@[^"@#]+#[^"@#]+';
    }

    /**
     * Collect attributes for each existing user input field.
     * The following attributes are set:
     *  - 'page'  The name of the parent page of the field.
     *  - 'nav'   The name of the parent navigation section
     *  - 'name'  The name of the field
     *  - 'type'  The type of the field
     */
    public function set_field_attrs()
    {
        $this->field_attrs = array();
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        $sections = array();
        if ($get_result !== false) {
            $sections = $get_result;
        } else {
            $sql = "SELECT DISTINCT ui.id_sections, sft_it.content AS input_type, sft_in.content AS field_name, st.name AS field_type, sft_if.content AS form_name,
                    sft_il.content AS field_label, g.name AS gender, l.locale AS `language` 
                    FROM user_input AS ui
                    LEFT JOIN user_input_record record ON (ui.id_user_input_record = record.id)
                    LEFT JOIN sections_fields_translation AS sft_it ON sft_it.id_sections = ui.id_sections AND sft_it.id_fields = " . TYPE_INPUT_FIELD_ID . "
                    LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = " . NAME_FIELD_ID . "
                    LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = " . NAME_FIELD_ID . "
                    LEFT JOIN sections_fields_translation AS sft_il ON sft_il.id_sections = ui.id_sections AND sft_il.id_fields = " . LABEL_FIELD_ID . "
                    LEFT JOIN sections AS s ON s.id = ui.id_sections
                    LEFT JOIN styles AS st ON st.id = s.id_styles
                    LEFT JOIN genders AS g ON g.id = sft_il.id_genders
                    LEFT JOIN languages AS l ON l.id = sft_il.id_languages";
            $sections = $this->db->query_db($sql);
            $this->db->get_cache()->set($key, $sections);
        }
        foreach ($sections as $section) {
            $id = intval($section['id_sections']);
            $name = $section['field_name'];
            $label_name = $section['field_label'] ?? $name;
            if (isset($this->field_attrs[$id])) {
                $this->field_attrs[$id]["label"][$section['gender']][$section['language']] = $label_name;
                continue;
            }
            $type = $section['input_type'] ?? $section['field_type'];
            $label = array('male' => array(), 'female' => array());
            $label[$section['gender']][$section['language']] = $label_name;
            $page = $this->find_section_page($id);
            $this->field_attrs[$id] = array(
                "page" => $page["page"],
                "nav" => $page["nav"],
                "name" => $name,
                "label" => $label,
                "form_name" => $section['form_name'],
                "type" => $type,
            );
        }
    }

    /**
     * @param $id
     * the id of the input field section
     * @param boolean $get_page_info
     * If true it fetch the info for the page and nav 
     *@return array
     * Collect attributes for each existing user input field.
     * The following attributes are set:
     *  - 'page'  The name of the parent page of the field.
     *  - 'nav'   The name of the parent navigation section
     *  - 'name'  The name of the field
     *  - 'type'  The type of the field
     */
    public function get_field_attrs($id, $get_page_info = false)
    {
        $field_attrs = array();
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        $sections = array();
        if ($get_result !== false) {
            $sections = $get_result;
        } else {
            $sql = "SELECT DISTINCT ui.id_sections, sft_it.content AS input_type, sft_in.content AS field_name, st.name AS field_type, sft_if.content AS form_name, 
                    sft_il.content AS field_label, g.name AS gender, l.locale AS `language`
                    FROM user_input AS ui
                    LEFT JOIN user_input_record record ON (ui.id_user_input_record = record.id)
                    LEFT JOIN sections_fields_translation AS sft_it ON sft_it.id_sections = ui.id_sections AND sft_it.id_fields = " . TYPE_INPUT_FIELD_ID . "
                    LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = " . NAME_FIELD_ID . "
                    LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = " . NAME_FIELD_ID . "
                    LEFT JOIN sections_fields_translation AS sft_il ON sft_il.id_sections = ui.id_sections AND sft_il.id_fields = " . LABEL_FIELD_ID . "
                    LEFT JOIN sections AS s ON s.id = ui.id_sections
                    LEFT JOIN styles AS st ON st.id = s.id_styles
                    LEFT JOIN genders AS g ON g.id = sft_il.id_genders
                    LEFT JOIN languages AS l ON l.id = sft_il.id_languages
                    WHERE ui.id_sections = :id or :id = -1";
            $sections = $this->db->query_db($sql, array(":id" => $id));
            $this->db->get_cache()->set($key, $sections);
        }
        foreach ($sections as $section) {
            $id = intval($section['id_sections']);
            $name = $section['field_name'];
            $label_name = $section['field_label'] ?? $name;
            if (isset($field_attrs[$id])) {
                $field_attrs[$id]["label"][$section['gender']][$section['language']] = $label_name;
                continue;
            }
            $type = $section['input_type'] ?? $section['field_type'];
            $label = array('male' => array(), 'female' => array());
            $label[$section['gender']][$section['language']] = $label_name;
            if ($get_page_info) {
                $page = $this->find_section_page($id);
            }
            $field_attrs[$id] = array(
                "page" => ($get_page_info ? $page["page"] : ""),
                "nav" => ($get_page_info ? $page["nav"] : ""),
                "name" => $name,
                "label" => $label,
                "form_name" => $section['form_name'],
                "type" => $type,
            );
        }
        return $field_attrs;
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
            $form_id = $this->get_form_id('ui-preferences', FORM_INTERNAL);
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
        $form_id = $this->get_form_id('notification', FORM_INTERNAL);
        if ($form_id) {
            $res = $this->get_data($form_id, '', true, FORM_INTERNAL, $id_users);
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
     * Get the id of the table or the form based on the required type
     * @param string $name
     * The name of the form or table     
     * @param int $form_type
     * Internal or external form, it loads different table based on this value
     * @return int | false
     * the result of the fetched form row
     */
    public function get_form_id($name, $form_type = FORM_INTERNAL)
    {
        // the cache type is like a section, because the form name can be edited only in cms
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_SECTIONS, $name, [__FUNCTION__, $form_type]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            if ($form_type == FORM_INTERNAL) {
                $sql = 'select record.id_sections as id
                from user_input ui
                LEFT JOIN user_input_record record ON (ui.id_user_input_record = record.id)
                inner JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
                where sft_if.content = :name
                limit 0,1;';
            } else if ($form_type == FORM_EXTERNAL) {
                $sql = 'SELECT id 
                FROM uploadTables
                WHERE `name` = :name';
            }
            $res = $this->db->query_db_first($sql, array(":name" => $name));
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
     * If true it loads only records created by the same user. 
     * @param string $form_type
     * Internal or external form, it loads different table based on this value
     * @param int $user_id
     * Show the data for that user
     * @param boolean $db_first
     * If true it returns the first row. 
     * @return array
     * the result of the fetched data
     */
    public function get_data($form_id, $filter, $own_entries_only = true, $form_type = FORM_INTERNAL, $user_id = null, $db_first = false)
    {
        if (strpos($filter, '{{') !== false) {
            $filter = ''; // filter is not correct, tried to be set dynamically but failed
        }
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, $form_id, [__FUNCTION__, $filter, $own_entries_only, $form_type, $user_id, $db_first]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            if (!$user_id) {
                $user_id =  isset($_SESSION['id_user']) ? $_SESSION['id_user'] : -1; // if the user is not defined we set the session user if needed
            }
            if ($form_type == FORM_INTERNAL) {
                $sql = 'CALL get_form_data_for_user_with_filter(:form_id, :user_id, :filter)';
                $params = array(
                    ":form_id" => $form_id,
                    ":user_id" => $user_id
                );
                if (!$own_entries_only) {
                    $sql = 'CALL get_form_data_with_filter(:form_id, :filter)';
                    $params = array(
                        ":form_id" => $form_id
                    );
                }
            } else if ($form_type == FORM_EXTERNAL) {
                if (!$own_entries_only) {
                    $user_id = -1;
                }
                $params = array(
                    ":form_id" => $form_id,
                    ":user_id" => $user_id
                );
                $sql = 'CALL get_uploadTable_with_filter(:form_id, :user_id, :filter)';
            }
            $params[':filter'] = $filter;
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
     * @param string $form_type
     * Internal or external form, it loads different table based on this value
     * @param boolean $db_first
     * If true it returns the first row. 
     * @return array
     * the result of the fetched data
     */
    public function get_data_for_user($form_id, $user_id, $filter, $form_type = FORM_INTERNAL, $db_first = false)
    {
        return $this->get_data($form_id, $filter, true, $form_type, $user_id, $db_first);
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
        $form_id = $this->get_form_id('avatar');
        if ($form_id) {
            $avatar = $this->get_data_for_user($form_id, $user_id, '', FORM_INTERNAL, true);
            return $avatar ? $avatar['avatar'] : '';
        } else {
            return '';
        }
    }

    /**
     * Save static data in the upload_tables structure
     * @param string $transaction_by
     * What initialized the transaction
     * @param string $table_name
     * The table name where we want to save the data
     * @param array $data
     * The data that we want to save - associative array which contains "name of the column" => "value of the column"
     * @return array | false
     * return array with the result containing result and message
     */
    public function save_external_data($transaction_by, $table_name, $data, $updateBasedOn = null)
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
                    $res = $res && $this->save_external_row($transaction_by, $table_name, $row);
                }
            } else {
                $res = $this->save_external_row($transaction_by, $table_name, $data, $updateBasedOn);
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
                "form_id" => $this->get_form_id($table_name, FORM_EXTERNAL),
                "form_type" => FORM_EXTERNAL,
                "form_fields" => $form_fields
            );
            /**************** Check jobs ***************************************/
            $this->db->commit();
            $this->queue_job_from_actions($form_data);
            return $res;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get the form field id
     * @param int $field_id
     * the section_id of the field
     * @retval string the fiedl name
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
            $actions = $this->get_actions($form_data['form_id'], $form_data['form_type'], $form_data['trigger_type']);
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
                    $result['deleted_jobs'] = $this->delete_jobs_for_record($form_data['form_type'], $action['id'], $form_data['form_fields'][ENTRY_RECORD_ID]);
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
                                            "id_formActions" => $action['id']
                                        );
                                        if ($form_data['form_type'] == FORM_INTERNAL) {
                                            $scheduledJobData["id_user_input_record"] = $form_data['form_fields'][ENTRY_RECORD_ID];
                                        } else if ($form_data['form_type'] == FORM_EXTERNAL) {
                                            $scheduledJobData["id_uploadRows"] = $form_data['form_fields'][ENTRY_RECORD_ID];
                                        }
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

            $end_time = microtime(true);
            $result['time'] = array(
                "start_date" => $start_date,
                "exec_time" => $end_time - $start_time
            );
            $this->transaction->add_transaction(
                transactionTypes_insert,
                transactionBy_by_user,
                $id_users,
                $form_data['form_type'],
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
     * @param int $form_type The type of form (internal or external).
     * @param int $action_id The ID of the action.
     * @param int $record_id The ID of the record.
     * @return array Array with the ids of the deleted jobs
     */
    public function delete_jobs_for_record($form_type, $action_id, $record_id)
    {
        $job_status_deleted = $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_deleted);
        $job_status_queued = $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued);
        $sql = '';
        if ($form_type == FORM_INTERNAL) {
            $sql = 'SELECT id
            FROM scheduledJobs sj
            INNER JOIN scheduledJobs_formActions sjfa ON (sj.id = sjfa.id_scheduledJobs)
            WHERE sjfa.id_formActions = :action_id AND id_jobStatus = :job_status_queued AND sjfa.id_user_input_record = :record_id';
        } else if (
            $form_type == FORM_EXTERNAL
        ) {
            $sql = 'SELECT id
            FROM scheduledJobs sj
            INNER JOIN scheduledJobs_formActions sjfa ON (sj.id = sjfa.id_scheduledJobs)
            WHERE sjfa.id_formActions = :action_id AND id_jobStatus = :job_status_queued AND sjfa.id_uploadRows = :record_id';
        }
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
}
?>
