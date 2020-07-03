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
        $qualtrics_api = $this->db->query_db_first('SELECT DISTINCT qualtrics_api
                                                    FROM view_qualtricsActions
                                                    WHERE qualtrics_survey_id = :qualtrics_survey_id
                                                    LIMIT 0, 1;', array("qualtrics_survey_id" => $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE]))['qualtrics_api'];
        $strengths = array(
            "creativity" => array(
                "coefficient_1" => 3.78,
                "coefficient_2" => 0.5,
                "label" => "Kreativitaet",
                "value" => 0
            ),
            "curiosity" => array(
                "coefficient_1" => 3.85,
                "coefficient_2" => 0.46,
                "label" => "Neugier",
                "value" => 0
            ),
            "open_mindedness" => array(
                "coefficient_1" => 3.62,
                "coefficient_2" => 0.44,
                "label" => "Urteilsvermoegen",
                "value" => 0
            ),
            "learning" => array(
                "coefficient_1" => 3.6,
                "coefficient_2" => 0.48,
                "label" => "Liebe zum Lernen",
                "value" => 0
            ),
            "perspektive" => array(
                "coefficient_1" => 3.9,
                "coefficient_2" => 0.47,
                "label" => "Weisheit",
                "value" => 0
            ),
            "bravery" => array(
                "coefficient_1" => 3.57,
                "coefficient_2" => 0.48,
                "label" => "Tapferkeit",
                "value" => 0
            ),
            "persistence" => array(
                "coefficient_1" => 3.52,
                "coefficient_2" => 0.52,
                "label" => "Ausdauer",
                "value" => 0
            ),
            "authenticity" => array(
                "coefficient_1" => 3.32,
                "coefficient_2" => 0.56,
                "label" => "Authentizitaet",
                "value" => 0
            ),
            "zest" => array(
                "coefficient_1" => 3.32,
                "coefficient_2" => 0.53,
                "label" => "Enthusiasmus",
                "value" => 0
            ),
            "love" => array(
                "coefficient_1" => 3.25,
                "coefficient_2" => 0.55,
                "label" => "Bindungsfaehigkeit",
                "value" => 0
            ),
            "kindness" => array(
                "coefficient_1" => 3.51,
                "coefficient_2" => 0.54,
                "label" => "Freundlichkeit",
                "value" => 0
            ),
            "social_intelligence" => array(
                "coefficient_1" => 3.69,
                "coefficient_2" => 0.53,
                "label" => "Soziale Intelligenz",
                "value" => 0
            ),
            "teamwork" => array(
                "coefficient_1" => 3.54,
                "coefficient_2" => 0.55,
                "label" => "Teamwork",
                "value" => 0
            ),
            "fairness" => array(
                "coefficient_1" => 3.65,
                "coefficient_2" => 0.56,
                "label" => "Fairness",
                "value" => 0
            ),
            "leadership" => array(
                "coefficient_1" => 3.02,
                "coefficient_2" => 0.89,
                "label" => "Fuehrungsvermoegen",
                "value" => 0
            ),
            "forgiveness" => array(
                "coefficient_1" => 3.43,
                "coefficient_2" => 0.6,
                "label" => "Vergebungsbereitschaft",
                "value" => 0
            ),
            "modesty" => array(
                "coefficient_1" => 3.92,
                "coefficient_2" => 0.51,
                "label" => "Bescheidenheit",
                "value" => 0
            ),
            "prudence" => array(
                "coefficient_1" => 3.7,
                "coefficient_2" => 0.48,
                "label" => "Vorsicht",
                "value" => 0
            ),
            "self_regulation" => array(
                "coefficient_1" => 3.59,
                "coefficient_2" => 0.62,
                "label" => "Selbstregulation",
                "value" => 0
            ),
            "appreciation" => array(
                "coefficient_1" => 3.46,
                "coefficient_2" => 0.47,
                "label" => "Sinn fuer das Schoene",
                "value" => 0
            ),
            "gratitude" => array(
                "coefficient_1" => 3.52,
                "coefficient_2" => 0.5,
                "label" => "Dankbarkeit",
                "value" => 0
            ),
            "hope" => array(
                "coefficient_1" => 3.47,
                "coefficient_2" => 0.59,
                "label" => "Hoffnung",
                "value" => 0
            ),
            "humor" => array(
                "coefficient_1" => 3.78,
                "coefficient_2" => 0.43,
                "label" => "Humor",
                "value" => 0
            ),
            "spirituality" => array(
                "coefficient_1" => 3.57,
                "coefficient_2" => 0.52,
                "label" => "Spiritualitaet",
                "value" => 0
            )
        );
        $moduleQualtrics = new ModuleQualtricsProjectModel($this->services, null, $qualtrics_api);
        $result[] = qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts;
        $result[] = $data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE];
        $result[] = $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE];
        // $survey_response = $this->moduleQualtrics->get_survey_response($data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE], $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]);
        // $loops = 0;
        // while (!$survey_response) {
        //     //it takes time for the response to be recorded
        //     sleep(1);
        //     $loops++;
        //     $survey_response = $this->moduleQualtrics->get_survey_response($data[$moduleQualtrics::QUALTRICS_SURVEY_ID_VARIABLE], $data[$moduleQualtrics::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE]);
        //     if ($loops > 60) {
        //         // we wait maximum 1 minute for the response
        //         $result[] = 'No survey response';
        //         return $result;
        //         break;
        //     }
        // }
        $survey_response = $moduleQualtrics->get_survey_response('SV_824CbMwxvS8SJsp', 'R_20SDVytaYg9mSyG');
        foreach ($strengths as $key => $value) {
            if (isset($survey_response['values'][$key])) {
                //pecl install stats-2.0.3 ; then added extension=stats.so to my php.ini
                $strengths[$key]["value"] = round(stats_cdf_normal($survey_response['values'][$key], $value["coefficient_1"], $value["coefficient_2"], 1) * 100);
            }
        }
        array_multisort(array_column($strengths, 'value'), SORT_DESC, $strengths);
        $body = 'Auf Basis Ihrer Antworten haben wir eine Rangreihe Ihrer persönlichen Charakterstärken erstellt. Damit Sie Ihre Ergebnisse verstehen können, erhalten Sie im Folgenden einige Hinweise, die von genereller Bedeutung sind.Im Folgenden erhalten Sie eine Auflistung der 24 Stärken. Dies ist Ihre persönliche Rangreihenfolge der Charakterstärken. Die erste Stärke ist am wichtigsten be- ziehungsweise am typischsten für sie, die letzte eher unwichtig oder wenig charakteristisch. Die Forschung hat ergeben, dass Menschen meist zwischen drei und sieben für sie _cha- rakteristische Stärken_ aufweisen. Legen Sie Ihr Augenmerk deshalb auf die ersten Stärken der Rangreihenfolge. Personen erfahren Zufriedenheit bei der Ausübung dieser Stärken, z.B. im Beruf (Harzer &amp; Ruch, 2012) oder bei Freizeitaktivitäten.

