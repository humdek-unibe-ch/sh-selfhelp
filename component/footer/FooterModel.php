<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the footer component such
 * that the data can easily be displayed in the view of the component.
 */
class FooterModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $acl;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $acl
     *  The instnce of the access control layer (ACL) which allows to decide
     *  which links to display.
     */
    public function __construct($router, $db, $acl)
    {
        parent::__construct($router, $db);
        $this->acl = $acl;
    }

    /* Public Methods *********************************************************/

    /**
     * Fetches all page links that are placed in the footer from the database.
     * Note that only page links are returned with matching access rights.
     *
     * @retval array
     *  An associative array of the from (<keyword> => <page_title>) where the
     *  keyword corresponds to the route identifier.
     */
    public function get_pages()
    {
        $locale_cond = $this->db->get_locale_condition();
        $sql = "SELECT p.id, p.keyword, pft.content AS title FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            WHERE p.footer_position > 0 AND $locale_cond AND f.name = 'label'
            ORDER BY p.footer_position";
        $pages_db = $this->db->query_db($sql, array());
        $pages = array();
        foreach($pages_db as $item)
        {
            if($this->acl->has_access_select($_SESSION['id_user'], $item['id']))
            $pages[$item['keyword']] = $item['title'];
        }
        return $pages;
    }
}
?>
