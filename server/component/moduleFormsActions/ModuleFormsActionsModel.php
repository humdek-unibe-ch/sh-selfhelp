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
            if(isset($data['schedule_info']) && isset($data['schedule_info']['config'])){
                $data['schedule_info']['config'] = json_decode($data['schedule_info']['config'], true);
            }
            $actionId = $this->db->insert("formActions", array(
                "name" => $data['name'],
                "id_forms" => $data['id_forms'],
                "id_formProjectActionTriggerTypes" => $data['id_formProjectActionTriggerTypes'],
                "id_formActionScheduleTypes" => $data['id_formActionScheduleTypes'],
                "id_forms_reminder" => isset($data['id_forms_reminder']) && $data['id_forms_reminder'] != '' ? $data['id_forms_reminder'] : null,
                "id_formActions" => isset($data['id_formActions']) ? $data['id_formActions'] : null,
                "schedule_info" => isset($data['schedule_info']) ? json_encode($data['schedule_info'], true) : null
            ));
            if (isset($data['id_groups']) && is_array($data['id_groups'])) {
                //insert related groups to the action if some are set
                foreach ($data['id_groups'] as $group) {
                    $this->db->insert("formActions_groups", array(
                        "id_formActions" => $actionId,
                        "id_groups" => intval($group)
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
            if(isset($data['schedule_info']) && isset($data['schedule_info']['config'])){
                $data['schedule_info']['config'] = json_decode($data['schedule_info']['config'], true);
            }
            $this->db->update_by_ids("formActions", array(
                "name" => $data['name'],
                "id_forms" => $data['id_forms'],
                "id_formProjectActionTriggerTypes" => $data['id_formProjectActionTriggerTypes'],
                "id_formActionScheduleTypes" => $data['id_formActionScheduleTypes'],
                "id_forms_reminder" => isset($data['id_forms_reminder']) ? $data['id_forms_reminder'] : null,
                "id_formActions" => isset($data['id_formActions']) ? ($data['id_formActions'] == '' ? null : $data['id_formActions']) : null,
                "schedule_info" => isset($data['schedule_info']) ? json_encode($data['schedule_info'], true) : null
            ), array('id' => $data['id']));

            //delete all group relations
            $this->db->remove_by_fk("formActions_groups", "id_formActions", $data['id']);

            if (isset($data['id_groups']) && is_array($data['id_groups'])) {
                //insert related groups to the action if some are set
                foreach ($data['id_groups'] as $group) {
                    $this->db->insert("formActions_groups", array(
                        "id_formActions" => $data['id'],
                        "id_groups" => intval($group)
                    ));
                }
            }

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

}