Die als Nummer 24 gereihte Stärke ist die am geringsten ausgeprägte Stärke (sie ist aber nicht als Schwäche zu interpretieren).

Man nimmt an, dass jeder Mensch zwischen 3 und 7 „Signaturstärken&quot; besitzt, also für eine

Person besonders zentral Stärken, deren Ausübung als erfüllend empfunden wird. Die in der

„top 5 strengths&quot; besonders zu beachten. Würden Sie den Fragebogen erneut ausfüllen, so könnte es sein, dass sich Ihre Rangreihenfolge mehr oder weniger verändert. Bei vielen Personen ist es jedoch so, dass die Ausprägungen ihrer Charakterstärken im Erwachsenenalter recht stabil bleiben.

Die rückgemeldeten Ergebnisse reflektieren eine Zusammenfassung _Ihrer VIA-IS- Selbstbeschreibung_. Sie selbst haben sich anhand der Fragen bzw. Aussagen beschrieben. Die Ergebnisse sind daher abhängig davon, wie genau und ehrlich Sie die Fragen beantwortet haben und welches Bild Sie von sich selbst haben.

**Wichtig!** Nur weil eine Charakterstärke weiter unten aufgeführt ist, bedeutet das nicht, dass Sie diese nicht haben. Der Fragebogen misst auschliesslich die Ausprägung in Stärken, nicht in Schwächen. Es ist also lediglich eine Rangfolge Ihrer Stärken.

