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
class ModuleFormsActionsModel extends BaseModel
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
     * Insert a new action for form to the DB.
     *
     * @param array $data
     * name,
     * id_forms,
     * id_formProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     * @retval int
     *  The id of the new action or false if the process failed.
     */
    public function insert_new_action($data)
    {
        try {
            $this->db->begin_transaction();
            $actionId = $this->db->insert("formActions", array(
                "name" => $data['name'],
                "id_formProjectActionTriggerTypes" => $data['id_formProjectActionTriggerTypes'],
                "config" => $data['config'],
                "id_dataTables" => $data['id_dataTables']
            ));
            $this->db->commit();
            return $actionId;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Update form action.
     *
     * @param array $data
     * name,
     * id_forsm,
     * id_formProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     * @retval int
     *  The id of the new action or false if the process failed.
     */
    public function update_action($data)
    {
        try {
            $this->db->begin_transaction();
            if (isset($data['schedule_info']) && isset($data['schedule_info']['config'])) {
                $data['schedule_info']['config'] = json_decode($data['schedule_info']['config'], true);
            }
            $this->db->update_by_ids("formActions", array(
                "name" => $data['name'],
                "id_formProjectActionTriggerTypes" => $data['id_formProjectActionTriggerTypes'],
                "config" => $data['config'],
                "id_dataTables" => $data['id_dataTables']
            ), array('id' => $data['id']));
            $this->db->commit();
            return $data['id'];
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Fetch all form actions from the database
     *
     * @retval array $action
     */
    public function get_formActions()
    {
        return $this->db->select_table('view_formActions');
    }

    private function getGroupId($group)
    {
        $sql = "SELECT id FROM `groups`
            WHERE name = :group";
        $res = $this->db->query_db_first($sql, array(':group' => $group));
        return  !isset($res['id']) ? false : $res['id'];
    }

    public function test_insert_action($data)
    {
        try {
            $this->db->begin_transaction();
            $config = json_decode($data['config'], true);
            $condition_action = $config['condition'];
            $id_conditions_action = $this->db->insert("dta_conditions", array(
                "jsonLogic" => json_encode($condition_action['jsonLogic']),
                "builder" => json_encode($condition_action['builder'])
            ));
            $id_dta_actions = $this->db->insert("dta_actions", array(
                "name" => $data['name'],
                "id_formProjectActionTriggerTypes" => $data['id_formProjectActionTriggerTypes'],
                "id_dataTables" => $data['id_dataTables'],
                "id_conditions" => $id_conditions_action
            ));
            // blocks
            foreach ($config['blocks'] as $block) {
                $condition_block = $block['condition'];
                $id_conditions_block = $this->db->insert("dta_conditions", array(
                    "jsonLogic" => json_encode($condition_block['jsonLogic']),
                    "builder" => json_encode($condition_block['builder'])
                ));
                $insert_block = array(
                    "id_conditions" => $id_conditions_block,
                    "block_name" => $block['block_name'],
                    "id_dta_actions" => $id_dta_actions,
                );
                if (isset($block['randomization_count'])) {
                    $insert_block['randomization_count'] = $block['randomization_count'];
                }
                $id_blocks = $this->db->insert("dta_blocks", $insert_block);
                // jobs
                foreach ($block['jobs'] as $job) {
                    $condition_job = $job['condition'];
                    $id_conditions_job = $this->db->insert("dta_conditions", array(
                        "jsonLogic" => json_encode($condition_job['jsonLogic']),
                        "builder" => json_encode($condition_job['builder'])
                    ));
                    $condition_job_on_execute = $job['on_job_execute']['condition'];
                    $id_conditions_job_on_execute = $this->db->insert("dta_conditions", array(
                        "jsonLogic" => json_encode($condition_job_on_execute['jsonLogic']),
                        "builder" => json_encode($condition_job_on_execute['builder'])
                    ));
                    // Insert schedule_time
                    $schedule_time = $job['schedule_time'];
                    $id_schedule_time = $this->db->insert("dta_schedule_time", $schedule_time);
                    $id_jobs = $this->db->insert("dta_jobs", array(
                        "id_blocks" => $id_blocks,
                        "id_conditions" => $id_conditions_job,
                        "id_conditions_on_execute" => $id_conditions_job_on_execute,
                        "id_schedule_time" => $id_schedule_time,
                        "job_name" => $job['job_name'],
                        "job_type" => $job['job_type'],
                    ));

                    // Insert job_add_remove_groups
                    foreach ($job['job_add_remove_groups'] as $group_name) {
                        // Fetch group_id from groups table
                        $id_groups = $this->getGroupId($group_name);

                        $this->db->insert("dta_jobs_groups", array(
                            "id_jobs" => $id_jobs,
                            "id_groups" => $id_groups
                        ));
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
