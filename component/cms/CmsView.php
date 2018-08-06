<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The view class of the cms component.
 */
class CmsView extends BaseView
{
    private $page_info;
    private $page_sections;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model);
        $this->page_info = $this->model->get_page_info();

        $pages = $this->model->get_pages();
        $this->add_list_component("page-list", "Pages", $pages, "page", true,
            $this->model->get_active_page_id());

        $global_sections = $this->model->get_global_sections();
        $this->add_list_component("global-section-list", "Global Sections",
            $global_sections, "global_sections");

        $this->page_sections = $this->model->get_page_sections();
        $this->add_list_component("page-section-list", "Page Sections",
            $this->page_sections, "page_sections", true);

        $this->add_list_component("navigation-hierarchy-list",
            "Navigation Hierarchy", $this->model->get_navigation_hierarchy(),
            "navigation_sections", true, $this->model->get_active_section_id());

        if($this->page_info['action'] == "component")
        {
            $this->add_local_component("component",
                $this->model->get_component());
        }
        else if($this->page_info['action'] == "sections"
            || $this->page_info['action'] == "custom")
        {
            foreach($this->page_sections as $section)
            {
                $this->add_local_component("section-" . $section['id'],
                    new StyleComponent($this->model->get_services(),
                    $section['id']));
            }
        }
    }

    /* Private Methods ********************************************************/

    private function add_list_component($name, $title, $items, $prefix,
        $is_expanded_root = false, $id_active = 0)
    {
        if(count($items) == 0) return;
        $this->add_local_component($name,
            new BaseStyleComponent("nested_list"),
            array(
                "title" => $title,
                "search_text" => "Search",
                "items" => $items,
                "id_prefix" => $prefix,
                "is_expanded" => false,
                "is_expanded_root" => $is_expanded_root,
                "id_active" => $id_active,
                "root_name" => "root element"
            )
        );
    }

    private function output_page_list()
    {
        $this->output_local_component("page-list");
    }

    private function output_global_section_list()
    {
        $this->output_local_component("global-section-list");
    }

    private function output_page_section_list()
    {
        $this->output_local_component("page-section-list");
    }

    private function output_navigation_hierarchy_list()
    {
        $this->output_local_component("navigation-hierarchy-list");
    }

    private function output_page_content()
    {
        $this->output_local_component("component");
        foreach($this->page_sections as $section)
        {
            $this->output_local_component("section-" . $section['id']);
        }
    }

    private function output_page_properties()
    {
        $title = "Page Properties";
        $function_name = "output_page_property_items";
        require __DIR__ . "/tpl_field_wrapper.php";
    }

    private function output_page_property_items()
    {
        foreach($this->page_info as $name => $content)
        {
            if($content == null) continue;
            if($name == "id") continue;
            if($name == "id_navigation_section") continue;
            require __DIR__. "/tpl_page_info.php";
        }
    }

    private function output_page_fields()
    {
        $title = "Page Fields";
        $function_name = "output_page_field_items";
        require __DIR__ . "/tpl_field_wrapper.php";
    }

    private function output_page_field_items()
    {
        $fields = $this->model->get_page_fields();
        foreach($fields as $field)
        {
            $name = $field['name'];
            $content = $field['content'];
            require __DIR__. "/tpl_page_field.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/cms.css");
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/cms.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_cms.php";
    }
}
?>
