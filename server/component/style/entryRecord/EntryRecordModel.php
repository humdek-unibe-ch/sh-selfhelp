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
class EntryRecordModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the selected form.
     */
    private $form_id;

    /**
     * String with filter the data source; Use SQL syntax
     */
    private $filter;

    /**
     * If selected only the entries of the user will be loaded
     */
    private $own_entries_only;

    /**
     * The selected record id
     */
    private $record_id = -1;

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
        if ($this->entry_record) {
            $this->entry_record = array_merge($this->entry_record, $entry_record);
        }
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch form data by record id
     * @param int record_id
     * Record id
     * @return array
     * the result of the fetched form
     */
    private function fetch_entry_record($record_id)
    {
        $this->filter = " AND record_id = " . $record_id . ' ' . $this->filter; // do not show the deleted records
        $entry_data = $this->user_input->get_data($this->form_id, $this->filter, $this->own_entries_only, null, true);
        return $entry_data;
    }

    /**
     * Initializes properties for the current component.
     *
     * This method retrieves necessary data from the database fields, initializes class properties,
     * and prepares the entry record for the component based on the provided parameters.
     * If a form ID is available, it fetches the entry record for the specified record ID.
     * Additionally, if a scope prefix is specified in the database field, it adds the prefix to each entry record key.
     *
     * @return void
     */
    private function init_properties()
    {
        $url_param = $this->get_db_field("url_param", "record_id");
        $this->record_id = isset($this->params[$url_param]) ? intval($this->params[$url_param]) : -1;
        $this->form_id = $this->get_db_field("formName");
        $this->own_entries_only = $this->get_db_field("own_entries_only", "1");
        $this->filter = $this->get_db_field("filter", "");
        if ($this->form_id) {
            $this->entry_record = $this->fetch_entry_record($this->record_id);
            if ($this->entry_record) {
                // add scope prefix
                $scope = $this->get_db_field("scope", "");
                if ($scope !== '') {
                    $scoped_array = array();
                    foreach ($this->entry_record as $key => $value) {
                        $scoped_array[$scope . '_' .  $key] = $value;
                    }
                    $this->entry_record = $scoped_array;
                }
            }
        }
        $this->debug_data['current_entry_record'] = $this->entry_record;
    }

    /* Public Methods *********************************************************/

    /**
     * Getter function, return the entry_record array property
     * @return array entry_list
     */
    public function get_entry_record()
    {
        return $this->entry_record;
    }

    /**
     * Loads children components for the current component.
     *
     * If the current page is a CMS page, it loads children using the parent class method.
     * Otherwise, it initializes properties, merges the provided entry record with the existing parent entry,
     * and fetches children components from the database based on the section ID.
     * Each child component is instantiated as a StyleComponent object and added to the list of children.
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
            if ($this->get_entry_record()) {
                $entry_record = array_merge($entry_record, $this->get_entry_record()); // merge with already existing parent entry
            } else {
                return;
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
