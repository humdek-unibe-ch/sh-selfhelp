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
     * The entry record data
     */
    private $entry_record;

    public function __construct($services, $id, $record_id)
    {
        parent::__construct($services, $id);
        $formInfo = explode('-', $this->get_db_field("formName"));
        $this->form_id = $formInfo[0];        
        $own_entries_only =  $this->get_db_field("own_entries_only", 1);
        $this->entry_record = $record_id > 0 ? $this->fetch_entry_record($this->form_id, $record_id, $own_entries_only) : null;
    }

    /* Private Methods *********************************************************/

    

    /* Public Methods *********************************************************/

    /**
     * Get the entry record;
     * @retval array;
     * The entry record;
     */
    public function get_entry_record(){
        return $this->entry_record;
    }
}
