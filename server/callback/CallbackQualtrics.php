<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseCallback.php";
require_once __DIR__ . "/../component/moduleQualtricsProject/ModuleQualtricsProjectModel.php";
require_once __DIR__ . "/../component/style/register/RegisterModel.php";
require_once __DIR__ . "/../service/ext/php-pdftk-0.8.1.0/vendor/autoload.php";

use mikehaertl\pdftk\Pdf;

/**
 * A small class that handles callbak and set the group number for validation code
 * calls.
 */
class CallbackQualtrics extends BaseCallback
{

    /* Constants ************************************************/
    const VALIDATION_add_survey_response = 'add_survey_response';
    const VALIDATION_set_group = 'set_group';
    const VALIDATION_open_survey_response = 'open_survey_response';
    const CALLBACK_NEW = 'callback_new';
    const CALLBACK_ERROR = 'callback_error';
    const CALLBACK_SUCCESS = 'callback_success';

    /* Private Properties *****************************************************/

    /**
     * The instance of the user model from the user component.
     */
    private $register_user_model = null;

    /**
     * Services
     */
    private $services = null;

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
        $this->services = $services;
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
     * Get all actions for a survey and a trigger_type which has functions
     *
     * @param string $sid
     *  qualtrics survey id
     * @param string $trigger_type
     *  trigger type
     *  @retval array
     * return all actions for that survey with this trigger_type
     */
    private function get_actions_with_functions($sid, $trigger_type)
    {
        $sqlGetActions = "SELECT *
                FROM view_qualtricsActions
                WHERE qualtrics_survey_id = :sid AND trigger_type = :trigger_type AND functions IS NOT NULL";
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
     * @param string action_schedule_type_code
     * type notification or reminder
     * @retval string
     * the date in sting format for MySQL
     */
    private function calc_date_to_be_sent($schedule_info, $action_schedule_type_code)
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
            // send on specific weekday after 1,2,3, or more weeks at specific time
            $date_to_be_sent = $this->calc_date_on_weekday($schedule_info);
            if ($action_schedule_type_code == qualtricsActionScheduleTypes_reminder) {
                // we have to check the linked notification and schedule the reminder always after the notification
                $schedule_info_notification = json_decode($this->db->query_db_first('SELECT schedule_info FROM qualtricsActions WHERE id = :id', array(':id' => $schedule_info['linked_action']))['schedule_info'], true);
                $base_schedule_info = $schedule_info;
                $base_schedule_info['send_on'] = 1;
                $schedule_info_notification['send_on'] = 1;
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
    private function add_reminder($mq_id, $uid, $sid)
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
                // *************************************** CHECK FOR ADDITIONAL FUNCTIONS THAT RETURN ATTACHMENTS *************************************************************
                $attachments = array();
                $functions = explode(';', $action['functions_code']);
                foreach ($functions as $key => $value) {
                    if ($value == qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts) {
                        // WORKWELL evaluate strenghts function
                        $result[] = qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts;
                        $func_res = $this->workwell_evaluate_strenghts($data, $user_id);
                        $result[] = $func_res['output'];
                        if ($func_res['attachment']) {
                            $attachments[] = $func_res['attachment'];
                        }
                    } else if (
                        $value == qualtricsProjectActionAdditionalFunction_workwell_cg_ap_4 ||
                        $value == qualtricsProjectActionAdditionalFunction_workwell_cg_ap_5 ||
                        $value == qualtricsProjectActionAdditionalFunction_workwell_eg_ap_4 ||
                        $value == qualtricsProjectActionAdditionalFunction_workwell_eg_ap_5
                    ) {
                        // Fill PDF with qualtrics embeded data
                        $result[] = $value;
                        $func_res = $this->fill_pdf_with_qualtrics_embeded_data($value, $data, $user_id);
                        $result[] = $func_res['output'];
                        if ($func_res['attachment']) {
                            $attachments[] = $func_res['attachment'];
                        }
                    }
                }
                // *************************************** END CHECK FOR ADDITIONAL FUNCTIONS THAT RETURN ATTACHMENTS *************************************************************
                $body = str_replace('@user_name', $this->db->select_by_uid('users', $user_id)['name'], $schedule_info['body']);
                $mail = array(
                    "id_mailQueueStatus" => $this->db->get_lookup_id_by_code(mailQueueStatus, mailQueueStatus_queued),
                    "date_to_be_sent" => $this->calc_date_to_be_sent($schedule_info, $action['action_schedule_type_code']),
                    "from_email" => $schedule_info['from_email'],
                    "from_name" => $schedule_info['from_name'],
                    "reply_to" => $schedule_info['reply_to'],
                    "recipient_emails" =>  str_replace('@user', $this->db->select_by_uid('users', $user_id)['email'], $schedule_info['recipient']),
                    "subject" => $schedule_info['subject'],
                    "body" => $body
                );
                $mq_id = $this->mail->add_mail_to_queue($mail, $attachments);
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
                    if (($schedule_info[qualtricScheduleTypes] == qualtricScheduleTypes_immediately)) {
                        if ($this->mail->send_mail_from_queue($mq_id, transactionBy_by_qualtrics_callback)) {
                            $result[] = 'Mail was sent for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                                ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                                ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
                        } else {
                            $result[] = 'ERROR! Mail was not sent for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                                ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                                ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
                        }
                    }
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
     * Evaluate personal strenghts for WORKWELL project
     *
     * @param array $data
     *  the data from the callback.     
     * @param in user_id
     * user id
     * @retval string
     *  log text what actions was done;
     */
    private function workwell_evaluate_strenghts($data, $user_id)
    {
        $result = [];
        $qualtrics_api = $this->get_qualtrics_api($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE]);
        $strengths = array(
            "creativity" => array(
                "coefficient_1" => 3.43,
                "coefficient_2" => 0.6,
                "label" => "Kreativitaet",
                "value" => 0
            ),
            "curiosity" => array(
                "coefficient_1" => 3.92,
                "coefficient_2" => 0.51,
                "label" => "Neugier",
                "value" => 0
            ),
            "open_mindedness" => array(
                "coefficient_1" => 3.7,
                "coefficient_2" => 0.48,
                "label" => "Urteilsvermoegen",
                "value" => 0
            ),
            "learning" => array(
                "coefficient_1" => 3.59,
                "coefficient_2" => 0.62,
                "label" => "Liebe zum Lernen",
                "value" => 0
            ),
            "perspektive" => array(
                "coefficient_1" => 3.46,
                "coefficient_2" => 0.47,
                "label" => "Weisheit",
                "value" => 0
            ),
            "bravery" => array(
                "coefficient_1" => 3.52,
                "coefficient_2" => 0.5,
                "label" => "Tapferkeit",
                "value" => 0
            ),
            "persistence" => array(
                "coefficient_1" => 3.47,
                "coefficient_2" => 0.59,
                "label" => "Ausdauer",
                "value" => 0
            ),
            "authenticity" => array(
                "coefficient_1" => 3.78,
                "coefficient_2" => 0.43,
                "label" => "Authentizitaet",
                "value" => 0
            ),
            "zest" => array(
                "coefficient_1" => 3.57,
                "coefficient_2" => 0.52,
                "label" => "Enthusiasmus",
                "value" => 0
            ),
            "love" => array(
                "coefficient_1" => 3.78,
                "coefficient_2" => 0.5,
                "label" => "Bindungsfaehigkeit",
                "value" => 0
            ),
            "kindness" => array(
                "coefficient_1" => 3.85,
                "coefficient_2" => 0.46,
                "label" => "Freundlichkeit",
                "value" => 0
            ),
            "social_intelligence" => array(
                "coefficient_1" => 3.62,
                "coefficient_2" => 0.44,
                "label" => "Soziale Intelligenz",
                "value" => 0
            ),
            "teamwork" => array(
                "coefficient_1" => 3.6,
                "coefficient_2" => 0.48,
                "label" => "Teamwork",
                "value" => 0
            ),
            "fairness" => array(
                "coefficient_1" => 3.9,
                "coefficient_2" => 0.47,
                "label" => "Fairness",
                "value" => 0
            ),
            "leadership" => array(
                "coefficient_1" => 3.57,
                "coefficient_2" => 0.48,
                "label" => "Fuehrungsvermoegen",
                "value" => 0
            ),
            "forgiveness" => array(
                "coefficient_1" => 3.52,
                "coefficient_2" => 0.52,
                "label" => "Vergebungsbereitschaft",
                "value" => 0
            ),
            "modesty" => array(
                "coefficient_1" => 3.32,
                "coefficient_2" => 0.56,
                "label" => "Bescheidenheit",
                "value" => 0
            ),
            "prudence" => array(
                "coefficient_1" => 3.32,
                "coefficient_2" => 0.53,
                "label" => "Vorsicht",
                "value" => 0
            ),
            "self_regulation" => array(
                "coefficient_1" => 3.25,
                "coefficient_2" => 0.55,
                "label" => "Selbstregulation",
                "value" => 0
            ),
            "appreciation" => array(
                "coefficient_1" => 3.51,
                "coefficient_2" => 0.54,
                "label" => "Sinn fuer das Schoene",
                "value" => 0
            ),
            "gratitude" => array(
                "coefficient_1" => 3.69,
                "coefficient_2" => 0.53,
                "label" => "Dankbarkeit",
                "value" => 0
            ),
            "hope" => array(
                "coefficient_1" => 3.54,
                "coefficient_2" => 0.55,
                "label" => "Hoffnung",
                "value" => 0
            ),
            "humor" => array(
                "coefficient_1" => 3.65,
                "coefficient_2" => 0.56,
                "label" => "Humor",
                "value" => 0
            ),
            "spirituality" => array(
                "coefficient_1" => 3.02,
                "coefficient_2" => 0.89,
                "label" => "Spiritualitaet",
                "value" => 0
            )
        );
        $moduleQualtrics = new ModuleQualtricsProjectModel($this->services, null, $qualtrics_api);
        $result[] = qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts;
        $result[] = $data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE];
        $result[] = $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE];
        $survey_response = $moduleQualtrics->get_survey_response($data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE], $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]);
        $loops = 0;
        while (!$survey_response) {
            //it takes time for the response to be recorded
            sleep(1);
            $loops++;
            $survey_response = $moduleQualtrics->get_survey_response($data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE], $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]);
            if ($loops > 60) {
                // we wait maximum 1 minute for the response
                $result[] = 'No survey response';
                return $result;
                break;
            }
        }
        // $survey_response = $moduleQualtrics->get_survey_response('SV_824CbMwxvS8SJsp', 'R_20SDVytaYg9mSyG'); //for testing
        foreach ($strengths as $key => $value) {
            if (isset($survey_response['values'][$key])) {
                //sudo apt install php-dev; pecl install stats-2.0.3 ; then added extension=stats.so to my php.ini
                $strengths[$key]["value"] = round(stats_cdf_normal($survey_response['values'][$key], $value["coefficient_1"], $value["coefficient_2"], 1) * 100);
            }
        }
        array_multisort(array_column($strengths, 'value'), SORT_DESC, $strengths);

        $fields = array();
        $i = 1;
        foreach ($strengths as $key => $value) {
            $fields['Strengths' . $i] = $value['label'];
            $i++;
        }
        $attachment = $this->get_attachment_info(qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts, $data[$moduleQualtrics::QUALTRICS_PARTICIPANT_VARIABLE]);
        $pdf = new Pdf($attachment['template_path']);
        $pdf->fillForm($fields)
            ->needAppearances()
            ->saveAs($attachment['attachment_path']);
        $ret_value = null;
        $ret_value['attachment'] = $attachment;
        $ret_value['output'] = $result;
        return $ret_value;
    }

    /**
     * Get qualtrics api key
     * @param string $survey_id survey id
     * @retval string return the api key
     */
    private function get_qualtrics_api($survey_id)
    {
        return $this->db->query_db_first('SELECT DISTINCT qualtrics_api
                                                    FROM view_qualtricsActions
                                                    WHERE qualtrics_survey_id = :qualtrics_survey_id
                                                    LIMIT 0, 1;', array("qualtrics_survey_id" => $survey_id))['qualtrics_api'];
    }

    /**
     * Fill pdf form template with qualtrics embeded data. The name of the form's fields should be the same as the name of the embeded data fields
     *
     * @param string $function_name the name of the function - we use it to get the template
     * @param array $data
     *  the data from the callback.     
     * @param in user_id
     * user id
     * @retval string
     *  log text what actions was done;
     */
    private function fill_pdf_with_qualtrics_embeded_data($function_name, $data, $user_id)
    {
        $result = [];
        $qualtrics_api = $this->get_qualtrics_api($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE]);
        $moduleQualtrics = new ModuleQualtricsProjectModel($this->services, null, $qualtrics_api);
        $result[] = $function_name;
        $result[] = $data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE];
        $result[] = $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE];
        $survey_response = $moduleQualtrics->get_survey_response($data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE], $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]);
        $loops = 0;
        while (!$survey_response) {
            //it takes time for the response to be recorded
            sleep(1);
            $loops++;
            $survey_response = $moduleQualtrics->get_survey_response($data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE], $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]);
            if ($loops > 60) {
                // we wait maximum 1 minute for the response
                $result[] = 'No survey response';
                return $result;
                break;
            }
        }
        // $survey_response = $moduleQualtrics->get_survey_response('SV_039wOwdfOHnlAZT', 'R_2B8trWgcDYyyE29'); // for tests
        $attachment = $this->get_attachment_info($function_name, $data[$moduleQualtrics::QUALTRICS_PARTICIPANT_VARIABLE]);
        $pdfTemplate = new Pdf($attachment['template_path']);
        $data_fields = $pdfTemplate->getDataFields()->__toArray();

        // generate fields dynamically from the template
        $fields = array();
        foreach ($data_fields as $key => $value) {
            if (isset($survey_response['values'][$value['FieldName']])) {
                $fields[$value['FieldName']] = $survey_response['values'][$value['FieldName']];
            }
        }        
        $pdf = new Pdf($attachment['template_path']);
        $pdf->fillForm($fields)
            ->flatten()
            ->needAppearances()
            ->saveAs($attachment['attachment_path']);
        $ret_value = null;
        $ret_value['attachment'] = $attachment;
        $ret_value['output'] = $result;
        return $ret_value;
    }

    /**
     * Get the attachment info
     * @param string $function_name
     * @param string $file_name
     * @retval array 
     * The attachment info properties
     */
    private function get_attachment_info($function_name, $file_name)
    {
        $genPdfFileName = $file_name . ".pdf";
        $genPdfFilePath = ASSET_SERVER_PATH . "/" . $function_name . "/" . $genPdfFileName;
        $genPdfFileUrl = ASSET_PATH . "/" . $function_name . "/" . $genPdfFileName;
        $templatePath = ASSET_SERVER_PATH . "/" . $function_name . ".pdf";
        $attachment = array(
            "attachment_name" => $genPdfFileName,
            "attachment_path" => $genPdfFilePath,
            "attachment_url" => $genPdfFileUrl,
            "template_path" => $templatePath
        );
        return $attachment;
    }

    /**
     * Check if any action has addtional function that should be executed
     *
     * @param array $data
     *  the data from the callback.     
     * @param in user_id
     * user id
     * @retval string
     *  log text what actions was done;
     */
    private function check_functions_from_actions($data, $user_id)
    {
        $result = [];
        $result[] = 'check additional functions';
        //get all actions for this survey and trigger type 
        $actions = $this->get_actions_with_functions($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE], $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]);
        foreach ($actions as $action) {
            if ($this->is_user_in_group($user_id, $action['id_groups'])) {
                // Special Functions code here if it is not related to notifications or reminders
                // if (strpos($action['functions_code'], qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts) !== false) {
                //     // WORKWELL evaluate strenghts function
                //     $result[] = qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts;
                //     $result[] = $this->workwell_evaluate_strenghts($data, $user_id);
                // }
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
                        $result['selfhelpCallback'][] = $this->check_queue_mail_from_actions($data, $user_id);
                        $result['selfhelpCallback'][] = $this->check_functions_from_actions($data, $user_id);
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
                        $result['selfhelpCallback'][] = $this->check_functions_from_actions($data, $user_id);
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
     * QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE,
     * QUALTRICS_CALLBACK_KEY_VARIABLE,
     * QUALTRICS_TRIGGER_TYPE_VARIABLE
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
                    $log = 'User with code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE];
                    $result['selfhelpCallback'][] = $log;
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_qualtrics_callback, null, $this->transaction::TABLE_USERS_GROUPS, $user_id, false, $log);
                } else {
                    $result['selfhelpCallback'][] = 'Failed! User with code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was not assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE];
                    $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CALLBACK_ERROR;
                }
            } else {
                // set group for code and once user is registered the group will be assigned
                if ($this->assignGroupToCode($result['groupId'], $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE])) {
                    $log = 'Code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE];
                    $result['selfhelpCallback'][] = $log;
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_qualtrics_callback, null, $this->transaction::TABLE_CODES_GROUPS, $result['groupId'], false, $log);
                } else {
                    $result['selfhelpCallback'][] = 'Failed! Code: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] . ' was not assigned to group: ' . $result['groupId'] . ' with name: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE];
                    $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CALLBACK_ERROR;
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
     * QUALTRICS_SURVEY_ID_VARIABLE,
     * QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE,
     * QUALTRICS_CALLBACK_KEY_VARIABLE,
     * QUALTRICS_TRIGGER_TYPE_VARIABLE
     */
    public function open_survey_response($data)
    {
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = $this->validate_callback($data, CallbackQualtrics::VALIDATION_open_survey_response);
        if ($result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] == CallbackQualtrics::CALLBACK_SUCCESS) {
            //validation passed; try to execute
            $result['pdf_link'] = "178.38.48.100/selfhelp/assets/workwell_eg_ap_4.pdf";
        }
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }
}
?>