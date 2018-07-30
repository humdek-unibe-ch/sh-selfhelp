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

    private function add_item($id, $title, $children, $url)
    {
        return array(
            "id" => $id,
            "title" => $title,
            "children" => $children,
            "url" => $url
        );
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
            $res[] = $this->add_item($id, $item['keyword'], array(),
                $this->router->generate("cms_show", array("id" => $id))
            );
            if($item['url'] == "")
                $res[count($res)-1]['root-link'] = false;
        }
        return $res;
    }

    private function get_item_index($id, $items)
    {
        for($idx = 0; $idx < count($items); $idx++)
            if($items[$idx]['id'] == $id)
                return $idx;
        return -1;
    }

    private function add_page_list_children($root_items, $items)
    {
        foreach($items as $item)
        {
            if($item['parent'] == "") continue;
            $id = intval($item["id"]);
            if(!$this->acl->has_access_select($_SESSION['id_user'], $id))
                continue;
            $root_idx = $this->get_item_index(intval($item['parent']),
                $root_items);
            $root_items[$root_idx]['children'][] = $this->add_item($id,
                $item['keyword'], array(),
                $this->router->generate("cms_show", array("id" => $id))
            );
        }
        return $root_items;
    }

    private function add_page_list_children_root($root_items)
    {
        foreach($root_items as $index => $item)
        {
            $id = $item['id'];
            if(($item['root-url'] != "") && (count($item['children']) > 0))
            {
                array_unshift($root_items[$index]['children'], array(
                    "id" => $id,
                    "title" => "root (" . $item['title'] . ")",
                    "children" => array(),
                    "url" => $item['url']
                ));
                $root_items[$index]['url'] = "";
                $root_items[$index]['id'] = $id . "-root";
            }
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
                $res["nav"][] = array(
                    "id" => $id,
                    "title" => $item['name'],
                    "children" => array(),
                    "url" => "#"
                );
            else
            {
                $children_db = $this->db->fetch_section_children($id);
                $children = $this->prepare_section_list($children_db);
                $res["page"][] = array(
                    "id" => $id,
                    "title" => $item['name'],
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