| **Rang** | **Charakterstärke** | **Score(For testing)** |
| --- | --- | --- |
';
        $i = 1;
        foreach ($strengths as $key => $value) {
            $body = $body . '| ' . $i . ' | ' . $value['label'] . ' | ' . $value['value'] . ' |
            ';
            $i++;
        }
        $body = $body . '

***Beschreibung der Stärken***

**Kreativität**

Kreative Menschen besitzen die nötigen Fähigkeiten, um ständig eine Vielzahl von verschiedenen originellen Ideen zu produzieren oder originelle Verhaltensweisen zu zeigen. Diese zeichnen sich dadurch aus, dass sie nicht nur innovativ und neu, sondern auch der Realität angepasst sein müssen, damit sie den Menschen im Leben nützlich sind und ihnen weiterhelfen. Menschen mit ausgeprägter Kreativität zeigen diese Stärke meistens in mehreren Bereichen des Alltags auf, d.h. sie besitzen eine so genannte „praktische Intelligenz&quot;.

**Neugier**

Neugierige und interessierte Menschen haben ein ausgeprägtes Interesse an neuen Erfahrungen und sind sehr offen und flexibel bezüglich neuen, oft unerwarteten Situationen. Sie haben viele Interessen und finden an jeder Situation etwas Interessantes. Sie suchen aktiv nach Abwechslungen und Herausforderungen in ihrem täglichen Leben. Menschen können neugierig in Bezug auf einen spezifischen Bereich sein (z.B. Interesse an speziellen Tierarten) oder ein weitgefasstes Interesse an unterschiedlichen Dingen aufweisen.

**Urteilsvermögen**

Menschen mit einem stark ausgeprägten Urteilsvermögen haben die Fähigkeit, Probleme und Gegebenheiten des Alltags aus unterschiedlichen Perspektiven zu betrachten, sie kritisch zu hinterfragen und Argumente für wichtige Entscheidungen zu entwickeln. Sie sind in der Lage, Informationen objektiv und kritisch zu beleuchten. Dabei orientieren sie sich an der Realität.

**Liebe zum Lernen**

Menschen mit einer ausgeprägten Wissbegierde zeichnen sich durch eine grosse Begeisterung für das Lernen neuer Fähigkeiten, Fertigkeiten und Wissensinhalte aus. Sie lieben es, neue Dinge zu lernen und sind bemüht, sich ständig weiterzubilden und zu entwickeln. Die Liebe zum Lernen kann sich auf einen spezifischen Themenbereich (z.B. Geschichte) beziehen oder auch ganz allgemein ausgeprägt sein. Die Wissbegierde widerspiegelt den Wunsch, immer mehr über das Leben und die Welt wissen zu wollen. Dabei wird das ständige Lernen als eine Herausforderung betrachtet und es gibt kaum Menschen, die nicht mindestens in einem Bereich gerne lernen.

**Weisheit**

Weise, weitsichtige bzw. tiefsinnige Menschen haben einen guten Überblick und eine sinnvolle Sichtweise des Lebens. Sie besitzen die Fähigkeit, über das bisherige Leben eine sinnvolle Bilanz ziehen zu können. Dabei geht es um die Koordination des gelernten Wissens und der gemachten Erfahrungen eines Menschen, die zu seinem Wohlbefinden beitragen. Aus sozialer Perspektive betrachtet, können weise bzw. tiefsinnige Menschen anderen gut zuhören, Urteile abgeben und gute Ratschläge erteilen. Von den Mitmenschen werden sie oft um Ratschläge gebeten, weil sie eine Lebenseinstellung und Weltsicht haben, die für andere Leute (und sich selbst) Sinn macht.

**Tapferkeit**

Tapfere und mutige Menschen verfolgen ihre Ziele und lassen sich dabei nicht von Schwierigkeiten und Hindernissen entmutigen. Tapferkeit und Mut können sich auf unterschiedliche Lebensbereiche beziehen. Bei dieser Stärke handelt es sich um die Fähigkeit, etwas Positives und Nützliches weiterzubringen, trotz drohender Gefahren. Sie ermöglicht einem Menschen, unbeliebte aber richtige Meinungen zu vertreten, sich einem Problem zu stellen, den Ängsten ins Gesicht zu schauen und sich gegen Ungerechtigkeiten zu wehren.

**Ausdauer**

