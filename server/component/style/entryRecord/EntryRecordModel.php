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
     * The type of the selected form, internal or external
     */
    private $form_type;

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
        if ($this->form_type == FORM_INTERNAL) {
            $this->filter = " AND deleted = 0 AND record_id = " . $record_id . ' ' . $this->filter; // do not show the deleted records
        }
        $entry_data = $this->user_input->get_data($this->form_id, $this->filter, $this->own_entries_only, $this->form_type, null, true);
        return $entry_data;
    }

    private function init_properties()
    {
        $this->record_id = isset($this->params['record_id']) ? intval($this->params['record_id']) : -1;
        $formInfo = explode('-', $this->get_db_field("formName"));
        $this->form_id = $formInfo[0];
        if (isset($formInfo[1])) {
            $this->form_type = $formInfo[1];
        }
        $this->own_entries_only = $this->get_db_field("own_entries_only", "1");
        $this->filter = $this->get_db_field("filter", "");
        if ($this->form_id) {
            $this->entry_record = $this->fetch_entry_record($this->record_id);
        }
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

    public function loadChildren()
    {
        if ($this->is_cms_page()) {
            parent::loadChildren();
        } else {
            $this->init_properties();
            $entry_record = $this->get_entry_record();
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
