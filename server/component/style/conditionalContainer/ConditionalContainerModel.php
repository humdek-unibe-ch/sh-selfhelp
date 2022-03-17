<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the conditional container
 * component style such that the data can easily be displayed in the view of
 * the component.
 */
class ConditionalContainerModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The result of the computeted condition
     */    
    public $condition_result;  

    /**
     * The DB field data config
     */
    private $data_config;

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id, array(), -1, false);
        $this->data_config = $this->get_db_field("data_config");
        $condition = $this->get_db_field('condition');
        if ($this->data_config) {
            $condition = $this->retrieve_data_form_config($condition);
        }
        $this->condition_result = $this->compute_condition($condition);
        if ($this->condition_result['result']) {
            $this->loadChildren();
        } else {
            $this->checkChildrenConditionFailed();
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Retrieve data from database - base dont the JSON configuration
     */
    private function retrieve_data_form_config($condition)
    {        
        $fields = $this->retrieve_data($this->data_config);
        if ($fields) {
            foreach ($fields as $field_name => $field_value) {
                $new_value = $field_value;
                $condition_string = json_encode($condition);
                $condition_string = str_replace($field_name, $new_value, $condition_string);
                $condition = json_decode($condition_string, true);
            }
        }
        return $condition;
    }

    /**
     * Check for childeren in the condtionalContainer whcih are conditionFailed and if there are load them
     */
    private function checkChildrenConditionFailed()
    {
        $db_children = $this->db->fetch_section_children($this->section_id);        
        foreach ($db_children as $child) {
            if ($this->getStyleNameById($child['id_styles']) == 'conditionFailed') {
                $this->children[$child['name']] = new StyleComponent($this->services, intval($child['id']), array(), -1);
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Use the JsonLogic libarary to compute whether the json condition is true
     * or false.
     *
     * @param array $condition
     *  An array representing the json condition string.
     * @retval mixed
     *  The evaluated condition.
     */
    public function compute_condition($condition, $id_users = null)
    {
        return $this->services->get_condition()->compute_condition($condition, $id_users, $this->get_db_field('id'));
    }
}
?>
