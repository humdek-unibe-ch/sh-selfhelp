<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";
require_once __DIR__ . "/../component/style/BaseStyleComponent.php";

/**
 * This class maps the section structure of the DB. A section page consists
 * solely of a collection of sections as defined in the database.
 */
class SectionPage extends BasePage
{
    /* Private Properties *****************************************************/

    /**
     * The list of sections to be rendered on the page.
     */
    private $sections;

    /**
     * The id of the selceted navigation section.
     */
    private $nav_section_id;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and collects all sections that are allocated to the current page.
     * For each section, a StyleComponent is created and added to the component
     * list of the page.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param string $keyword
     *  The identification name of the page.
     * @param array $params
     *  An array of get parameter taht will be passed to each style component.
     *  If the page is a navigation page it must hold the key 'nav' where the
     *  value defines the id of the current navigation section.
     */
    public function __construct($services, $keyword, $params=array())
    {
        parent::__construct($services, $keyword);
        if(!$this->acl_pass)
            return;
        $this->nav_section_id = isset($params['nav']) ? $params['nav'] : null;

        $db = $services->get_db();
        $this->sections = $db->fetch_page_sections($keyword);
        foreach($this->sections as $section)
            $this->add_component("section-" . $section['id'],
                new StyleComponent($this->services, intval($section['id']),
                    $params, $this->id_page));

        if($this->nav_section_id != null)
        {
            $nav = $this->services->get_nav();
            $nav->set_current_index($this->nav_section_id);
            $this->add_component("navigation", new StyleComponent(
                $this->services, $this->id_navigation_section, $params,
                $this->id_page));
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        $db = $this->services->get_db();
        $was_section_rendered = false;
        if ($this->pageAccessType != pageAccessTypes_mobile) {
            // if the page is only mobile do not load here
            require __DIR__ . "/tpl_fixed_box.php";
            foreach ($this->sections as $section) {
                $comp = $this->get_component("section-" . $section['id']);
                if ($comp->has_access()) {
                    $comp->output_content();
                    $was_section_rendered = true;
                }
            }
            if ($this->nav_section_id) {
                $sql = "SELECT * FROM sections_navigation
                WHERE child = :id AND id_pages = :pid";
                if ($db->query_db_first($sql, array(
                    ":id" => $this->nav_section_id, ":pid" => $this->id_page
                ))) {
                    $this->output_component("navigation");
                    $was_section_rendered = true;
                }
            }
        }
        if ((count($this->sections) > 0 || $this->nav_section_id)
            && !$was_section_rendered
        ) {
            $page = new InternalPage($this, "missing");
            $page->output_content();
        }
    }

    protected function output_content_mobile()
    {
        $res = [];
        $db = $this->services->get_db();
        $was_section_rendered = false;
        if ($this->pageAccessType != pageAccessTypes_web) {
            // if the page is only mobile do not load here
            foreach ($this->sections as $section) {
                $comp = $this->get_component("section-" . $section['id']);
                if ($comp->has_access()) {
                    $res[] = $comp->output_content_mobile();
                    $was_section_rendered = true;
                }
            }
            if ($this->nav_section_id) {
                $sql = "SELECT * FROM sections_navigation
                WHERE child = :id AND id_pages = :pid";
                if ($db->query_db_first($sql, array(
                    ":id" => $this->nav_section_id, ":pid" => $this->id_page
                ))) {
                    $res[] = $this->output_component_mobile("navigation");
                    $was_section_rendered = true;
                }
            }
        }

        if ((count($this->sections) > 0 || $this->nav_section_id)
            && !$was_section_rendered
        ) {
            $page = new InternalPage($this, "missing");
            // $res[] = $page->output_content_mobile();
            $res[] = 'missing';
        }
        return $res;
    }


    /* Private Methods ********************************************************/

    /**
     * Render a button to edit the page in CMS
     */
    private function output_cms_edit()
    {
        $router = $this->services->get_router();
        $acl = $this->services->get_acl();
        if($acl->has_access($_SESSION['id_user'],
                $this->id_page, 'insert'))
        {
            $arr = array('pid' => $this->id_page);
            if($this->id_page == $_SESSION['cms_edit_url']['pid'])
                $arr = $_SESSION['cms_edit_url'];
            if (isset($this->nav_section_id)) {
                $arr['sid'] = $this->nav_section_id;
            } else {
                $arr['sid'] = null;
            }
            $url = $router->generate('cmsSelect', $arr);
            require __DIR__ . "/tpl_cms_edit.php";
        }
    }

    /**
     * Render a button to jump back to the top of the page
     */
    private function output_back_to_top()
    {
        $url = "#top";
        require __DIR__ . "/tpl_to_top.php";
    }
}
?>