Ausdauer, Beharrlichkeit und Fleiss kennzeichnen Menschen, die alles zu Ende bringen wollen, was sie sich vorgenommen haben. Beharrlich streben sie nach ihren Zielen, geben nicht schnell auf, beenden was sie angefangen haben und lassen sich nicht ständig ablenken. Mit Beharrlichkeit ist jedoch keine zwanghafte Verfolgung von unerreichbaren Zielen gemeint. Beharrliche Menschen passen sich flexibel und realistisch den jeweiligen Situationsbedingungen an, ohne perfektionistisch zu werden.

**Autentizität**

Ehrliche und authentische Menschen sind sich selbst und ihren Mitmenschen gegenüber aufrichtig und ehrlich, halten ihre Versprechen und sind ihren Prinzipien treu. Sie legen Wert darauf, dass ihre Umgebung/Wirklichkeit nicht verfälscht wird. Sie sind fähig, für sich selbst die Verantwortung zu übernehmen. Authentische Menschen handeln in Übereinstimmung mit den eigenen Gedanken, Gefühlen und Überzeugungen.

**Enthusiasmus**

Menschen mit einem ausgeprägten Enthusiasmus und Tatendrang sind voller Energie und Lebensfreude und weisen eine ausgeprägte Begeisterungsfähigkeit für viele unterschiedliche Aktivitäten auf. Sie freuen sich auf jeden neuen Tag. Solche Menschen werden oft als energisch, flott, keck, munter und schwungvoll beschrieben. Sie setzen sich für ihre Aufgaben jeweils voll ein und bringen sie zu Ende.

**Liebe**

Menschen mit ausgeprägter Bindungsfähigkeit zeichnen sich dadurch aus, dass sie anderen Menschen ihre Liebe zeigen können und auch in der Lage sind, Liebe von anderen anzunehmen. Bei dieser Stärke handelt es sich um die Fähigkeit, enge Beziehungen und Freundschaften mit Mitmenschen aufzubauen, die von Zuneigung und Gegenseitigkeit gekennzeichnet sind. Diese Beziehungen zeichnen sich vor allem durch gegenseitige Hilfeleistung, Akzeptanz und Verpflichtung aus.

**Freundlichkeit**

Freundliche und grosszügige Menschen zeichnen sich dadurch aus, dass sie sehr nett und hilfsbereit zu anderen Menschen sind und ihnen gerne einen Gefallen tun, auch wenn sie die andere Person nicht gut kennen. Sie lieben es, andere glücklich zu machen. Freundliches und grosszügiges Verhalten kann auf ganz unterschiedliche Art und Weise gezeigt werden (z.B. im Bus den eigenen Platz freigeben, bei den Hausaufgaben helfen, Blut spenden). Zentral an dieser Stärke ist die Wertschätzung, die man anderen Menschen zukommen lässt.

**Soziale Intelligenz**

Menschen unterscheiden sich in der Fähigkeit, wichtige soziale Informationen, wie z.B. Gefühle, wahrzunehmen und zu verarbeiten. Sozial intelligente Menschen kennen ihre Motive und Gefühle und sie nehmen auch Unterschiede zwischen Menschen vor allem in Bezug zu deren Stimmungen, Motivationen und Absichten wahr. Sie kennen auch ihre eigenen Interessen und Fähigkeiten und sind in der Lage, sie zu fördern. Ein wichtiges Merkmal besteht darin, sich der jeweiligen Situation anzupassen.

**Teamfähigkeit**

Menschen mit dieser Stärke zeichnen sich durch ihre Teamfähigkeit und Verbundenheit gegenüber ihrer Gruppe aus. Sie können dann am besten arbeiten, wenn sie Teil einer Gruppe sind. Die Gruppenzugehörigkeit wird sehr hoch bewertet. Die eigenen Interessen werden meistens zugunsten der Gruppe zurückgesteckt. Teamfähige Menschen tragen oft eine soziale Verantwortung. Auch die getroffenen Entscheidungen der Gruppe werden respektiert und vor die eigenen Meinungen gestellt.

**Fairness**

