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
            $this->children = []; // clear the children in case we are updating the data
            if ($this->user_input->is_there_user_input_change()) {
                // if there is a change in the user_input, recalculate
                $fields = $this->db->fetch_section_fields($this->section_id);
                $this->set_db_fields($fields);
            }
            $interpolation_data = $this->get_interpolation_data();
            $entry_record = array();
            if ($interpolation_data) {
                foreach ($interpolation_data as $key => $value) {
                    if ($key == 'data_config_retrieved') {
                        $scope = $this->get_db_field("scope", "");
                        if ($scope) {
                            $scoped_vars = array();
                            foreach ($interpolation_data['data_config_retrieved'] as $key => $value) {
                                $scoped_vars[$scope . '.' . $key] = $value; // add the scope prefix
                            }
                            $entry_record = array_merge($entry_record, $scoped_vars);
                        } else {
                            $entry_record = array_merge($entry_record, $interpolation_data['data_config_retrieved']);
                        }
                    } else {
                        $entry_record = array_merge($entry_record, $interpolation_data[$key]);
                    }
                }
            }
            $debug_data = $this->get_debug_data();
            $debug_data['interpolation_data'] = $entry_record;
            $this->set_debug_data($debug_data);
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
