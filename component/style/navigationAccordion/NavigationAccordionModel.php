<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";
/**
 * This class is used to prepare all data related to the navigation component
 * such that the data can easily be displayed in the view of the component.
 */
class NavigationAccordionModel extends StyleModel
{
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
     */
    public function __construct($services, $id, $id_active)
    {
        parent::__construct($services, $id);
        $this->id_active = $id_active;
        $this->children[] = new StyleComponent($services, $id_active);
    }

    /**
     * Gets the current navigation item id.
     *
     * @retval int
     *  The current navigation item id.
     */
    public function get_current_id()
    {
        return $this->id_active;
    }
}
?>
