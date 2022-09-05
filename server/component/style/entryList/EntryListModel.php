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
     * The type of the selected form, dynamic or static
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

    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->init_properties();
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch form data by id
     * @return array
     * the result of the fetched form
     */
    private function fetch_entry_list()
    {
        if ($this->form_type == FORM_DYNAMIC) {
            $this->filter = ' AND deleted = 0 ' . $this->filter; // do not show the deleted records
        }
        return $this->user_input->get_data($this->form_id, $this->filter, $this->own_entries_only, $this->form_type);
    }

    private function init_properties(){
        $formInfo = explode('-', $this->get_db_field("formName"));
        $this->form_id = $formInfo[0];
        if (isset($formInfo[1])) {
            $this->form_type = $formInfo[1];
        }
        $this->own_entries_only = $this->get_db_field("own_entries_only", "1");
        $this->filter = $this->get_db_field("filter", "");
        if ($this->form_id) {
            $this->entry_list = $this->fetch_entry_list();
        }
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

    public function loadChildren()
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
            foreach ($entry_list as $key => $entry_record) {
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
