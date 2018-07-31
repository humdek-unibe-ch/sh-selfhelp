<?php
require_once __DIR__ . "/../style/StyleModel.php";
/**
 * This class is used to prepare all data related to the session component such
 * that the data can easily be displayed in the view of the component.
 *
 * What the style model provides is sufficient for the session model as well.
 */
class SessionModel extends StyleModel
{
    private $id;
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this session.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->id = $id;
    }

    /**
     * Gets the current navigation item id.
     *
     * @retval int
     *  The current navigation item id.
     */
    public function get_current_id()
    {
        return $this->id;
    }
}
?>
