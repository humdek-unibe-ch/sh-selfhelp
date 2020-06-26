<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseCallback.php";
require_once __DIR__ . "/../component/moduleQualtricsProject/ModuleQualtricsProjectModel.php";
require_once __DIR__ . "/../component/style/register/RegisterModel.php";

/**
 * A small class that handles callbak and set the group number for validation code
 * calls.
 */
class CallbackQualtrics extends BaseCallback
{

    /* Constants ************************************************/
    const VALIDATION_add_survey_response = 'add_survey_response';
    const VALIDATION_set_group = 'set_group';
    const CALLBACK_NEW = 'callback_new';
    const CALLBACK_ERROR = 'callback_error';
    const CALLBACK_SUCCESS = 'callback_success';

    /* Private Properties *****************************************************/

    /**
     * The instance of the user model from the user component.
     */
    private $register_user_model = null;

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $this->register_user_model = new RegisterModel($services, GUEST_USER_ID);
    }

    /**
     * Get the user id given a user code
     *
     * @param $code
     *  The code for which a user is searched
     * @retval $boolean
     *  The user id on success, -1 on failure
     */
    private function getUserId($code)
    {
        $sql = "select id_users
                from validation_codes
                where code  = :code";
        $res = $this->db->query_db_first($sql, array(':code' => $code));
        return  !isset($res['id_users']) ? -1 : $res['id_users'];
    }

    /**
     * Check if the code exist in validation_codes table
     *
     * @param $code
     *  The code for which a user is searched
     * @retval $boolean
     *  
     */
    private function code_exist($code)
    {
        $sql = "select code
                from validation_codes
                where code  = :code";
        $res = $this->db->query_db_first($sql, array(':code' => $code));
        return  isset($res['code']);
    }

    /**
     * Get the scheduled reminders for the user and this survey
     * @param int $uid 
     * user_id
     * @param string $qualtrics_survey_id
     * qualtrics survey id from Qualtrics
     * @retval array
     * all scheduled reminders
     */
    private function get_scheduled_reminders($uid, $qualtrics_survey_id)
    {
        return $this->db->query_db(
            'SELECT mailQueue_id FROM view_qualtricsReminders WHERE `user_id` = :uid AND qualtrics_survey_id = :sid AND mailQueue_status_code = :status',
            array(
                ":uid" => $uid,
                ":sid" => $qualtrics_survey_id,
                ":status" => mailQueueStatus_queued
            )
        );
    }

    /**
     * Change the status of the queueud mails to deleted
     * @param @array $scheduled_reminders
     * Arra with reminders that should be deleted
     */
    private function delete_reminders($scheduled_reminders)
    {
        foreach ($scheduled_reminders as $reminder) {
            $this->mail->delete_queue_entry($reminder['mailQueue_id'], transactionBy_by_qualtrics_callback);
        }
    }

    /**
     * Add a new user to the DB.
     *
     * @param string $code
     *  The user code.     
     * @retval int
     *  The id of the new user.
     */
    private function insert_new_user($code)
    {
        try {
            $this->db->begin_transaction();
            $uid = $this->register_user_model->register_user_from_qualtrics_callback($code . '@selfhelp.psy.unibe.ch', $code);
            if ($uid === false) {
                $this->db->rollback();
                return false;
            } else {
                if ($this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_qualtrics_callback, null, $this->transaction::TABLE_USERS, $uid) === false) {
                    $this->db->rollback();
                    return false;
                }
            }
            $this->db->commit();
            return $uid;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Add a new user to the DB.
     *
     * @param array $data
     *  the data from the callback.     
     * @param int $uid
     * user id
     * @retval int
     *  The id of the new record.
     */
    private function insert_survey_response($data, $uid)
    {
        return $this->db->insert("qualtricsSurveysResponses", array(
            "id_users" => $uid,
            "id_surveys" => $this->db->query_db_first(
                'SELECT id FROM qualtricsSurveys WHERE qualtrics_survey_id = :qualtrics_survey_id',
                array(":qualtrics_survey_id" => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE])
            )['id'],
            "id_qualtricsProjectActionTriggerTypes" => $this->db->get_lookup_id_by_value(qualtricsProjectActionTriggerTypes, $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]),
            "survey_response_id" => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]
        ));
    }

    /**
     * Get all actions for a survey and a trigger_type
     *
     * @param string $sid
     *  qualtrics survey id
     * @param string $trigger_type
     *  trigger type
     *  @retval array
     * return all actions for that survey with this trigger_type
     */
    private function get_actions($sid, $trigger_type)
    {
        $sqlGetActions = "SELECT *
                FROM view_qualtricsActions
                WHERE qualtrics_survey_id = :sid AND trigger_type = :trigger_type AND action_schedule_type <> 'Nothing'";
        return $this->db->query_db(
            $sqlGetActions,
            array(
                "sid" => $sid,
                "trigger_type" => $trigger_type
            )
        );
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
                INNER JOIN groups g ON g.id = ug.id_groups
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
     * Calculate the date when the email should be sent
     * @param array $schedule_info
     * Schedule info from the action
     * @retval string
     * the date in sting format for MySQL
     */
    private function calc_date_to_be_sent($schedule_info)
    {
        $date_to_be_sent = 'undefined';
        if ($schedule_info[qualtricScheduleTypes] == qualtricScheduleTypes_immediately) {
            // send imediately
            $date_to_be_sent = date('Y-m-d H:i:s', time());
        } else if ($schedule_info[qualtricScheduleTypes] == qualtricScheduleTypes_on_fixed_datetime) {
            // send on specific date
            $date_to_be_sent = date('Y-m-d H:i:s', DateTime::createFromFormat('d-m-Y H:i', $schedule_info['custom_time'])->getTimestamp());
        } else if ($schedule_info[qualtricScheduleTypes] == qualtricScheduleTypes_after_period) {
            // send after time period 
            $now = date('Y-m-d H:i:s', time());
            $date_to_be_sent = date('Y-m-d H:i:s', strtotime('+' . $schedule_info['send_after'] . ' ' . $schedule_info['send_after_type'], strtotime($now)));
        } else if ($schedule_info[qualtricScheduleTypes] == qualtricScheduleTypes_after_period_on_day_at_time) {
            // send on specific weekday after 1,2,3, or more weeks at specifi time
            $now = date('Y-m-d H:i:s', time());
            $next_weekday = strtotime('next ' . $schedule_info['send_on_day'], strtotime($now));
            $d = new DateTime();
            $next_weekday = $d->setTimestamp($next_weekday);
            $at_time = explode(':', $schedule_info['send_on_day_at']);
            $next_weekday = $next_weekday->setTime($at_time[0], $at_time[1]);
            if ($schedule_info['send_on'] > 1) {
                $date_to_be_sent = date('Y-m-d H:i:s', strtotime('+' . $schedule_info['send_on'] - 1 . ' weeks', $next_weekday->getTimestamp()));
            } else {
                $next_weekday = $next_weekday->getTimestamp();
                $date_to_be_sent = date('Y-m-d H:i:s', $next_weekday);
            }
        }
        return $date_to_be_sent;
    }

    /**
     * Add a reminder in qualtricsReminders
     *
     * @param int $mq_id
     *  the mailQueue id
     * @param int $uid
     * user id
     * @param int $sid
     * the id of the reminded survey
     * @retval int
     *  The id of the new record.
     */
    public function add_reminder($mq_id, $uid, $sid)
    {
        return $this->db->insert("qualtricsReminders", array(
            "id_users" => $uid,
            "id_qualtricsSurveys" => $sid,
            "id_mailQueue" => $mq_id
        ));
    }

    /**
     * Check if any mail should be queued based on the actions
     *
     * @param array $data
     *  the data from the callback.     
     * @param in user_id
     * user id
     * @retval string
     *  log text what actions was done;
     */
    private function check_queue_mail_from_actions($data, $user_id)
    {
        $result[] = 'no mail queue';
        $mail = array();
        //get all actions for this survey and trigger type
        $actions = $this->get_actions($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE], $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]);
        foreach ($actions as $action) {
            //clear the mail generation data
            if ($this->is_user_in_group($user_id, $action['id_groups'])) {
                $schedule_info = json_decode($action['schedule_info'], true);
                unset($mail);
                unset($result);
                $mail = array(
                    "id_mailQueueStatus" => $this->db->get_lookup_id_by_code(mailQueueStatus, mailQueueStatus_queued),
                    "date_to_be_sent" => $this->calc_date_to_be_sent($schedule_info),
                    "from_email" => $schedule_info['from_email'],
                    "from_name" => $schedule_info['from_name'],
                    "reply_to" => $schedule_info['reply_to'],
                    "recipient_emails" =>  str_replace('@user', $this->db->select_by_uid('users', $user_id)['email'], $schedule_info['recipient']),
                    "subject" => $schedule_info['subject'],
                    "body" => $schedule_info['body']
                );
                $mq_id = $this->mail->add_mail_to_queue($mail);
                if ($mq_id > 0) {
                    $this->transaction->add_transaction(
                        transactionTypes_insert,
                        transactionBy_by_qualtrics_callback,
                        null,
                        $this->transaction::TABLE_MAILQUEUE,
                        $mq_id
                    );
                    if ($action['action_schedule_type_code'] == qualtricsActionScheduleTypes_reminder) {
                        $this->add_reminder($mq_id, $user_id, $action['id_qualtricsSurveys_reminder']);
                    }
                    $result[] = 'Mail was queued for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                        ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                        ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
                } else {
                    $result[] = 'ERROR! Mail was not queued for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                        ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                        ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
                }
            }
        }

        return $result;
    }

    /**
     * Add a new user to the DB.
     *
     * @param array $data
     *  the data from the callback.     
     * @param int $uid
     * user id
     * @retval int
     *  The id of the new user.
     */
    private function update_survey_response($data)
    {
        return $this->db->update_by_ids(
            "qualtricsSurveysResponses",
            array(
                "id_qualtricsProjectActionTriggerTypes" => $this->db->get_lookup_id_by_value(
                    qualtricsProjectActionTriggerTypes,
                    $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]
                )
            ),
            array('survey_response_id' => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE])
        );
    }

    /**
     * Get the group id
     *
     * @param $group
     *  The name of a group
     * @return $groupId
     *  the id of the group or -1 on failure
     */
    private function getGroupId($group)
    {
        $sql = "SELECT id FROM groups
            WHERE name = :group";
        $res = $this->db->query_db_first($sql, array(':group' => $group));
        return  !isset($res['id']) ? -1 : $res['id'];
    }

    /**
     * Assign group to code in the table validation codes
     *
     * @param $group
     *  The id of the group
     * @param $code
     *  The code to be assigned to the group
     * @retval boolean
     *  true an success, false on failure
     */
    private function assignGroupToCode($group, $code)
    {
        return (bool) $this->db->insert(
            'codes_groups',
            array(
                'id_groups' => $group,
                'code' => $code
            )
        );
    }

    /**
     * Assign group to user in the table validation codes
     *
     * @param $group
     *  The id of the group
     * @param $userId
     *  The id of the user to be assigned to the group
     * @retval boolean
     *  true an success, false on failure
     */
    private function assignUserToGroup($group, $userId)
    {
        return (bool) $this->db->insert(
            'users_groups',
            array('id_groups' => $group, 'id_users' => $userId)
        );
        return false;
    }

    /**
     * Validate all request parameters and return the results
     *
     * @param $data
     *  The POST data of the callback call:
     *   callbackKey is expected from where the callback is initialized
     * @param $type
     *  the type for which function should be validate the data
     * @retval array
     *  An array with the callback results
     */
    private function validate_callback($data, $type)
    {
        $result['selfhelpCallback'] = [];
        $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_SUCCESS;
        if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE]) || $this->db->get_callback_key() !== $data[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE]) {
            //validation for the callback key; if wrong return not secured
            array_push($result['selfhelpCallback'], 'wrong callback key');
            $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            return $result;
        }
        if ($type == CallbackQualtrics::VALIDATION_add_survey_response) {
            // validate add_survey_response parameters
            if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE]) || $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] == '') {
                array_push($result['selfhelpCallback'], 'misisng participant');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            } else if (preg_match('/[^A-Za-z0-9]/', $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'wrong participant value (only numbers and laters are possible)');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            } else if (!$this->code_exist($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE])) {
                //check if the code is in the table validation_codes
                array_push($result['selfhelpCallback'], 'validation code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' does not exist');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            }
            if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'misisng response id');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            }
            if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'misisng survey id');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            }
            if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'misisng trigger type');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            }
        }
        if ($type == CallbackQualtrics::VALIDATION_set_group) {
            // validate set_group parameters
            if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE]) || $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] == '') {
                array_push($result['selfhelpCallback'], 'misisng participant');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            } else if (preg_match('/[^A-Za-z0-9]/', $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'wrong participant value (only numbers and laters are possible)');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            } else if (!$this->code_exist($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE])) {
                //check if the code is in the table validation_codes
                array_push($result['selfhelpCallback'], 'validation code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' does not exist');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            }
            if (!isset($data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'misisng group');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            } else if (!preg_match('/^[\w-]+$/', $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE])) {
                array_push($result['selfhelpCallback'], 'wrong group value (only numbers, laters, hyphens and underscores are possible)');
                $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
            }
            $result['groupId'] = $this->getGroupId($data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE]);
            if (!($result['groupId'] > 0)) {
                // validation for does the group exists
                array_push($result['selfhelpCallback'], 'group does not exist');
                $result['callback_status'] = CALLBACK_ERROR;
            }
        }
        return $result;
    }

    /**
     * Add survey response for the user
     *
     * @param $data
     * The POST data of the callback call:
     * QUALTRICS_PARTICIPANT_VARIABLE,
     * QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE,
     * QUALTRICS_CALLBACK_KEY_VARIABLE,
     * QUALTRICS_TRIGGER_TYPE_VARIABLE
     */
    public function add_survey_response($data)
    {
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = $this->validate_callback($data, CallbackQualtrics::VALIDATION_add_survey_response);
        if ($result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] == CallbackQualtrics::CALLBACK_SUCCESS) {
            //validation passed; try to execute
            $user_id = $this->getUserId($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE]);
            if (!($user_id > 0)) {
                //user does not exist; create a new user with status auto_created
                $user_id = $this->insert_new_user($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE]);
                if ($user_id > 0) {
                    $result['selfhelpCallback'][] = "User with code " . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . " was created.";
                } else {
                    $result['selfhelpCallback'][] = "Error. User with code " . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . " cannot be created.";
                    $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
                }
            }
            if ($user_id > 0) {
                if ($data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE] === qualtricsProjectActionTriggerTypes_started) {
                    //insert survey response
                    $inserted_id = $this->insert_survey_response($data, $user_id);
                    if ($inserted_id > 0) {
                        //successfully inserted survey repsonse
                        $result['selfhelpCallback'][] = "Success. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was inserted.";
                        $result['selfhelpCallback'] = array_merge($result['selfhelpCallback'], $this->check_queue_mail_from_actions($data, $user_id));
                    } else {
                        //something went wrong; survey resposne was not inserted
                        $result['selfhelpCallback'][] = "Error. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was not inserted.";
                        $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
                    }
                } else if ($data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE] === qualtricsProjectActionTriggerTypes_finished) {
                    //update survey response
                    $update_id = $this->update_survey_response($data);
                    $scheduled_reminders = $this->get_scheduled_reminders($user_id, $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE]);
                    if ($scheduled_reminders && count($scheduled_reminders) > 0) {
                        $this->delete_reminders($scheduled_reminders);
                    }
                    if ($update_id > 0) {
                        //successfully updated survey repsonse
                        $result['selfhelpCallback'][] = "Success. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was updated.";
                        $result['selfhelpCallback'][] = $this->check_queue_mail_from_actions($data, $user_id);
                    } else {
                        //something went wrong; survey resposne was not updated
                        $result['selfhelpCallback'][] = "Error. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was not updated.";
                        $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
                    }
                }
            }
        }
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }

    /**
     * Add group for the user. If the group does not exist it is created.
     *
     * @param $data
     * The POST data of the callback call:
     * QUALTRICS_PARTICIPANT_VARIABLE,
     * QUALTRICS_GROUP_VARIABLE,
     * QUALTRICS_CALLBACK_KEY_VARIABLE
     */
    public function set_group($data)
    {
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = $this->validate_callback($data, CallbackQualtrics::VALIDATION_set_group);
        if ($result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] == CallbackQualtrics::CALLBACK_SUCCESS) {
            //validation passed; try to execute
            $user_id = $this->getUserId($data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE]);
            if ($user_id > 0) {
                // set group for user
                if ($this->assignUserToGroup($result['groupId'], $user_id)) {
                    array_push($result['selfhelpCallback'][], 'User with code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE]);
                } else {
                    array_push($result['selfhelpCallback'][], 'Failed! User with code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was not assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE]);
                    $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CALLBACK_ERROR;
                }
            } else {
                // set group for code and once user is registered the group will be assigned
                if ($this->assignGroupToCode($result['groupId'], $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE])) {
                    $result['selfhelpCallback'][] = 'Code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE];
                } else {
                    $result['selfhelpCallback'][] = 'Failed! Code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was not assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE];
                    $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CALLBACK_ERROR;
                }
            }
        }
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }
}
?>
