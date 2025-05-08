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
class EntryRecordDeleteModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The constructor
     *
     * @param object $services
     *  An associative array holding the different available services. See the
     *  class definition base page for a list of all services.
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

    /**
     * Get deleted record id from the entry
     * @return int | false
     * Return the record id or false
     */
    function get_delete_record_id()
    {
        return isset($this->entry_record['record_id']) ? $this->entry_record['record_id'] :  false;
    }

    /**
     * Mark this user input as deleted in the database.
     * @param int $record_id
     * The deleted record id
     */
    public function delete_record($record_id)
    {
        return $this->user_input->delete_data($record_id, $this->get_db_field("own_entries_only", 1));
;
    }

}
