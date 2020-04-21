<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";
/**
 * This class is used to prepare all data related to the navigation component
 * such that the data can easily be displayed in the view of the component.
 */
class NavigationModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the active item.
     */
    private $id_active;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     * @param int $id_active
     *  The section id of the navigation section to be rendered inside the
     *  navigation wrapper.
     * @param number $id_page
     *  The id of the parent page
     */
    public function __construct($services, $id, $id_active, $id_page)
    {
        parent::__construct($services, $id);
        $this->id_active = $id_active;
        if($this->id_active != null)
            $this->children[] = new StyleComponent($services, $id_active,
                array(), $id_page);
    }

    /**
     * Gets the current navigation item id.
     *
     * @retval int
     *  The current navigation item id.
     */
    public function get_current_id()
    {
        return intval($this->id_active);
    }
}
?>
