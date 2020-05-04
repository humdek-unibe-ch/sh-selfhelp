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
class ModuleQualtricsProjectModel extends BaseModel
{

    /* Constants ************************************************/
    const QUALTRICS_API_GET_SURVEY_FLOW = 'https://psyunibe.eu.qualtrics.com/API/v3/survey-definitions/:survey_api_id/flow';

    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/
    /**
     * project id,
     */
    private $pid;

    /**
     * project object
     */
    private $project;


    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $pid)
    {
        parent::__construct($services);
        $this->pid = $pid;
        $this->project = $this->db->select_by_uid("qualtricsProjects", $this->pid);
    }

    private function get_qualtrics_api()
    {
        return "X-API-TOKEN: " . $this->project['qualtrics_api'];
    }

    private function get_default_qaltrics_curl_settings()
    {
        return array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 100,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                $this->get_qualtrics_api()
            )
        );
    }

    /**
     * Get survey flow via qualtrics api
     * @param string $survey_api_id qualtrics survey id
     * @retval array with flow structure
     */
    private function get_survey_flow($survey_api_id)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, $this->get_default_qaltrics_curl_settings());
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_URL, str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SURVEY_FLOW));

            if (DEBUG) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //remove in prodcution
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //remove in prodcution
            }

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $response = curl_exec($curl);
            $curlError = curl_error($curl);

            curl_close($curl);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Insert a new qualtrics project to the DB.
     *
     * @param array $data
     *  name, description, api_mailing_group_id
     * @retval int
     *  The id of the new project or false if the process failed.
     */
    public function insert_new_project($data)
    {
        return $this->db->insert("qualtricsProjects", array(
            "name" => $data['name'],
            "description" => $data['description'],
            "qualtrics_api" => $data['qualtrics_api'],
            "api_mailing_group_id" => $data['api_mailing_group_id'],
            "participent_variable" => $data['participent_variable']
        ));
    }

    /**
     * Update qualtrics project.
     *
     * @param array $data
     *  id, name, description, api_mailing_group_id
     * @retval int
     *  The number of the updated rows
     */
    public function update_project($data)
    {
        return $this->db->update_by_ids(
            "qualtricsProjects",
            array(
                "name" => $data['name'],
                "description" => $data['description'],
                "qualtrics_api" => $data['qualtrics_api'],
                "api_mailing_group_id" => $data['api_mailing_group_id'],
                "participent_variable" => $data['participent_variable']
            ),
            array('id' => $data['id'])
        );
    }

    /**
     * Fetch all qualtrics projects from the database
     *
     * @retval array $project
     * id
     * name
     * description
     * api_mailing_group_id
     */
    public function get_projects()
    {
        return $this->db->select_table('qualtricsProjects');
    }

    /**
     * get db
     */
    public function get_db()
    {
        return $this->db;
    }

    /**
     * Get all the stages for the project
     * @param int $pid
     * project id
     * @retval array $stages
     */
    public function get_stages($pid)
    {
        $sql = "SELECT *
                FROM view_qualtricsStages
                WHERE project_id = :pid";
        return $this->db->query_db($sql, array(":pid" => $pid));
    }

    /**
     * Synchfonize a survey to qualtrics using qualtrics API
     * @param array $survey
     * @retval bool 
     */
    public function syncSurvey($survey)
    {
        $this->get_survey_flow($survey['qualtrics_survey_id']);
    }

    public function get_project()
    {
        return $this->project;
    }
}
