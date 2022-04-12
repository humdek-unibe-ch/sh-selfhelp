<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class QualtricsSurveyModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the selected survey.
     */
    private $survey_id;

    /**
     * If checked the survey can be done once per schedule
     */
    private $once_per_schedule;

    /**
     * If checked the survey can be done only once by an user. The checkbox `once_per_schedule` is ignore if this is checked
     */
    private $once_per_user;

    /**
     * Start time when the survey should be available
     */
    private $start_time;

    /**
     * End time when the survey should be not available anymore
     */
    private $end_time;

    /**
     * Start time converted to date
     */
    private $start_time_calced;

    /**
     * End time converted to date and adjusted if smaller than start time
     */
    private $end_time_calced;


    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section to which this style is assigned.
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
        $this->survey_id = $this->get_db_field("qualtricsSurvey");
        $this->once_per_schedule = $this->get_db_field('once_per_schedule', 0);
        $this->once_per_user = $this->get_db_field('once_per_user', 0);
        $this->use_as_container = $this->get_db_field('use_as_container', 0);
        $this->start_time = $this->get_db_field('start_time', '00:00');
        $this->end_time = $this->get_db_field('end_time', '00:00');
        $this->calc_times();
    }

    /* Private Methods *********************************************************/

    private function calc_times()
    {
        $d = new DateTime();
        $now = $d->setTimestamp(strtotime("now"));
        $at_start_time = explode(':', $this->start_time);
        $at_end_time = explode(':', $this->end_time);
        $start_time = $now->setTime($at_start_time[0], $at_start_time[1]);
        $start_time = date('Y-m-d H:i:s', $start_time->getTimestamp());
        $end_time = $now->setTime($at_end_time[0], $at_end_time[1]);
        $end_time = date('Y-m-d H:i:s', $end_time->getTimestamp());
        if (strtotime($start_time) > strtotime($end_time)) {
            // move end time to next day
            $end_time = date('Y-m-d H:i:s', strtotime($end_time . ' +1 day'));
        }
        $this->start_time_calced = $start_time;
        $this->end_time_calced = $end_time;
    }

    /**
     * Check if the survey is already done by the user
     * @retval boolean
     * true if it is already done, false if not
     */
    private function is_survey_done_by_user()
    {
        $sql = "SELECT survey_response_id
                FROM qualtricsSurveysResponses
                WHERE id_users = :id_users AND id_surveys = :id_surveys AND id_qualtricsProjectActionTriggerTypes = :id_qualtricsProjectActionTriggerTypes;";
        $res = $this->db->query_db_first($sql, array(
            ':id_users' => $_SESSION['id_user'],
            ':id_surveys' => $this->survey_id,
            ':id_qualtricsProjectActionTriggerTypes' => $this->db->get_lookup_id_by_value(qualtricsProjectActionTriggerTypes, qualtricsProjectActionTriggerTypes_finished)
        ));
        return $res;
    }

    /**
     * Check if the survey is already done by the user for the selected period
     * @retval boolean
     * true if it is already done, false if not
     */
    private function is_survey_done_by_user_for_schedule()
    {
        $sql = "SELECT survey_response_id
                FROM qualtricsSurveysResponses
                WHERE id_users = :id_users AND id_surveys = :id_surveys AND id_qualtricsProjectActionTriggerTypes = :id_qualtricsProjectActionTriggerTypes
                AND (edited_on BETWEEN :start_time AND :end_time);";
        $res = $this->db->query_db_first($sql, array(
            ':id_users' => $_SESSION['id_user'],
            ':id_surveys' => $this->survey_id,
            ':id_qualtricsProjectActionTriggerTypes' => $this->db->get_lookup_id_by_value(qualtricsProjectActionTriggerTypes, qualtricsProjectActionTriggerTypes_finished),
            ':start_time' => $this->start_time_calced,
            ':end_time' => $this->end_time_calced
        ));
        return $res;
    }

    /* Public Methods *********************************************************/

    /**
     * Generate the quatrics survey link 
     * Check for additional url parameters and assign them if there are some in the same way it is done in Qualtrics
     * 
     * @retval string return the link which used in the iFrame
     */
    public function get_survey_link()
    {        
        $url_components = parse_url($this->router->get_url('#self')); // get the requested url
        $extra_qualtrics_params = isset($url_components['query'])? $url_components['query'] : ''; // check if the url contains url parameters (the same format as Qualtrics)
        $survey_info = $this->db->query_db_first('SELECT qualtrics_survey_id, participant_variable FROM qualtricsSurveys WHERE id = :id', array(':id' => $this->survey_id));
        $survey_link = '';
        if ($survey_info) {
            $survey_link =  'https://eu.qualtrics.com/jfe/form/' . $survey_info['qualtrics_survey_id'];
            if (isset($survey_info['participant_variable']) && $survey_info['participant_variable'] != '') {
                $user_code = $this->db->get_user_code();
                if ($user_code) {
                    $survey_link =  $survey_link . '?' . $survey_info['participant_variable'] . '=' . $user_code;
                    if($extra_qualtrics_params != ''){
                        $survey_link = $survey_link . '&' . $extra_qualtrics_params; // assign the extra parameters after the user_code variable
                    }
                }else if($extra_qualtrics_params != ''){
                    $survey_link = $survey_link . '?' . $extra_qualtrics_params; // assign the extra parameters 
                }
            }
        }
        return $survey_link;
    }

    /**
     * Check if the survey is active
     * @retval boolean
     * true if it is active, false if it is not active
     */
    public function is_survey_active()
    {
        if ($this->start_time == $this->end_time) {
            // survey is always active
            return true;
        } else {
            if (strtotime($this->start_time_calced) <= strtotime("now") && strtotime("now") <= strtotime($this->end_time_calced)) {
                // the survey is active
                return true;
            } else {
                // survey is not active right now
                return false;
            }
        }
    }

    /**
     * Check if the survey is done; if once_per_schedule is not enabled it will return always false
     * @retval boolean
     * true if it is active, false if it is not active
     */
    public function is_survey_done()
    {
        if ($this->once_per_user) {
            // the survey can be filled only once per user
            return $this->is_survey_done_by_user();
        } else if ($this->once_per_schedule) {
            // the survey can be filled once per schedule
            return $this->is_survey_done_by_user_for_schedule();
        } else {
            // survey can be filled as many times per schedule
            return false;
        }
    }
}
