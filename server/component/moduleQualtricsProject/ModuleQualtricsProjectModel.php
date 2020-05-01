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
     * Insert a new qualtrics project to the DB.
     *
     * @param array $data
     *  name, description, api_mailing_group_id
     * @retval int
     *  The id of the new project or false if the process failed.
     */
    public function insert_new_project($data){
        return $this->db->insert("qualtricsProjects", array(
            "name" => $data['name'],
            "description" => $data['description'],
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
    public function update_project($data){
        return $this->db->update_by_ids(
            "qualtricsProjects",
            array(
                "name" => $data['name'],
                "description" => $data['description'],
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
    public function get_projects(){
        return $this->db->select_table('qualtricsProjects');
    }

    /**
     * get db
     */
    public function get_db(){
        return $this->db;
    }

    /**
     * Get all the stages for the project
     * @param int $pid
     * project id
     * @retval array $stages
     */
    public function get_stages($pid){
        $sql = "SELECT *
                FROM view_qualtricsStages
                WHERE project_id = :pid";
        return $this->db->query_db($sql, array(":pid" => $pid));
    }


}