<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the formField style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class LoopModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
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
    }

    /* Public Methods *********************************************************/

    /**
     * Loads children components for the current component.
     * 
     * If the current page is a CMS page, it loads children using the parent class method.
     * Otherwise, it fetches children components from the database based on the section ID and a looping structure defined in the style.
     * Each loop iteration represents a set of entry records to be used when creating child components.
     * The looping structure allows for flexible creation of child components based on different data sets.
     * 
     * If a 'scope' is specified in the style, it is used as a prefix for variable names within each loop iteration,
     * ensuring variable names are unique and avoiding conflicts.
     * 
     * @param array $entry_record An optional array containing additional entry record data.
     * 
     * @return void
     */
    public function loadChildren($entry_record = array())
    {
        if ($this->is_cms_page()) {
            parent::loadChildren();
        } else {
            $db_children = $this->db->fetch_section_children($this->section_id);
            $loop = $this->get_db_field("loop", array());
            if (!$loop || count($loop) == 0) {
                return;
            }
            foreach ($loop as $loop_key => $loop_record) {
                // add scope prefix
                $scope = $this->get_db_field("scope", "");
                if ($scope !== '') {
                    foreach ($loop_record as $key_loop_record => $loop_record_value) {
                        $scoped_array = array();
                        foreach ($loop_record as $key => $value) {
                            $scoped_array[$scope . '_' .  $key] = $value;
                        }
                        $loop_record = $scoped_array;
                    }
                }
                $entry_record = array_merge($loop_record, $entry_record); // merge with already existing parent entry

                foreach ($db_children as $child) {
                    $new_child = new StyleComponent(
                        $this->services,
                        intval($child['id']),
                        $this->get_params(),
                        $this->get_id_page(),
                        $entry_record
                    );
                    array_push($this->children, $new_child);
                }
            }
        }
    }
}
?>
