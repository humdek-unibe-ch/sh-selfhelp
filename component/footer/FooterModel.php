<?php
/**
 * This class is used to prepare all data related to the footer component such
 * that the data can easily be displayed in the view of the component.
 */
class FooterModel
{
    /* Private Properties *****************************************************/

    private $db;
    private $acl;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $acl
     *  The instnce of the access control layer (ACL) which allows to decide
     *  which links to display.
     */
    public function __construct($db, $acl)
    {
        $this->acl = $acl;
        $this->db = $db;
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
        $sql = "SELECT p.id, p.keyword, pt.title FROM pages_translation AS pt
            LEFT JOIN pages AS p ON p.id = pt.id_pages
            LEFT JOIN languages AS l ON l.id = pt.id_languages
            WHERE p.footer_position > 0 AND l.locale = :locale
            ORDER BY p.footer_position";
        $pages_db = $this->db->query_db($sql,
            array(':locale' => $_SESSION['language']));
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
