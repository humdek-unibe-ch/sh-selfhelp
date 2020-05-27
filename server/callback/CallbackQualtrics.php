<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseCallback.php";
require_once __DIR__ . "/../component/moduleQualtricsProject/ModuleQualtricsProjectModel.php";

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
     * status id for auto created new users
     */
    private $auto_created_user_status;

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $this->auto_created_user_status = $this->db->query_db_first('SELECT id FROM userStatus WHERE `name` = "auto_created"')['id'];
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
            $uid =  $this->db->insert("users", array(
                "email" => $code . '@selfhelp.psy.unibe.ch',
                "id_status" => $this->auto_created_user_status,
            ));

            //try to update if the code exist
            $code_updated = (bool) $this->db->update_by_ids(
                'validation_codes',
                array(
                    "id_users" => $uid,
                ),
                array("code" => $code)
            );

            if (!$code_updated) {
                //if code does not exist, create the code
                //disabled. If in the future we need to create users without already generated codes in the DB
                // $this->db->insert("validation_codes", array(
                //     "code" => $code,
                //     "id_users" => $uid,
                // ));
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
     *  The id of the new user.
     */
    private function insert_survey_response($data, $uid)
    {
        return $this->db->insert("qualtricsSurveysResponses", array(
            "id_users" => $uid,
            "id_surveys" => $this->db->query_db_first(
                'SELECT id FROM qualtricsSurveys',
                array("qualtrics_survey_id" => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE])
            )['id'],
            "id_qualtricsProjectStageTriggerTypes" => $this->db->get_lookup_id_by_value(ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_LOOKUP_TYPE, $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]),
            "survey_response_id" => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]
        ));
    }

    /**
     * Get all stages for a survey and a trigger_type
     *
     * @param string $sid
     *  qualtrics survey id
     * @param string $trigger_type
     *  trigger type
     *  @retval array
     * return all stages for that survey with this trigger_type
     */
    private function get_stages($sid, $trigger_type)
    {
        $sqlGetStages = "SELECT *
                FROM view_qualtricsStages
                WHERE qualtrics_survey_id = :sid AND trigger_type = :trigger_type";
        return $this->db->query_db(
            $sqlGetStages,
            array(
                "sid" => $sid,
                "trigger_type" => $trigger_type
            )
        );
    }

    /**
     * Check if any mail should be queued
     *
     * @param array $data
     *  the data from the callback.     
     * @retval string
     *  log text what actions was done;
     */
    private function check_queue_mail($data)
    {
        $result[] = 'no mail queue';
        $mail = array();
        //get all stages for this survey and trigger type
        $stages = $this->get_stages($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE], $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]);
        foreach ($stages as $stage) {
            //clear the mail generation data
            unset($mail);
            unset($result);
            $mail = array(
                "id_mailQueueStatus" => 17,
                "date_to_be_sent" => date('Y-m-d H:i:s', time() + (10 * 60)),
                "from_email" => "tpf.unibe@gmail.com",
                "from_name" => "stefan",
                "reply_to" => "tpf.unibe@gmail.com",
                "recipient_emails" => "redwater@abv.bg",
                "subject" => "test",
                "body" => "test body"
            );
            if ($this->mail->add_mail_to_queue($mail) > 0) {
                $result[] = 'Mail was queued for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                    ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                    ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
            } else {
                $result[] = 'ERROR! Mail was not queued for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                    ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                    ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
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
                "id_qualtricsProjectStageTriggerTypes" => $this->db->get_lookup_id_by_value(
                    ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_LOOKUP_TYPE,
                    $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]
                )
            ),
            array('survey_response_id' => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE])
        );
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
                if ($data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE] === ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_START) {
                    //insert survey response
                    $inserted_id = $this->insert_survey_response($data, $user_id);
                    if ($inserted_id > 0) {
                        //successfully inserted survey repsonse
                        $result['selfhelpCallback'][] = "Success. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was inserted.";
                        $result['selfhelpCallback'] = array_merge($result['selfhelpCallback'], $this->check_queue_mail($data));
                    } else {
                        //something went wrong; survey resposne was not inserted
                        $result['selfhelpCallback'][] = "Error. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was not inserted.";
                        $result[ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS] = CallbackQualtrics::CALLBACK_ERROR;
                    }
                } else if ($data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE] === ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_END) {
                    //update survey response
                    $update_id = $this->update_survey_response($data);
                    if ($update_id > 0) {
                        //successfully updated survey repsonse
                        $result['selfhelpCallback'][] = "Success. Response " . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE] . " was updated.";
                        $result['selfhelpCallback'][] = $this->check_queue_mail($data);
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
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }
}
?>
