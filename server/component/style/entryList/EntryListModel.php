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
class EntryListModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the selected form.
     */
    private $form_id;

    /**
     * The type of the selected form, internal or external
     */
    private $form_type;

    /**
     * Array with the entry list data
     */
    private $entry_list;

    /**
     * String with filter the data source; Use SQL syntax
     */
    private $filter;

    /**
     * If selected only the entries of the user will be loaded
     */
    private $own_entries_only;

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
        $this->init_properties();
        if ($this->entry_list) {
            foreach ($this->entry_list as $key => $value) {
                $this->entry_list[$key] = array_merge($value, $entry_record);
            }
        }
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch form data by id
     * @return array
     * the result of the fetched form
     */
    private function fetch_entry_list()
    {
        if ($this->form_type == FORM_INTERNAL) {
            $this->filter = ' AND deleted = 0 ' . $this->filter; // do not show the deleted records
        }
        $entry_data = $this->user_input->get_data($this->form_id, $this->filter, $this->own_entries_only, $this->form_type);
        $i = 0;
        foreach ($entry_data as $key => $value) {
            // add index to the data
            $entry_data[$key]['_index'] = $i;
            $i++;
        }
        return $entry_data;
    }

    /**
     * Initializes properties for the current component.
     *
     * This method retrieves necessary data from the database fields, initializes class properties,
     * and prepares the entry list for the component based on the provided parameters.
     * If a form ID is available, it fetches the entry list for the specified form.
     * Additionally, if a scope prefix is specified in the database field, it adds the prefix to each entry list key.
     * Debug data containing the current entry list is also stored for debugging purposes.
     *
     * @return void
     */
    private function init_properties()
    {
        $formInfo = explode('-', $this->get_db_field("formName"));
        $this->form_id = $formInfo[0];
        if (isset($formInfo[1])) {
            $this->form_type = $formInfo[1];
        }
        $this->own_entries_only = $this->get_db_field("own_entries_only", "1");
        $this->filter = $this->get_db_field("filter", "");
        if ($this->form_id) {
            $this->entry_list = $this->fetch_entry_list();
            if ($this->entry_list) {
                // add scope prefix
                $scope = $this->get_db_field("scope", "");
                if ($scope !== '') {
                    foreach ($this->entry_list as $key_list => $list_value) {
                        $scoped_array = array();
                        foreach ($this->entry_list[$key_list] as $key => $value) {
                            $scoped_array[$scope . '_' .  $key] = $value;
                        }
                        $this->entry_list[$key_list] = $scoped_array;
                    }
                }
            }
        }
        $this->debug_data['current_entry_list'] = $this->entry_list;
    }

    /* Public Methods *********************************************************/

    /**
     * Getter function, return the entry_list array property
     * @return array entry_list
     */
    public function get_entry_list()
    {
        return $this->entry_list;
    }

    /**
     * Loads children components for the current component.
     *
     * If the current page is a CMS page, it loads children using the parent class method.
     * Otherwise, it initializes properties and prepares the entry list for the component.
     * It then fetches children components from the database based on the section ID.
     * For each entry in the entry list, it merges the entry record with the provided entry record and adds
     * the children components to the list of children.
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
            $this->init_properties();
            $entry_list = $this->get_entry_list();
            $db_children = $this->db->fetch_section_children($this->section_id);
            if (!$entry_list) {
                return;
            }
            foreach ($entry_list as $key => $list_record) {
                // add parent entry records if they exist with prefix p_                
                $entry_record = array_merge($entry_record, $list_record); // merge with already existing parent entry
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