Faire Menschen zeichnen sich durch einen ausgeprägten Sinn für Gerechtigkeit und Gleichheit aus. Jede Person wird gleich und fair behandelt, ungeachtet dessen, wer und was sie ist. Sie lassen sich in Entscheidungen nicht durch persönliche Gefühle beeinflussen, sondern versuchen, allen eine Chance zu geben. Die Bereitschaft zu Kompromissen (Mittelwegen) sowie das Zugebenkönnen von eigenen Fehlern werden als wichtige Merkmale dieser Stärke bezeichnet.

**Führungsvermögen**

Menschen mit einem ausgeprägten Führungsvermögen besitzen die Fähigkeit, einer Gruppe zu helfen gut miteinander zu arbeiten trotz unterschiedlichster Personen in der Gruppe. Ebenso zeichnen sie sich durch gute Planungs- und Organisationsfähigkeiten von Gruppenaktivitäten aus und können auch schwierige Entscheidungen treffen. Sie schaffen ein arbeitsförderndes Klima, unterstützen die gemeinsame Arbeit an Gruppenzielen und fördern das Zugehörigkeitsgefühl in der Gruppe, indem sie unterschiedliche Meinungen der Gruppenmitglieder einbeziehen können.

**Vergebungsbereitschaft**

Menschen mit dieser Stärke sind eher in der Lage, Vergangenes (z.B. einen Streit oder eine Meinungsverschiedenheit) ruhen zu lassen und einen Neuanfang zu wagen und können bis zu einem gewissen Punkt Verständnis aufbringen für die schlechte Behandlung durch andere Menschen. Sie geben ihren Mitmenschen eine Chance zur Wiedergutmachung. Der Prozess des Vergebens bzw. des Verzeihens beinhaltet heilsame und förderliche Veränderungen von Gedanken, Gefühlen und Verhaltensweisen bei Menschen, die von anderen verletzt wurden.

**Bescheidenheit**

Bescheidene Menschen zeichnen sich dadurch aus, dass sie nicht mit ihren Erfolgen prahlen, nicht gerne in der Menge auffallen und auch nicht die Aufmerksamkeit auf sich ziehen wollen. Sie ziehen es vor, andere reden zu lassen. Bescheidene Menschen können Fehler und Mängel zugeben. Bescheidenheit kann sich auch auf eine innere Haltung beziehen, die sich dadurch kennzeichnet, dass man sich nicht als Zentrum der Welt betrachtet.

**Vorsicht**

Kluge und vorsichtige Menschen zeichnen sich dadurch aus, dass sie Entscheidungen sorgfältig treffen, über mögliche Konsequenzen vor dem Sprechen und Durchführen nachdenken und Recht von Unrecht unterscheiden können. Sie vermeiden gefährliche körperliche Aktivitäten, was aber nicht heisst, dass sie neue Erfahrungen meiden. Sie werden von ihren Mitmenschen oft als vorsichtig im positiven Sinne bezeichnet. Mit ihren Fähigkeiten sind kluge Menschen in der Lage, längerfristige Ziele sorgfältig zu planen und zu verfolgen, ohne sich „kopflos&quot; in ein Abenteuer zu stürzen.

**Selbstregulation**

Menschen mit ausgeprägter Selbstregulation kontrollieren ihre Gefühle und ihr Verhalten in allen Situationen, z.B. ein Geheimnis für sich behalten, sich gesund ernähren, regelmässig Sport treiben, rechtzeitig Aufgaben erledigen. Sie zeichnen sich dadurch aus, dass sie längerfristigen Erfolg dem kurzfristigen vorziehen. Sie weisen eine starke Selbstdisziplin auf und merken aber gleichzeitig auch, wann es genug ist.

**Sinn für das Schöne**

Menschen, die in verschiedenen Lebensbereichen (wie z.B. Musik, Kunst, Natur, Sport, Wissenschaft) Schönes bewusst wahrnehmen, wertschätzen und sich darüber freuen können, haben einen ausgeprägten Sinn für das Schöne. Sie nehmen im Alltag schöne Dinge wahr, die von anderen übersehen oder nicht beachtet werden. Beim Anblick der Schönheit der Natur oder von Kunst empfinden sie tiefe Gefühle der Ehrfurcht und der Verwunderung und sind oft sprachlos. Es kommt auch vor, dass solche Menschen selber etwas Schönes schaffen, wie z.B. ein Bild malen.

**Dankbarkeit**

