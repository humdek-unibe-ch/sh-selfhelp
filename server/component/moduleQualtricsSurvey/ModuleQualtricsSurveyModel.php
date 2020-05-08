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
class ModuleQualtricsSurveyModel extends BaseModel
{

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /**
     * Insert a new qualtrics survey to the DB.
     *
     * @param array $data
     *  name, description, qualtrics_survey_id, group_variable
     * @retval int
     *  The id of the new survey or false if the process failed.
     */
    public function insert_new_survey($data){
        return $this->db->insert("qualtricsSurveys", array(
            "name" => $data['name'],
            "description" => $data['description'],
            "qualtrics_survey_id" => $data['qualtrics_survey_id'],
            "group_variable" => isset($data['group_variable']) ? 1 : 0
        ));
    }

    /**
     * Update qualtrics survey.
     *
     * @param array $data
     *  id, name, description, qualtrics_survey_id, group_variable
     * @retval int
     *  The number of the updated rows
     */
    public function update_survey($data){
        return $this->db->update_by_ids(
            "qualtricsSurveys",
            array(
                "name" => $data['name'],
                "description" => $data['description'],
                "qualtrics_survey_id" => $data['qualtrics_survey_id'],
                "group_variable" => isset($data['group_variable']) ? 1 : 0
            ),
            array('id' => $data['id'])
        );
    }

    /**
     * Fetch all qualtrics surveys from the database
     *
     * @retval array $survey
     * id
     * name
     * description
     * api_mailing_group_id
     */
    public function get_surveys(){
        return $this->db->select_table('qualtricsSurveys');
    }

    /**
     * get db
     */
    public function get_db(){
        return $this->db;
    }


}