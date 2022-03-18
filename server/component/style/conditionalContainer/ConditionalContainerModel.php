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
    private $condition_result;  

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
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, array(), -1, false, $entry_record);
        $this->data_config = $this->get_db_field("data_config");
        $condition = $this->get_db_field('condition');
        if ($this->data_config) {
            $condition = $this->retrieve_data_form_config($condition);
        }
        if($this->get_entry_record()){
            $condition = $this->get_entry_values($condition);
        }
        $this->condition_result = $this->services->get_condition()->compute_condition($condition, null, $this->get_db_field('id'));
        if ($this->is_correct_platform()) {
            // check conditions if it is for the correct platfform only, otherwise do not load children - improve perfromacne
            if ($this->condition_result['result']) {
                $this->loadChildren();
            } else {
                $this->checkChildrenConditionFailed();
            }
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Get entries values if there are any set
     * @param $condition
     * The condition value array
     * @retval array
     * Return the condition array
     */
    private function get_entry_values($condition){
        $condition = $this->get_entry_value($this->get_entry_record(), json_encode($condition));
        return json_decode($condition, true);
    }

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

    /**
     * Check if the call is from the platform that was selected for the style
     * @retval boolean
     * Returns true if the call is from the correct platform otherwise false
     */
    private function is_correct_platform(){
        $platform = $this->get_db_field('platform', pageAccessTypes_mobile_and_web); 
        if ($platform == pageAccessTypes_mobile_and_web) {
            return true;
        } else if ($platform == pageAccessTypes_mobile && (isset($_POST['mobile']) && $_POST['mobile'])) {
            return true;
        } else if ($platform == pageAccessTypes_web && !isset($_POST['mobile'])) {
            return true;
        }
        return false;
    }

    /* Public Methods *********************************************************/

    /**
     * Get the already computed condtion result
     *
     * @retval array
     *  The result array
     */
    public function get_condition_result()
    {
        return $this->condition_result;
    }
}
?>
