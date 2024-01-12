<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the footer component such
 * that the data can easily be displayed in the view of the component.
 */
class FooterModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /* Public Methods *********************************************************/ 

    /**
     * Get a list of languages and prepares the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':      The id of the language.
     *   'locale':   
     *   'language':   
     *   'csv_separator':
     */
    public function get_languages()
    {
        $res = array();
        foreach ($this->db->fetch_languages() as $language) {
            $res[] = array(
                "locale" => $language["locale"],
                "title" => $language["language"]                
            );
        }
        return $res;
    }

    /**
     * Fetches all page links that are placed in the footer from the database.
     * Note that only page links are returned with matching access rights.
     *
     * @retval array
     *  An associative array of the from (keyword => page_title) where the
     *  keyword corresponds to the route identifier.
     */
    public function get_pages()
    {
        $locale_cond = $this->db->get_locale_condition();
        $sql = "SELECT p.id, p.keyword, pft.content AS title FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            WHERE p.footer_position > 0 AND $locale_cond AND f.`name` = 'label'
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
