<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class DataContainerModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The constructor
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
    }

    /* Private Methods *********************************************************/


    /* Public Methods *********************************************************/

    public function loadChildren()
    {
        if ($this->is_cms_page()) {
            parent::loadChildren();
        } else {
            // $entry_record = $this->get_entry_record();
            $interpolation_data = $this->get_interpolation_data();
            $entry_record = array();
            if (isset($interpolation_data['global_vars'])) {
                $entry_record = array_merge($entry_record, $interpolation_data['global_vars']);
            }
            if (isset($interpolation_data['global_values'])) {
                $entry_record = array_merge($entry_record, $interpolation_data['global_values']);
            }
            if (isset($interpolation_data['data_config_retrieved'])) {
                $scope = $this->get_db_field("scope", "");
                if ($scope) {
                    $scoped_vars = array();
                    foreach ($interpolation_data['data_config_retrieved'] as $key => $value) {
                        $scoped_vars[$scope. '_' . $key] = $value; // add the scope prefix
                    }
                    $entry_record = array_merge($entry_record, $scoped_vars);
                } else {
                    $entry_record = array_merge($entry_record, $interpolation_data['data_config_retrieved']);
                }
            }
            $db_children = $this->db->fetch_section_children($this->section_id);
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
