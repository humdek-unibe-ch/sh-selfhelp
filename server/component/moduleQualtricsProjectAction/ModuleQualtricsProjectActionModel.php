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
class ModuleQualtricsProjectActionModel extends ModuleQualtricsProjectModel
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
     * Insert a new action for project adn session to the DB.
     *
     * @param int $pid
     * project id
     * @param array $data
     * id_qualtricsProjectActionTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     * @retval int
     *  The id of the new action or false if the process failed.
     */
    public function insert_new_action($pid, $data)
    {
        try {
            $this->db->begin_transaction();
            $actionId = $this->db->insert("qualtricsActions", array(
                "id_qualtricsProjects" => $pid,
                "name" => $data['name'],
                "id_qualtricsSurveys" => $data['id_qualtricsSurveys'],
                "id_qualtricsProjectActionTriggerTypes" => $data['id_qualtricsProjectActionTriggerTypes'],
                "id_qualtricsActionScheduleTypes" => $data['id_qualtricsActionScheduleTypes'],
                "id_qualtricsSurveys_reminder" => isset($data['id_qualtricsSurveys_reminder']) ? $data['id_qualtricsSurveys_reminder'] : null,
                "id_qualtricsActions" => isset($data['id_qualtricsActions']) ? $data['id_qualtricsActions'] : null,
                "schedule_info" => isset($data['schedule_info']) ? json_encode($data['schedule_info']) : null
            ));
            if (isset($data['id_groups']) && is_array($data['id_groups'])) {
                //insert related groups to the action if some are set
                foreach ($data['id_groups'] as $group) {
                    $this->db->insert("qualtricsActions_groups", array(
                        "id_qualtricsActions" => $actionId,
                        "id_groups" => intval($group)
                    ));
                }
            }
            if (isset($data['id_functions']) && is_array($data['id_functions'])) {
                //insert related functions to the action if some are set
                foreach ($data['id_functions'] as $func) {
                    $this->db->insert("qualtricsActions_functions", array(
                        "id_qualtricsActions" => $actionId,
                        "id_lookups" => intval($func)
                    ));
                }
            }
            $this->db->commit();
            return $actionId;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Update qualtrics project.
     *
     * @param array $data
     *  id_qualtricsProjectActionTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     * @retval int
     *  The id of the new action or false if the process failed.
     */
    public function update_action($pid, $data)
    {
        try {
            $this->db->begin_transaction();
            $this->db->update_by_ids("qualtricsActions", array(
                "id_qualtricsProjects" => $pid,
                "name" => $data['name'],
                "id_qualtricsSurveys" => $data['id_qualtricsSurveys'],
                "id_qualtricsProjectActionTriggerTypes" => $data['id_qualtricsProjectActionTriggerTypes'],
                "id_qualtricsActionScheduleTypes" => $data['id_qualtricsActionScheduleTypes'],
                "id_qualtricsSurveys_reminder" => isset($data['id_qualtricsSurveys_reminder']) ? $data['id_qualtricsSurveys_reminder'] : null,
                "id_qualtricsActions" => isset($data['id_qualtricsActions']) ? ($data['id_qualtricsActions'] == '' ? null : $data['id_qualtricsActions']) : null,
                "schedule_info" => isset($data['schedule_info']) ? json_encode($data['schedule_info']) : null
            ), array('id' => $data['id']));

            //delete all group relations
            $this->db->remove_by_fk("qualtricsActions_groups", "id_qualtricsActions", $data['id']);

            if (isset($data['id_groups']) && is_array($data['id_groups'])) {
                //insert related groups to the action if some are set
                foreach ($data['id_groups'] as $group) {
                    $this->db->insert("qualtricsActions_groups", array(
                        "id_qualtricsActions" => $data['id'],
                        "id_groups" => intval($group)
                    ));
                }
            }

            //delete all functions relations
            $this->db->remove_by_fk("qualtricsActions_functions", "id_qualtricsActions", $data['id']);

            if (isset($data['id_functions']) && is_array($data['id_functions'])) {
                //insert related functions to the action if some are set
                foreach ($data['id_functions'] as $func) {
                    $this->db->insert("qualtricsActions_functions", array(
                        "id_qualtricsActions" => $data['id'],
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

    /**
     * get surveys from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_surveys()
    {
        $surveys = array();
        foreach ($this->db->select_table("qualtricsSurveys") as $survey) {
            array_push($surveys, array("value" => $survey['id'], "text" => $survey['name']));
        }
        return $surveys;
    }

    /**
     * get notifications from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_notifications()
    {
        $notifications = array();
        $sql = "SELECT id, name
                FROM qualtricsActions
                WHERE id_qualtricsActionScheduleTypes = :id_qualtricsActionScheduleTypes";
        $fetch_notifivations = $this->db->query_db($sql, array(
            ":id_qualtricsActionScheduleTypes" => $this->db->get_lookup_id_by_value(qualtricsActionScheduleTypes, qualtricsActionScheduleTypes_notification)
        ));
        foreach ($fetch_notifivations as $notification) {
            array_push($notifications, array("value" => $notification['id'], "text" => $notification['name']));
        }
        return $notifications;
    }
}
