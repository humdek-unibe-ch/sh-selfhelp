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
     * If selected only the entries of the user will be loaded
     */
    private $own_entries_only;

    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $formInfo = explode('-', $this->get_db_field("formName"));
        $this->form_id = $formInfo[0];
        if (isset($formInfo[1])) {
            $this->form_type = $formInfo[1];
        }
        $this->own_entries_only = $this->get_db_field("own_entries_only", "1");
        if ($this->form_id) {
            $this->entry_list = $this->fetch_entry_list();
        }
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch form data by id
     * @retval array
     * the result of the fetched form
     */
    private function fetch_entry_list()
    {
        if ($this->form_type == FORM_STATIC) {
            return $this->get_static_data($this->form_id, '', $this->own_entries_only);
        } else if ($this->form_type == FORM_DYNAMIC) {
            return $this->get_dynamic_data($this->form_id, ' AND deleted = 0', $this->own_entries_only);
        }
    }


    /* Public Methods *********************************************************/

    /**
     * Getter function, return the entry_list array property
     * @retval array emtry_list
     */
    public function get_entry_list()
    {
        return $this->entry_list;
    }
}
