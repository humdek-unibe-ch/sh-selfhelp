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
     * The id of the selected survey.
     */
    private $form_id;

    /**
     * Array with the entry list data
     */
    private $entry_list;

    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->form_id = $this->get_db_field("formName");
        $this->entry_list = $this->fetch_entry_list($this->form_id);
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch form data by id
     * @param int $form_id
     * the form id of the form that we want to fetcht
     * @retval array
     * the result of the fetched form
     */
    private function fetch_entry_list($form_id)
    {
        $sql = 'CALL get_form_data_with_filter(:form_id, " AND deleted = 0")';
        return $this->db->query_db($sql, array(
            ":form_id" => $form_id
        ));
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
