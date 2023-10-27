<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the formField style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class LoopModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
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

    /* Public Methods *********************************************************/

    public function loadChildren()
    {
        if ($this->is_cms_page()) {
            parent::loadChildren();
        } else {
            $db_children = $this->db->fetch_section_children($this->section_id);
            $loop = $this->get_db_field("loop", array());
            if (!$loop || count($loop) == 0) {
                return;
            }
            foreach ($loop as $key => $entry_record) {
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
?>
