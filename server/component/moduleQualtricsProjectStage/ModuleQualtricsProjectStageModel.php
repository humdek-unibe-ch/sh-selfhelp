<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../moduleQualtricsProject/ModuleQualtricsProjectModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ModuleQualtricsProjectStageModel extends ModuleQualtricsProjectModel
{

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $pid)
    {
        parent::__construct($services, $pid);
    }

    /**
     * Insert a new stage for project adn session to the DB.
     *
     * @param int $pid
     * project id
     * @param array $data
     * id_qualtricsProjectStageTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectStageTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     * @retval int
     *  The id of the new stage or false if the process failed.
     */
    public function insert_new_stage($pid, $data)
    {
        try {
            $this->db->begin_transaction();
            $stageId = $this->db->insert("qualtricsStages", array(
                "id_qualtricsProjects" => $pid,
                "name" => $data['name'],
                "id_qualtricsProjectStageTypes" => $data['id_qualtricsProjectStageTypes'],
                "id_qualtricsSurveys" => $data['id_qualtricsSurveys'],
                "id_qualtricsProjectStageTriggerTypes" => $data['id_qualtricsProjectStageTriggerTypes'],
                "notification" => isset($data['notification']) ? json_encode($data['notification']) : null,
                "reminder" => isset($data['reminder']) ? json_encode($data['reminder']) : null
            ));
            if (isset($data['id_groups']) && is_array($data['id_groups'])) {
                //insert related groups to the stage if some are set
                foreach ($data['id_groups'] as $group) {
                    $this->db->insert("qualtricsStages_groups", array(
                        "id_qualtricsStages" => $stageId,
                        "id_groups" => intval($group)
                    ));
                }
            }
            if (isset($data['id_functions']) && is_array($data['id_functions'])) {
                //insert related functions to the stage if some are set
                foreach ($data['id_functions'] as $func) {
                    $this->db->insert("qualtricsStages_functions", array(
                        "id_qualtricsStages" => $stageId,
                        "id_lookups" => intval($func)
                    ));
                }
            }
            $this->db->commit();
            return $stageId;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Update qualtrics project.
     *
     * @param array $data
     *  id_qualtricsProjectStageTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectStageTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     * @retval int
     *  The id of the new stage or false if the process failed.
     */
    public function update_stage($pid, $data)
    {
        try {
            $this->db->begin_transaction();
            $this->db->update_by_ids("qualtricsStages", array(
                "id_qualtricsProjects" => $pid,
                "name" => $data['name'],
                "id_qualtricsProjectStageTypes" => $data['id_qualtricsProjectStageTypes'],
                "id_qualtricsSurveys" => $data['id_qualtricsSurveys'],
                "id_qualtricsProjectStageTriggerTypes" => $data['id_qualtricsProjectStageTriggerTypes'],
                "notification" => isset($data['notification']) ? json_encode($data['notification']) : null,
                "reminder" => isset($data['reminder']) ? json_encode($data['reminder']) : null
            ), array('id' => $data['id']));

            //delete all group relations
            $this->db->remove_by_fk("qualtricsStages_groups", "id_qualtricsStages", $data['id']);

            if (isset($data['id_groups']) && is_array($data['id_groups'])) {
                //insert related groups to the stage if some are set
                foreach ($data['id_groups'] as $group) {
                    $this->db->insert("qualtricsStages_groups", array(
                        "id_qualtricsStages" => $data['id'],
                        "id_groups" => intval($group)
                    ));
                }
            }

            //delete all functions relations
            $this->db->remove_by_fk("qualtricsStages_functions", "id_qualtricsStages", $data['id']);

            if (isset($data['id_functions']) && is_array($data['id_functions'])) {
                //insert related functions to the stage if some are set
                foreach ($data['id_functions'] as $func) {
                    $this->db->insert("qualtricsStages_functions", array(
                        "id_qualtricsStages" => $data['id'],
                        "id_lookups" => intval($func)
                    ));
                }
            }
            $this->db->commit();
            return $data['id'];
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }


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
}
