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


    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section to which this style is assigned.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->survey_id = $this->get_db_field("qualtricsSurvey");
    }

    /**
     * Generate the quatrics survey link based on the selected stage id
     * 
     * @retval string return the link which used in the iFrame
     */
    public function get_survey_link()
    {
        $survey_info = $this->db->query_db_first('SELECT qualtrics_survey_id, participant_variable FROM qualtricsSurveys WHERE id = :id', array(':id' => $this->survey_id));
        $survey_link = '';
        if ($survey_info) {
            $survey_link =  'https://eu.qualtrics.com/jfe/form/' . $survey_info['qualtrics_survey_id'];
            if (isset($survey_info['participant_variable']) && $survey_info['participant_variable'] != '') {
                $user_code = $this->db->query_db_first('SELECT code
                                        FROM validation_codes vc
                                        INNER JOIN users u ON (u.id = vc.id_users)
                                        WHERE u.id = :id', array(':id' => $_SESSION['id_user']));
                if (isset($user_code['code'])) {
                    $survey_link =  $survey_link . '?' . $survey_info['participant_variable'] . '=' . $user_code['code'];
                }
            }
        }
        return $survey_link;
    }
}
