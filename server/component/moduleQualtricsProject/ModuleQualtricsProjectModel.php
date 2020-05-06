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

    /* API calls */
    const QUALTRICS_API_GET_SET_SURVEY_FLOW = 'https://psyunibe.eu.qualtrics.com/API/v3/survey-definitions/:survey_api_id/flow';
    const QUALTRICS_API_CREATE_CONTACT = 'https://env.qualtrics.com/API/v3/mailinglists/:api_mailing_group_id/contacts';

    /* Qualtrics flow types */
    const FLOW_TYPE_EMBEDDED_DATA = 'EmbeddedData';
    const FLOW_TYPE_WEB_SERVICE = 'WebService';

    /* values */
    const STAGE_TYPE_BASELINE = 'Baseline';
    const STAGE_TYPE_FOLLOWUP = 'Follow-up';
    const FLOW_ID_BASELINE_EMBEDED_DATA = 'FL_bl_embeded_data';
    const FLOW_ID_BASELINE_WEB_SERVICE = 'FL_bl_web_service';
    const QUALTRICS_API_SUCCESS = '200 - OK';

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

    private function get_qualtrics_api_headers()
    {
        $headers = array();
        $header = array(
            "key" => "X-API-TOKEN",
            "value" => $this->project['qualtrics_api']
        );
        $headers[] = $header;
        return $headers;
    }

    private function get_default_qaltrics_curl_settings()
    {
        $arr = array(
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

        if (DEBUG) {
            //skip ssl checks for local testing
            $arr[CURLOPT_SSL_VERIFYHOST] = false;
            $arr[CURLOPT_SSL_VERIFYPEER] = false;
        }

        return $arr;
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
            curl_setopt($curl, CURLOPT_URL, str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SET_SURVEY_FLOW));

            $response = curl_exec($curl);
            $response = json_decode($response, true);
            $result = $response['result'];

            curl_close($curl);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Set survey flow via qualtrics api
     * @param string $survey_api_id qualtrics survey id
     * @param array $flow the flow structure
     * @retval bool
     */
    private function set_survey_flow($survey_api_id, $flow)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, $this->get_default_qaltrics_curl_settings());
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_URL, str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SET_SURVEY_FLOW));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($flow));

            $response = curl_exec($curl);
            $response = json_decode($response, true);            
            
            curl_close($curl);
            return $response['meta']['httpStatus'] === ModuleQualtricsProjectModel::QUALTRICS_API_SUCCESS;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * generate the baseline embedded flow adn return nested array
     * @param string $participant_variable
     * @retval array
     */
    private function get_baseline_embedded_flow($participant_variable)
    {
        $embeddedData = array();
        $embeddedData[] = array(
                "Description" => $participant_variable,
                "Type" => "Recipient",
                "Field" => $participant_variable,
                "VariableType" => "String",
                "AnalyzeText" => false,
                "DataVisibility" => array()
            );
        $embedded = array(
            "Type" => ModuleQualtricsProjectModel::FLOW_TYPE_EMBEDDED_DATA,
            "FlowID" => ModuleQualtricsProjectModel::FLOW_ID_BASELINE_EMBEDED_DATA,
            "EmbeddedData" => $embeddedData
        );
        return $embedded;
    }

    /**
     * generate the baseline web service flow adn return nested array
     * @param array $survey
     * @retval array
     */
    private function get_baseline_webService_flow($survey)
    {
        $editBodyParams = array();
        $editBodyParams[] = array(
                "key" => "externalDataRef",
                "value" => '${e://Field/' . $survey['participant_variable'] . '}'
            );
        $webService = array(
            "Type" => ModuleQualtricsProjectModel::FLOW_TYPE_WEB_SERVICE,
            "FlowID" => ModuleQualtricsProjectModel::FLOW_ID_BASELINE_WEB_SERVICE,
            "URL" => str_replace(':api_mailing_group_id', $survey['api_mailing_group_id'], ModuleQualtricsProjectModel::QUALTRICS_API_CREATE_CONTACT),
            "Method" => "POST",
            "RequestParams" => array(),
            "EditBodyParams" =>  $editBodyParams,
            "Body" => array(
                "externalDataRef" => '${e://Field/' . $survey['participant_variable'] . '}'
            ),
            "ContentType" => "application/json",
            "Headers" => $this->get_qualtrics_api_headers(),
            "ResponseMap" => array(),
            "FireAndForget" => false,
            "SchemaVersion" => 0,
            "StringifyValues" => true
        );
        return $webService;
    }

    /**
     * Synchronize baseline survey to qualtrics via the API
     * @param array @survey
     * @param @surveyFlow object
     * @retval bool
     */
    private function sync_baseline_survey($survey, $surveyFlow)
    {
        if ($surveyFlow) {
            $baseline_embedded_flow = $this->get_baseline_embedded_flow($survey['participant_variable']);
            $baseline_webService_flow = $this->get_baseline_webService_flow($survey);
            foreach ($surveyFlow['Flow'] as $key => $flow) {
                if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_BASELINE_EMBEDED_DATA) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_embedded_flow;
                    $baseline_embedded_flow = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_BASELINE_WEB_SERVICE) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_webService_flow;
                    $baseline_webService_flow = false; //not needed anymore later when we check is it assign
                }
            }
            //check do we still have to add flows
            // order is important as we add as first. We should add the element that should be first as last call
            if ($baseline_webService_flow) {
                // add baseline webService
                array_unshift($surveyFlow['Flow'], $baseline_webService_flow);
            }
            if ($baseline_embedded_flow) {
                // add baseline embeded data
                array_unshift($surveyFlow['Flow'], $baseline_embedded_flow);
            }
            $this->set_survey_flow($survey['qualtrics_survey_id'], $surveyFlow);
        }
    }

    /**
     * Synchronize followup survey to qualtrics via the API
     * @param @survey array
     * @param @surveyFlow object
     * @retval bool
     */
    private function sync_followup_survey($survey, $surveyFlow)
    {
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
            "participant_variable" => $data['participant_variable']
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
                "participant_variable" => $data['participant_variable']
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
        $surveyFlow = $this->get_survey_flow($survey['qualtrics_survey_id']);
        if ($survey['stage_type'] === ModuleQualtricsProjectModel::STAGE_TYPE_BASELINE) {
            $this->sync_baseline_survey($survey, $surveyFlow);
        } else if ($survey['stage_type'] === ModuleQualtricsProjectModel::STAGE_TYPE_FOLLOWUP) {
            $this->sync_followup_survey($survey, $surveyFlow);
        }
    }

    public function get_project()
    {
        return $this->project;
    }
}
