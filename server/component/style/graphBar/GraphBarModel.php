<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphModel.php";

/**
 * This class is used to prepare all data related to the garphBar style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class GraphBarModel extends GraphModel
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

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Checks wether the label map provided through the CMS contains all
     * required fields.
     *
     * @retval boolean
     *  True on success, false on failure.
     */
    public function check_label_map($label_map) {
        if(!is_array($label_map) || count($label_map) === 0)
            return false;
        return true;
    }
}
?>
