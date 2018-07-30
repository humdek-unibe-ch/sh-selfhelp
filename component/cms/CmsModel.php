<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cms component such
 * that the data can easily be displayed in the view of the component.
 */
class CmsModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $id_page;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id_page
     *  The id of the page that is currently edited.
     */
    public function __construct($services, $id_page)
    {
        parent::__construct($services);
        $this->id_page = $id_page;
    }

    private function prepare_page_list_root($items)
    {
        $res = array();
        foreach($items as $item)
        {
            if($item['parent'] != "") continue;
            $id = intval($item["id"]);
            if(!$this->acl->has_access_select($_SESSION['id_user'], $id))
                continue;
            $res[$id] = array(
                "name" => $item['keyword'],
                "children" => array(),
                "url" => $this->router->generate("cms_show", array("id" => $id))
            );
        }
        return $res;
    }

    private function add_page_list_children($root_items, $items)
    {
        foreach($items as $item)
        {
            if($item['parent'] == "") continue;
            $id = intval($item["id"]);
            if(!$this->acl->has_access_select($_SESSION['id_user'], $id))
                continue;
            $root_items[intval($item['parent'])]['children'][$id] = array(
                "name" => $item['keyword'],
                "children" => array(),
                "url" => $this->router->generate("cms_show", array("id" => $id))
            );
        }
        return $root_items;
    }

    private function prepare_section_list($items)
    {
        $res = array("page" => array(), "nav" => array());
        foreach($items as $item)
        {
            $id = intval($item['id']);
            if(intval($item['id_styles']) == NAVIGATION_STYLE_ID)
                $res["nav"][$id] = array(
                    "name" => $item['name'],
                    "children" => array(),
                    "url" => "#"
                );
            else
            {
                $children_db = $this->db->fetch_section_children($id);
                $children = $this->prepare_section_list($children_db);
                $res["page"][$id] = array(
                    "name" => $item['name'],
                    "children" => $children["page"],
                    "url" => "#"
                );
            }
        }
        return $res;
    }

    public function get_pages()
    {
        $pages_db = $this->db->fetch_accessible_pages();
        $pages = $this->prepare_page_list_root($pages_db);
        return $this->add_page_list_children($pages, $pages_db);
    }

    public function get_global_sections()
    {
        $sql = "SELECT s.id, s.name, s.id_styles FROM sections AS s
            LEFT JOIN sections_hierarchy AS sh ON s.id = sh.child
            LEFT JOIN pages_sections AS ps ON s.id = ps.id_sections
            WHERE sh.child IS NULL AND ps.id_sections IS NULL";
        $sections_db = $this->db->query_db($sql);
        return $this->prepare_section_list($sections_db);
    }

    public function get_page_sections()
    {
        $sql = "SELECT keyword FROM pages WHERE id = :id";
        $keyword_db = $this->db->query_db_first($sql,
            array(":id" => $this->id_page));
        $sections_db = $this->db->fetch_page_sections($keyword_db['keyword']);
        return $this->prepare_section_list($sections_db);
    }

    public function get_active_page_id()
    {
        return $this->id_page;
    }
}
?>
