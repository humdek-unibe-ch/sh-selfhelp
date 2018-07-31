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
        $this->add_list_component("page-list", "Pages", $pages, "page",
            $this->model->get_active_id());

        $global_sections = $this->model->get_global_sections();
        $this->add_list_component("global-section-list", "Global Sections",
            $global_sections, "global_sections");

        $this->page_sections = $this->model->get_page_sections();
        $this->add_list_component("page-section-list", "Page Sections",
            $this->page_sections, "page_sections");

        $sections = $this->model->get_section_hierarchy();
        $this->add_list_component("section-hierarchy-list", "Section Hierarchy",
            $sections, "sections");

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
        $id_active = 0)
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

    private function output_section_hierarchy_list()
    {
        $this->output_local_component("section-hierarchy-list");
    }

    private function output_page_content()
    {
        $title = "Page Fields";
        $function_name = "output_page_fields";
        require __DIR__ . "/tpl_field_wrapper.php";
        $this->output_local_component("component");
        foreach($this->page_sections as $section)
        {
            $this->output_local_component("section-" . $section['id']);
        }
    }

    private function output_page_fields()
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
