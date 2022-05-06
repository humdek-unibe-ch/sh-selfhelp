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
     * Fetches all page links that are placed in the footer from the database.
     * Note that only page links are returned with matching access rights.
     *
     * @retval array
     *  An associative array of the from (keyword => page_title) where the
     *  keyword corresponds to the route identifier.
     */
    public function get_pages()
    {
        $pages_db = $this->db->fetch_pages(-1, $_SESSION['language'], 'AND footer_position > 0', 'ORDER BY footer_position');
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
