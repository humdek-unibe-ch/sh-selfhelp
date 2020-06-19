<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/qualtrics_api_templates.php";

/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ModuleQualtricsProjectModel extends BaseModel
{

    /* Constants ************************************************/

    /* API calls */
    const QUALTRICS_API_GET_SET_SURVEY_FLOW = 'https://eu.qualtrics.com/API/v3/survey-definitions/:survey_api_id/flow';
    const QUALTRICS_API_GET_SET_SURVEY_OPTIONS = 'https://eu.qualtrics.com/API/v3/survey-definitions/:survey_api_id/options';
    const QUALTRICS_API_CREATE_CONTACT = 'https://eu.qualtrics.com/API/v3/mailinglists/:api_mailing_group_id/contacts';

    /* Qualtrics flow types */
    const FLOW_TYPE_EMBEDDED_DATA = 'EmbeddedData';
    const FLOW_TYPE_WEB_SERVICE = 'WebService';
    const FLOW_TYPE_AUTHENTICATOR = 'Authenticator';

    /* Content Type */
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    /* Flow IDs start with FL_ then max 15 characters*/
    const FLOW_ID_EMBEDED_DATA = 'FL_embedded_data';
    const FLOW_ID_WEB_SERVICE_CONTACTS = 'FL_ws_contacts';
    const FLOW_ID_WEB_SERVICE_GROUP = 'FL_ws_group';
    const FLOW_ID_WEB_SERVICE_START = 'FL_ws_start';
    const FLOW_ID_WEB_SERVICE_END = 'FL_ws_end';
    const FLOW_ID_AUTHENTICATOR = 'FL_auth';
    const FLOW_ID_AUTHENTICATOR_CONTACT = 'FL_999999'; //'FL_auth_cont'; // THIS SHOULD BE ONLY NUMBERS AFTER FL_
    const FLOW_ID_AUTHENTICATOR_CONTACT_EMBEDDED_DATA = 'FL_auth_cont_ed';
    const FLOW_ID_BRANCH_CONTACT_EXIST = 'FL_br_cont_ex';
    const QUALTRICS_API_SUCCESS = '200 - OK';

    /* values */
    const QUALTRICS_PARTICIPANT_VARIABLE = 'code';
    const QUALTRICS_GROUP_VARIABLE = 'group';
    const QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE = 'ResponseID';
    const QUALTRICS_SURVEY_ID_VARIABLE = 'SurveyID';
    const QUALTRICS_CALLBACK_KEY_VARIABLE = 'callback_key';
    const QUALTRICS_TRIGGER_TYPE_VARIABLE = 'trigger_type';
    const QUALTRICS_EMBEDED_SESSION_ID_VAR = '${e://Field/ResponseID}';
    const QUALTRICS_EMBEDED_SURVEY_ID_VAR = '${e://Field/SurveyID}';
    const QUALTRICS_CALLBACK_STATUS = 'callback_status';
    const SELFEHLP_HEADER_HIDE_QUALTRIC_LOGO = 'selfhelp_hideQualtricsLogo';
    const SELFEHLP_HEADER_IFRAME_RESIZER = 'selfhelp_iFrameResizer';

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
     * Synchronize survey header; Get the header and check if the selfhelp is appended; if it is not we add it.
     * It adds hideQualtrics logo and iFrame resizer
     * @param string $survey_api_id qualtrics survey id
     * @retval array with result
     */
    private function sync_survey_header($survey_api_id)
    {
        //get survey options; they contain the survey header
        $data = array(
            "request_type" => "GET",
            "URL" => str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SET_SURVEY_OPTIONS)
        );
        $survey_options = $this->execute_curl($data);
        if ($survey_options !== false) {
            $survey_header = $survey_options['result']['Header'];
            $html = ''; //init no header we still need emty string
            if ($survey_header != '') {
                $dom = new DOMDocument();
                $dom->validateOnParse = true;
                $dom->loadHTML($survey_header);
                $dom->preserveWhiteSpace = false;
                /* Remove hideQualtrticsLogo if exists*/
                $hideQualtricsLogo = $dom->getElementById(ModuleQualtricsProjectModel::SELFEHLP_HEADER_HIDE_QUALTRIC_LOGO);
                if ($hideQualtricsLogo) {
                    $hideQualtricsLogo->parentNode->removeChild($hideQualtricsLogo);
                }
                /* Remove iFramreResizer if exists */
                $iFrameResizer = $dom->getElementById(ModuleQualtricsProjectModel::SELFEHLP_HEADER_IFRAME_RESIZER);
                if ($iFrameResizer) {
                    $iFrameResizer->parentNode->removeChild($iFrameResizer);
                }
                $html = $dom->saveHTML(); //save the html value of the header
            }
            $html = $html . QulatricsAPITemplates::hideQualtricsLogo . QulatricsAPITemplates::iFrameResizer;
            $survey_options['result']['Header'] = $html;
            return $this->set_survey_options($survey_api_id, $survey_options['result']);
        } else {
            return $this->return_info(false, 'Get survey options failed');
        }
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
     * helper function to show the info from the requests which combine multiple results
     * @param array resultsArray
     * @retval array
     */
    private function multi_return_info($resultsArray)
    {
        $res = array(
            "result" => true,
            "description" => ''
        );
        foreach ($resultsArray as $key => $arr) {
            $res['result'] = $res['result'] && $arr['result'];
            if ($res['description'] == '') {
                $res['description'] = $arr['description'];
            } else {
                $res['description'] = $res['description'] . '; ' . $arr['description'];
            }
        }
        return $res;
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
            return $this->return_info(false, "Something went wrong assinging the survey flow");
        } else {
            if ($result['meta']['httpStatus'] === ModuleQualtricsProjectModel::QUALTRICS_API_SUCCESS) {
                return $this->return_info(true, "The survey flow was synchronized");
            } else {
                return $this->return_info(false, json_encode($result));
            }
        }
    }

    /**
     * Set survey options via qualtrics api
     * @param string $survey_api_id qualtrics survey id
     * @param array $options the options structure
     * @retval array
     */
    private function set_survey_options($survey_api_id, $options)
    {
        $data = array(
            "request_type" => "PUT",
            "URL" => str_replace(':survey_api_id', $survey_api_id, ModuleQualtricsProjectModel::QUALTRICS_API_GET_SET_SURVEY_OPTIONS),
            "post_params" => json_encode($options)
        );
        $result = $this->execute_curl($data);
        if (!$result) {
            return $this->return_info(false, "Something went wrong with assigning survey options");
        } else {
            if ($result['meta']['httpStatus'] === ModuleQualtricsProjectModel::QUALTRICS_API_SUCCESS) {
                return $this->return_info(true, "The survey options were synchronized");
            } else {
                return $this->return_info(false, json_encode($result));
            }
        }
    }

    /**
     * generate the  Authenticator flow adn return nested array
     * @param string $participant_variable
     * @retval array
     */
    private function get_authenticator($participant_variable, $flow_id = ModuleQualtricsProjectModel::FLOW_ID_AUTHENTICATOR, $max_attempts = 100)
    {
        $authenticator = json_decode(QulatricsAPITemplates::authenticator, true);
        $authenticator['FlowID'] = $flow_id;
        $authenticator['PanelData']['LibraryID'] = $this->project['api_library_id'];
        $authenticator['PanelData']['PanelID'] = $this->project['api_mailing_group_id'];
        $authenticator['FieldData'][0][0]['embeddedDataField'] = $participant_variable;
        $authenticator['Options']['maxAttempts'] = $max_attempts;
        return $authenticator;
    }

    /**
     * generate the web service flow adn return nested array
     * @param array $embedded_vars
     * @param string $flowId
     * @param string $url
     * @param string $participant_varaible
     * @param string $callback_key null if not set     
     * @retval array
     */
    private function get_webService_flow($embedded_vars, $flowId, $url, $participant_variable, $is_callback)
    {
        $body = array();
        $body = array_merge(array("externalDataRef" => '${e://Field/' . $participant_variable . '}'));
        if ($is_callback) {
            //for callbacks different structure
            $body = $embedded_vars;
        }
        $webService = array(
            "Type" => ModuleQualtricsProjectModel::FLOW_TYPE_WEB_SERVICE,
            "FlowID" => $flowId,
            "URL" => $url,
            "Method" => "POST",
            "RequestParams" => array(),
            "EditBodyParams" =>  $embedded_vars,
            "Body" => $body,
            "ContentType" => "application/json",
            "Headers" => $is_callback ? array() : $this->get_qualtrics_api_headers(),
            "ResponseMap" => array(),
            "FireAndForget" => false,
            "SchemaVersion" => 0,
            "StringifyValues" => true
        );
        if ($is_callback) {
            $webService['ContentType'] = ModuleQualtricsProjectModel::CONTENT_TYPE_FORM;
            //$webService['ResponseMap'] = array();
            $webService['ResponseMap'][] = array(
                "key" => ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS,
                "value" => ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_STATUS . "_" . $flowId,
            );
        }
        return $webService;
    }

    private function get_webService_finish_flow($survey)
    {
        $editBodyParamsEnd[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE,
            "value" => '${e://Field/' . $survey['participant_variable'] . '}'
        );
        $editBodyParamsEnd[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE,
            "value" => ModuleQualtricsProjectModel::QUALTRICS_EMBEDED_SURVEY_ID_VAR
        );
        $editBodyParamsEnd[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE,
            "value" => ModuleQualtricsProjectModel::QUALTRICS_EMBEDED_SESSION_ID_VAR
        );
        $editBodyParamsEnd[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE,
            "value" => $this->db->get_callback_key()
        );
        $editBodyParamsEnd[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE,
            "value" => qualtricsProjectActionTriggerTypes_finished
        );
        return $this->get_webService_flow(
            $editBodyParamsEnd,
            ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_END,
            'http://' . $_SERVER['HTTP_HOST'] . $this->get_link_url("callback", array("class" => "CallbackQualtrics", "method" => "add_survey_response")),
            $survey['participant_variable'],
            true
        );
    }

    private function get_webService_start_flow($survey)
    {
        $editBodyParamsStart[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE,
            "value" => '${e://Field/' . $survey['participant_variable'] . '}'
        );
        $editBodyParamsStart[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_SURVEY_ID_VARIABLE,
            "value" => ModuleQualtricsProjectModel::QUALTRICS_EMBEDED_SURVEY_ID_VAR
        );
        $editBodyParamsStart[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_SURVEY_RESPONSE_ID_VARIABLE,
            "value" => ModuleQualtricsProjectModel::QUALTRICS_EMBEDED_SESSION_ID_VAR
        );
        $editBodyParamsStart[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE,
            "value" => $this->db->get_callback_key()
        );
        $editBodyParamsStart[] = array(
            "key" => ModuleQualtricsProjectModel::QUALTRICS_TRIGGER_TYPE_VARIABLE,
            "value" => qualtricsProjectActionTriggerTypes_started
        );
        return $this->get_webService_flow(
            $editBodyParamsStart,
            ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_START,
            'http://' . $_SERVER['HTTP_HOST'] . $this->get_link_url("callback", array("class" => "CallbackQualtrics", "method" => "add_survey_response")),
            $survey['participant_variable'],
            true
        );
    }

    /**
     * Synchronize baseline survey to qualtrics via the API
     * @param array @survey
     * @param object @surveyFlow
     * @retval array
     */
    private function sync_baseline_survey($survey, $surveyFlow)
    {
        if ($surveyFlow) {

            /** EMBEDED DATA variables *************************************************************************************************************************************/
            $baseline_embedded_flow = json_decode(QulatricsAPITemplates::embedded_data, true);
            $baseline_embedded_flow['FlowID'] = ModuleQualtricsProjectModel::FLOW_ID_EMBEDED_DATA;
            $baseline_embedded_flow['EmbeddedData'][] = array(
                "Description" => $survey['participant_variable'],
                "Type" => "Recipient",
                "Field" => $survey['participant_variable'],
                "VariableType" => "String",
                "DataVisibility" => array(),
                "AnalyzeText" => false
            );
            $baseline_embedded_flow['EmbeddedData'][] = array(
                "Description" => 'user_registered',
                "Type" => "Custom",
                "Field" => 'user_registered',
                "VariableType" => "String",
                "DataVisibility" => array(),
                "AnalyzeText" => false,
                "Value" => "false"
            );
            if ($survey['group_variable'] == 1) {
                //there is a randomization in the survey, prepare the group variable
                $baseline_embedded_flow['EmbeddedData'][] = array(
                    "Description" => ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE,
                    "Type" => "Recipient",
                    "Field" => ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE,
                    "VariableType" => "String",
                    "DataVisibility" => array(),
                    "AnalyzeText" => false
                );
            }

            /** AUTHENTICATOR is the user registered *************************************************************************************************************************************/
            $baseline_authenticator = $this->get_authenticator($survey['participant_variable'], ModuleQualtricsProjectModel::FLOW_ID_AUTHENTICATOR_CONTACT, '1');
            $embeded_data_authenticator_contact = json_decode(QulatricsAPITemplates::embedded_data, true);
            $embeded_data_authenticator_contact['EmbeddedData'][] = array(
                "Description" => 'user_registered',
                "Type" => "Custom",
                "Field" => 'user_registered',
                "VariableType" => "String",
                "DataVisibility" => array(),
                "AnalyzeText" => false,
                "Value" => "true"
            );
            $embeded_data_authenticator_contact['FlowID'] = ModuleQualtricsProjectModel::FLOW_ID_AUTHENTICATOR_CONTACT_EMBEDDED_DATA;
            $baseline_authenticator['Flow'][] = $embeded_data_authenticator_contact;

            /** BRANCH if user is not registered, add him/her to to the list *************************************************************************************************************************************/

            $editBodyParams[] = array(
                "key" => 'externalDataRef',
                "value" => '${e://Field/' . $survey['participant_variable'] . '}'
            );

            $baseline_webService_contacts = $this->get_webService_flow(
                $editBodyParams,
                ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_CONTACTS,
                str_replace(
                    ':api_mailing_group_id',
                    $survey['api_mailing_group_id'],
                    ModuleQualtricsProjectModel::QUALTRICS_API_CREATE_CONTACT
                ),
                $survey['participant_variable'],
                false
            );
            $branch_contact_exists = json_decode(QulatricsAPITemplates::branch_contact_exist, true);
            $branch_contact_exists['FlowID'] = ModuleQualtricsProjectModel::FLOW_ID_BRANCH_CONTACT_EXIST;
            $branch_contact_exists['Flow'][] = $baseline_webService_contacts;

            /** START SURVEY WEB SERVICE *******************************************************************************************************************************/

            $baseline_webService_start = $this->get_webService_start_flow($survey);

            /** END SURVEY WEB SERVICE *******************************************************************************************************************************/

            $baseline_webService_end = $this->get_webService_finish_flow($survey);

            /** GROUP WEB SERVICE if there is grouping *************************************************************************************************************************************/
            if ($survey['group_variable'] == 1) {
                // web service for setting group
                $editBodyParamsGroup[] = array(
                    "key" => ModuleQualtricsProjectModel::QUALTRICS_PARTICIPANT_VARIABLE,
                    "value" => '${e://Field/' . $survey['participant_variable'] . '}'
                );
                $editBodyParamsGroup[] = array(
                    "key" => ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE,
                    "value" => '${e://Field/' . ModuleQualtricsProjectModel::QUALTRICS_GROUP_VARIABLE . '}'
                );
                $editBodyParamsGroup[] = array(
                    "key" => ModuleQualtricsProjectModel::QUALTRICS_CALLBACK_KEY_VARIABLE,
                    "value" => $this->db->get_callback_key()
                );
                $baseline_webService_group = $this->get_webService_flow(
                    $editBodyParamsGroup,
                    ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_GROUP,
                    'http://' . $_SERVER['HTTP_HOST'] . $this->get_link_url("callback", array("class" => "CallbackQualtrics", "method" => "set_group")),
                    $survey['participant_variable'],
                    true
                );
            }

            /** LOOP IF FLOWS EXISTS, EDIT THEM **********************************************************************************************************************************/
            foreach ($surveyFlow['Flow'] as $key => $flow) {
                if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_EMBEDED_DATA) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_embedded_flow;
                    $baseline_embedded_flow = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_AUTHENTICATOR_CONTACT) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_authenticator;
                    $baseline_authenticator = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_BRANCH_CONTACT_EXIST) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $branch_contact_exists;
                    $branch_contact_exists = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_START) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_webService_start;
                    $baseline_webService_start = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_END) {
                    //already exist; overwirite
                    $surveyFlow['Flow'][$key] = $baseline_webService_end;
                    $baseline_webService_end = false; //not needed anymore later when we check is it assign
                } else if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_GROUP) {
                    //already exist; overwirite
                    if (!isset($baseline_webService_group)) {
                        //should not exist; remove it
                        unset($surveyFlow['Flow'][$key]);
                    } else {
                        // add it
                        $surveyFlow['Flow'][$key] = $baseline_webService_group;
                    }
                    $baseline_webService_group = false; //not needed anymore later when we check is it assign
                }
            }

            /** IF FLOW DOESN NOT EXIST, ADD THEM **********************************************************************************************************************************/

            //check do we still have to add flows
            // order is important as we add as first. We should add the element that should be first as last call
            if ($baseline_webService_start) {
                // add baseline webService for starting the survey
                array_unshift($surveyFlow['Flow'], $baseline_webService_start);
            }
            if ($branch_contact_exists) {
                // add baseline webService with the branch check
                array_unshift($surveyFlow['Flow'], $branch_contact_exists);
            }
            if ($baseline_authenticator) {
                // add baseline authenticaotr
                array_unshift($surveyFlow['Flow'], $baseline_authenticator);
            }
            if ($baseline_embedded_flow) {
                // add baseline embeded data
                array_unshift($surveyFlow['Flow'], $baseline_embedded_flow);
            }
            // at at the end of the list
            if (isset($baseline_webService_group) && $baseline_webService_group) {
                // add baseline group web service
                array_push($surveyFlow['Flow'], $baseline_webService_group);
            }
            if ($baseline_webService_end) {
                // add baseline webService for finishing the survey
                array_push($surveyFlow['Flow'], $baseline_webService_end);
            }

            /** EXECUTE THE FLOW **********************************************************************************************************************************/
            return $this->set_survey_flow($survey['qualtrics_survey_id'], $surveyFlow);
        } else {
            $this->return_info(false, "Something went wrong");
        }
    }

    /**
     * Synchronize followup survey to qualtrics via the API
     * @param array @survey
     * @param object @surveyFlow
     * @retval array
     */
    private function sync_followup_survey($survey, $surveyFlow)
    {
        if ($surveyFlow) {
            //flag is the authenticator created
            $followup_authenticator_exists = false;

            /** START SURVEY WEB SERVICE *******************************************************************************************************************************/

            $baseline_webService_start = $this->get_webService_start_flow($survey);

            /** END SURVEY WEB SERVICE *******************************************************************************************************************************/

            $baseline_webService_end = $this->get_webService_finish_flow($survey);

            $followup_authenticator = $this->get_authenticator($survey['participant_variable']);
            foreach ($surveyFlow['Flow'] as $key => $flow) {
                if ($flow['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_AUTHENTICATOR) {
                    //already exist; overwirite
                    $followup_authenticator['Flow'] = $surveyFlow['Flow'][$key]['Flow']; // keep what is inside the authenticator if it exists                                        
                    foreach ($followup_authenticator['Flow'] as $keyAuth => $flowAuth) {
                        //loop inside the authenticator to cgeck for elements
                        if ($flowAuth['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_START) {
                            //already exist; overwirite
                            $followup_authenticator['Flow'][$keyAuth] = $baseline_webService_start;
                            $baseline_webService_start = false; //not needed anymore later when we check is it assign
                        } else if ($flowAuth['FlowID'] === ModuleQualtricsProjectModel::FLOW_ID_WEB_SERVICE_END) {
                            //already exist; overwirite
                            $followup_authenticator['Flow'][$keyAuth] = $baseline_webService_end;
                            $baseline_webService_end = false; //not needed anymore later when we check is it assign
                        }
                    }
                    $surveyFlow['Flow'][$key] = $followup_authenticator; //assign the new authenticator                    
                    $followup_authenticator_exists = true; //not needed anymore later when we check, is it assign
                }
            }
            //check do we still have to add flows
            // order is important as we add as first. We should add the element that should be first as last call
            if ($baseline_webService_start) {
                // add baseline webService for starting the survey
                array_unshift($surveyFlow['Flow'], $baseline_webService_start);
            }
            if ($baseline_webService_end) {
                // add baseline webService for finishing the survey
                array_push($surveyFlow['Flow'], $baseline_webService_end);
            }
            if (!$followup_authenticator_exists) {
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
            "api_mailing_group_id" => $data['api_mailing_group_id']
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
                "api_mailing_group_id" => $data['api_mailing_group_id']
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
     * Get all the actions for the project
     * @param int $pid
     * project id
     * @retval array $actions
     */
    public function get_actions($pid)
    {
        $sql = "SELECT *
                FROM view_qualtricsActions
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
        $res1 = $this->sync_survey_header($survey['qualtrics_survey_id']);
        $surveyFlow = $this->get_survey_flow($survey['qualtrics_survey_id']);
        if ($survey['survey_type_code'] === qualtricsSurveyTypes_baseline) {
            $res2 = $this->sync_baseline_survey($survey, $surveyFlow);
        } else if ($survey['survey_type_code'] === qualtricsSurveyTypes_follow_up) {
            $res2 = $this->sync_followup_survey($survey, $surveyFlow);
        }
        return $this->multi_return_info(array($res1, $res2));
    }

    public function get_project()
    {
        return $this->project;
    }
}
