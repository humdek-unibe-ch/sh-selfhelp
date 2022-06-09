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
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);                
        if (!$this->get_condition_result()['result']) {
            $this->checkChildrenConditionFailed();
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Check for children in the conditionalContainer which are conditionFailed and if there are load them
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

    
}
?>
