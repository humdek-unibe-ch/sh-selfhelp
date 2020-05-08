<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/qualtrics_api_json_templates.php";

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
    const FLOW_TYPE_AUTHENTICATOR = 'Authenticator';

    /* Content Type */
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    /* values */
    const STAGE_TYPE_BASELINE = 'Baseline';
    const STAGE_TYPE_FOLLOWUP = 'Follow-up';
    const FLOW_ID_EMBEDED_DATA = 'FL_embedded_data';
    const FLOW_ID_WEB_SERVICE_CONTACTS = 'FL_ws_contacts';
    const FLOW_ID_WEB_SERVICE_GROUP = 'FL_ws_group';
    const FLOW_ID_FOLLOWUP_AUTHENTICATOR = 'FL_fu_auth';
    const QUALTRICS_API_SUCCESS = '200 - OK';
    const QUALTRICS_PARTICIPANT_VARIABLE = 'code';
    const QUALTRICS_GROUP_VARIABLE = 'group';
    const QUALTRICS_CALLBACK_KEY_VARIABLE = 'callback_key';

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
     * Execute curl and get/set data to Qualtrics
     * @param array $data
     * request_type, url, post_params
     * #retval bool or response
     */
    private function execute_curl($data)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, $this->get_default_qaltrics_curl_settings());
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $data['request_type']);
            curl_setopt($curl, CURLOPT_URL, $data['URL']);
            if (isset($data['post_params'])) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data['post_params']);
            }

            $response = curl_exec($curl);
            $response = json_decode($response, true);

            curl_close($curl);
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get survey flow via qualtrics api
     * @param string $survey_api_id qualtrics survey id
     * @retval array with flow structure
     */
    private function get_survey_flow($survey_api_id)
    {
        $data = array(
            "request_type" => "GET",
            "URL" => str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SET_SURVEY_FLOW)
        );
        $result = $this->execute_curl($data);
        return $result ? $result['result'] : $result;
    }

    /**
     * helper function to show the info from the requests
     * @param bool result
     * @param string text decription
     * @retval array
     */
    private function return_info($result, $text)
    {
        return array(
            "result" => $result,
            "description" => $text
        );
    }

    /**
     * Set survey flow via qualtrics api
     * @param string $survey_api_id qualtrics survey id
     * @param array $flow the flow structure
     * @retval array
     */
    private function set_survey_flow($survey_api_id, $flow)
    {
        $data = array(
            "request_type" => "PUT",
            "URL" => str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SET_SURVEY_FLOW),
            "post_params" => json_encode($flow)
        );
        $result = $this->execute_curl($data);
        if (!$result) {
            return $this->return_info(false, "Something went wrong");
        } else {
            if ($result['meta']['httpStatus'] === ModuleQualtricsProjectModel::QUALTRICS_API_SUCCESS) {
                return $this->return_info(true, "The survey was synchronized");
            } else {
                return $this->return_info(false, json_encode($result));
            }
        }
    }

    /**
     * generate the embedded flow and return nested array
     * @param array $survey
     * @retval array
     */
    private function get_embedded_flow($survey)
    {
        $embeddedData = array();
        $embeddedData[] = array(
            "Description" => $survey['participant_variable'],
            "Type" => "Recipient",
            "Field" => $survey['participant_variable'],
            "VariableType" => "String",
            "AnalyzeText" => false,
            "DataVisibility" => array()
        );
        if (isset($survey['group_variable'])) {
            //there is a randomization in the survey, prepare the group variable
            $embeddedData[] = array(
                "Description" => $survey['group_variable'],
                "Type" => "Recipient",
                "Field" => $survey['group_variable'],
                "VariableType" => "String",
                "AnalyzeText" => false,
                "DataVisibility" => array()
            );
        }
        $embedded = array(
            "Type" => ModuleQualtricsProjectModel::FLOW_TYPE_EMBEDDED_DATA,
            "FlowID" => ModuleQualtricsProjectModel::FLOW_ID_EMBEDED_DATA,
            "EmbeddedData" => $embeddedData
        );
        return $embedded;
    }

    /**
     * generate the followup Authenticator flow adn return nested array
     * @param string $participant_variable
     * @retval array
     */
    private function get_followup_authenticator($participant_variable)
    {
        $authenticator = json_decode(QulatricsAPIJsonTemplates::authenticator, true);
        $authenticator['FlowID'] = ModuleQualtricsProjectModel::FLOW_ID_FOLLOWUP_AUTHENTICATOR;
        $authenticator['PanelData']['LibraryID'] = $this->project['api_library_id'];
        $authenticator['PanelData']['PanelID'] = $this->project['api_mailing_group_id'];
        $authenticator['FieldData'][0][0]['embeddedDataField'] = $participant_variable;
        return $authenticator;
    }

    /**
     * generate the web service flow adn return nested array
     * @param array $embedded_vars
     * @param string $flowId
     * @param string $url
     * @param string $callback_key null if not set
     * @retval array
     */
    private function get_webService_flow($embedded_vars, $flowId, $url)
    {
        $is_callback = false;
        $editBodyParams = array();
        $body = array();
        foreach ($embedded_vars as $key => $var) {
            $editBodyParams[] = array(
                "key" => $key,
                "value" => ($key === ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE) ? $var : '${e://Field/' . $var . '}'
            );
            if($key === ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE){
                // check if the callbackkey is sent and if it is then the webservice is a callback to selfhelp
                $is_callback = true;
            }
            $body = array_merge(array("externalDataRef" => '${e://Field/' . $var . '}'));
        }
        $webService = array(
            "Type" => ModuleQualtricsProjectModel::FLOW_TYPE_WEB_SERVICE,
            "FlowID" => $flowId,
            "URL" => $url,
            "Method" => "POST",
            "RequestParams" => array(),
            "EditBodyParams" =>  $editBodyParams,
            "Body" => $body,
            "ContentType" => "application/json",
            "Headers" => $is_callback ? array() : $this->get_qualtrics_api_headers(),
            "ResponseMap" => array(),
            "FireAndForget" => false,
            "SchemaVersion" => 0,
            "StringifyValues" => true
        );
        if($is_callback){
            $webService['ContentType'] = ModuleQualtricsProjectModel::CONTENT_TYPE_FORM;
            //$webService['ResponseMap'] = array();
            $webService['ResponseMap'][] = array(
                "key" => "callback_status",
                "value" => "callback_status"
                );
        }
        return $webService;
    }

    /**
     * Synchronize baseline survey to qualtrics via the API
     * @param array @survey
     * @param @surveyFlow object
     * @retval array
     */
    private function sync_baseline_survey($survey, $surveyFlow)
    {
        if ($surveyFlow) {
            $baseline_embedded_flow = $this->get_embedded_flow($survey);
            $baseline_webService_contacts = $this->get_webService_flow(
                array("externalDataRef" => ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE),
                ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_CONTACTS,
                str_replace(
                    ':api_mailing_group_id',
                    $survey['api_mailing_group_id'],
                    ModuleQualtricsProjectModel::QUALTRICS_API_CREATE_CONTACT
                )
            );
            if (isset($survey['group_variable'])) {
                // web service for setting group
                $baseline_webService_group = $this->get_webService_flow(
                    array(
                        ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE => ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE,
                        ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE => ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE,
                        ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE => $this->db->get_callback_key()
                    ),
                    ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_GROUP,
                    $_SERVER['HTTP_HOST'] . $this->get_link_url("callback", array("class" => "CallbackSetGroup", "method" => "set_group"))
                );
            }
            foreach ($surveyFlow['Flow'] as $key => $flow) {
                if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_EMBEDED_DATA) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_embedded_flow;
                    $baseline_embedded_flow = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_CONTACTS) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_webService_contacts;
                    $baseline_webService_contacts = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_GROUP) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_webService_group;
                    $baseline_webService_group = false; //not needed anymore later when we check is it assign
                }
            }
            //check do we still have to add flows
            // order is important as we add as first. We should add the element that should be first as last call
            if ($baseline_webService_contacts) {
                // add baseline webService
                array_unshift($surveyFlow['Flow'], $baseline_webService_contacts);
            }
            if ($baseline_embedded_flow) {
                // add baseline embeded data
                array_unshift($surveyFlow['Flow'], $baseline_embedded_flow);
            }
            if ($baseline_webService_group) {
                // add baseline group web service
                array_push($surveyFlow['Flow'], $baseline_webService_group);
            }
            return $this->set_survey_flow($survey['qualtrics_survey_id'], $surveyFlow);
        } else {
            $this->return_info(false, "Something went wrong");
        }
    }

    /**
     * Synchronize followup survey to qualtrics via the API
     * @param @survey array
     * @param @surveyFlow object
     * @retval array
     */
    private function sync_followup_survey($survey, $surveyFlow)
    {
        if ($surveyFlow) {
            $followup_authenticator = $this->get_followup_authenticator($survey['participant_variable']);
            foreach ($surveyFlow['Flow'] as $key => $flow) {
                if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_FOLLOWUP_AUTHENTICATOR) {
                    //already exist; overwirite
                    $followup_authenticator['Flow'] = $surveyFlow['Flow'][$key]['Flow']; // keep what is inside the authenticator if it exists                    
                    $surveyFlow['Flow'][$key] = $followup_authenticator; //assign the new authenticator
                    $followup_authenticator = false; //not needed anymore later when we check, is it assign
                }
            }
            //check do we still have to add flows
            // order is important as we add as first. We should add the element that should be first as last call
            if ($followup_authenticator) {
                // add followup authenticaotr
                $followup_authenticator['Flow'] = $surveyFlow['Flow']; //move all blocks inside the authenticator
                unset($surveyFlow['Flow']); // clear the flow before assing the authenticator
                $surveyFlow['Flow'][] = $followup_authenticator; // assign the authenticator to the flow, now the authenticator keeps the rest of the flow inside
            }
            return $this->set_survey_flow($survey['qualtrics_survey_id'], $surveyFlow);
        } else {
            $this->return_info(false, "Something went wrong");
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
            "api_library_id" => $data['api_library_id'],
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
                "api_library_id" => $data['api_library_id'],
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
            return $this->sync_baseline_survey($survey, $surveyFlow);
        } else if ($survey['stage_type'] === ModuleQualtricsProjectModel::STAGE_TYPE_FOLLOWUP) {
            return $this->sync_followup_survey($survey, $surveyFlow);
        }
    }

    public function get_project()
    {
        return $this->project;
    }
}
