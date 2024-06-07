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
}