Dankbare Menschen zeichnen sich dadurch aus, dass sie sich bewusst sind über all die vielen Dinge in ihrem Leben, die nicht selbstverständlich sind. Sie nehmen sich die Zeit, ihre Dankbarkeit Menschen gegenüber auszudrücken. Wenn sie ein Geschenk bekommen, zeigen sie ihre Dankbarkeit. Sie realisieren, dass sie im Leben mit vielem gesegnet (beschenkt) sind. Die Dankbarkeit kann sich sowohl auf Menschen beziehen als auch auf nichtmenschliche Dinge (z.B. Tiere, Natur, Gott). Man kann die Dankbarkeit als gefühlvolle Antwort auf ein „Geschenk&quot; betrachten.

**Hoffnung**

Zuversichtliche und optimistische Menschen haben grundsätzlich eine positive Einstellung gegenüber der Zukunft. Sie können auch dann noch etwas positiv sehen, wenn es für andere negativ erscheint. Sie hoffen das Beste für die Zukunft und tun ihr Möglichstes, um ihre Ziele zu erreichen. Sie haben dabei ein klares Bild, was sie sich für die Zukunft wünschen und wie sie sich die Zukunft vorstellen. Und wenn es mal nicht klappt, dann versuchen sie trotz Herausforderungen oder Rückschlägen hoffnungsvoll in die Zukunft zu blicken.

**Humor**

Humorvolle und heitere Menschen lachen gerne und bringen andere Menschen gerne zum Lächeln oder zum Lachen. Sie versuchen ihre Freunde und Freundinnen aufzuheitern, wenn diese in einer bedrückten Stimmung sind. Menschen mit einem ausgeprägten Sinn für Humor versuchen in allen möglichen Situationen, Spass zu haben und versuchen alles was sie machen, mit ein bisschen Humor anzugehen. Humorvollen Menschen gelingt es auch, verschiedene Situationen von einer leichteren Seite her zu betrachten.

**Religiösität/Spiritualität**

Religiöse bzw. gläubige Menschen haben bestimmte Überzeugungen über den höheren Sinn und Zweck des Universums/der Welt. Sie glauben an eine höhere Macht bzw. an einen Gott. Ihre religiösen Überzeugungen beeinflussen ihr Denken, Handeln und Fühlen und können auch in schwierigen Zeiten eine Quelle des Trostes und der Kraft sein. Religiöse Menschen praktizieren ihre Religion, was sich durch unterschiedliche Verhaltensweisen zeigen kann, z.B. beten, meditieren, Kirchenbesuch oder Besinnung.
        ';
        $mail = array(
            "id_mailQueueStatus" => $this->db->get_lookup_id_by_code(mailQueueStatus, mailQueueStatus_queued),
            "date_to_be_sent" => date('Y-m-d H:i:s', time()),
            "from_email" => 'workwell@workwell.psy.unibe.ch',
            "from_name" => 'Workwell',
            "reply_to" => 'workwell@workwell.psy.unibe.ch',
            "recipient_emails" =>  $this->db->select_by_uid('users', $user_id)['email'],
            "subject" => 'Persönliche Rückmeldung für TeilnehmerIn mit dem Code: ' . $data[$moduleQualtrics::QUALTRICS_PARTICIPANT_VARIABLE],
            "body" => $body
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
            $result[] = '[workwell_evaluate_strenghts] Mail was queued for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
            if ($this->mail->send_mail_from_queue($mq_id, transactionBy_by_qualtrics_callback)) {
                $result[] = '[workwell_evaluate_strenghts] Mail was sent for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                    ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                    ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
            } else {
                $result[] = '[workwell_evaluate_strenghts] ERROR! Mail was not sent for user: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE] .
                    ' when survey: ' . $data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE] .
                    ' ' . $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE];
            }
        }
        return $result;
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
        $result[] = 'no functions';
        //get all actions for this survey and trigger type 
        $actions = $this->get_actions_with_functions($data[ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE], $data[ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE]);
        foreach ($actions as $action) {
            //clear the mail generation data
            unset($result);
            if ($this->is_user_in_group($user_id, $action['id_groups'])) {
                if (strpos($action['functions_code'], qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts) !== false) {
                    // WORKWELL evaluate strenghts function
                    $result[] = qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts;
                    $result[] = $this->workwell_evaluate_strenghts($data, $user_id);
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
}
?>
