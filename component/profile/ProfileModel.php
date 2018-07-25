<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the profile component such
 * that the data can easily be displayed in the view of the component.
 */
class ProfileModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db)
    {
        parent::__construct($router, $db);
        $fields = $db->fetch_page_fields("profile");
        $this->set_db_fields($fields);
    }
}
?>
