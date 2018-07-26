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
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The section id of this session.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }
}
?>
