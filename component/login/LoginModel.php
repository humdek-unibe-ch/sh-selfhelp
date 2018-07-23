<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the login component such
 * that the data can easily be displayed in the view of the component.
 */
class LoginModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db)
    {
        parent::__construct($router, $db);
        $locale_cond = $db->get_locale_condition();
        $sql = "SELECT f.name, pft.content
            FROM pages_fields_translation AS pft
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN pages AS p ON p.id = pft.id_pages
            WHERE p.keyword = 'login' AND $locale_cond";
        $fields = $db->query_db($sql, array());
        $this->set_db_fields($fields);
    }
}
?>
