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
     * The constructor. Here all the main style components are created.
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
        $expand_pages = !$this->model->is_navigation_item();
        $this->add_list_component("page-list", "Page Index", $pages, "page",
            $expand_pages, $this->model->get_active_page_id());

        $global_sections = $this->model->get_global_sections();
        $this->add_list_component("global-section-list", "Global Sections",
            $global_sections, "global_sections");

        $this->page_sections = $this->model->get_page_sections();
        $this->add_list_component("page-section-list", "Page Sections",
            $this->page_sections, "sections-page", true,
            $this->model->get_active_section_id());

        $this->add_list_component("navigation-hierarchy-list",
            "Navigation Hierarchy", $this->model->get_navigation_hierarchy(),
            "navigation_sections", true,
            $this->model->get_active_root_section_id());

        $this->add_description_list_component("page-fields",
            "Page Fields", $this->prepare_page_fields());
        $this->add_description_list_component("section-fields",
            "Section Fields", $this->model->get_section_fields());
        $this->add_description_list_component("page-properties",
            "Page Properties", $this->prepare_page_properties(), true);

        if($this->model->get_active_root_section_id() == null)
        {
            foreach($this->page_sections as $section)
            {
                $this->add_local_component("section-" . $section['id'],
                    new StyleComponent($this->model->get_services(),
                        intval($section['id']),
                        $this->model->get_active_section_id()));
            }
        }
        else
        {
            $this->add_local_component("section",
                new StyleComponent($this->model->get_services(),
                    $this->model->get_active_root_section_id(),
                    $this->model->get_active_section_id()));
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Helper function to create a nested list style component, wrapped by a
     * card style component. The created component is added to the local
     * components list.
     *
     * @param string $name
     *  The name of the local component.
     * @param string $title
     *  The title to appear in the card header.
     * @param array $items
     *  The item list to be rendered as a nested list.
     * @param string $prefix
     *  A unique id prefix that is used to identify a list.
     * @param bool $is_expanded_root
     *  Indicates wheter the card style is expanded or not.
     * @param int $id_active
     *  The id of the currently active item.
     */
    private function add_list_component($name, $title, $items, $prefix,
        $is_expanded_root = false, $id_active = 0)
    {
        if(count($items) == 0) return;
        $nestedList = new BaseStyleComponent("nested_list");
        $nestedList->set_fields(array(
                "search_text" => "Search",
                "items" => $items,
                "id_prefix" => $prefix,
                "is_expanded" => false,
                "id_active" => $id_active,
                "root_name" => "root element"
            )
        );
        $this->add_local_component($name, new BaseStyleComponent("card"),
            array(
                "is_expanded" => $is_expanded_root,
                "is_collapsible" => true,
                "title" => $title,
                "content" => array($nestedList)
            )
        );
    }

    /**
     * Helper function to create a description list style component, wrapped by
     * a card style component. The created component is added to the local
     * components list.
     *
     * @param string $name
     *  The name of the local component.
     * @param string $title
     *  The title to appear in the card header.
     * @param array $fields
     *  The list of fields to be rendered as a description list.
     * @param bool $is_expanded
     *  Indicates wheter the card style is expanded or not.
     */
    private function add_description_list_component($name, $title, $fields,
        $is_expanded=false)
    {
        if(count($fields) == 0) return;
        $descriptionList = new BaseStyleComponent("description_list");
        $descriptionList->set_fields(array(
            "fields" => $fields
        ));
        $this->add_local_component($name, new BaseStyleComponent("card"),
            array(
                "is_expanded" => $is_expanded,
                "is_collapsible" => true,
                "title" => $title,
                "content" => array($descriptionList)
            )
        );
    }

    /**
     * Renders the control buttons.
     */
    private function output_controls()
    {
        if($this->model->get_active_page_id() == null) return;
        require __DIR__ . "/tpl_controls.php";
    }

    /**
     * Renders all the nested lists.
     */
    private function output_lists()
    {
        $this->output_local_component("page-list");
        $this->output_local_component("navigation-hierarchy-list");
        $this->output_local_component("global-section-list");
        $this->output_local_component("page-section-list");
    }

    /**
     * Renders the main content.
     */
    private function output_page_content()
    {
        if($this->model->is_navigation_main())
            echo "Here comes the description of how to handle a naviagtion page";
        else if($this->model->get_active_page_id() == null)
            echo "Here comes the description of how the cms works";
        else
        {
            $this->output_local_component("section");
            foreach($this->page_sections as $section)
            {
                $this->output_local_component("section-" . $section['id']);
            }
        }
    }

    /**
     * Renders the description list components.
     */
    private function output_fields()
    {
        $this->output_local_component("page-fields");
        $this->output_local_component("section-fields");
    }

    /**
     * Renders the page properties. Page properties only rendered of a page is
     * active and the active page is not a navigation item.
     */
    private function output_page_properties()
    {
        if($this->model->is_navigation_item()) return;
        if($this->model->get_active_page_id() == null) return;
        require __DIR__ . "/tpl_page_properties.php";
    }

    /**
     * Renders the page property items.
     */
    private function output_page_property_items()
    {
        $this->output_local_component("page-properties");
    }

    /**
     * Helper function to prepare the page fields such that they can be rendered
     * as a description list.
     *
     * @retval array
     *  An array of field arrays where each field has the following keys:
     *   'name':    the name of the field.
     *   'content': the content of the field.
     */
    private function prepare_page_fields()
    {
        if($this->model->is_navigation_item())
            return array();
        $fields = $this->model->get_page_fields();
        foreach($fields as $index => $field)
            if($field['name'] == "label") unset($fields[$index]);
        return $fields;
    }

    /**
     * Helper function to prepare the page properties such that they can be
     * rendered as a description list.
     *
     * @retval array
     *  An array of field arrays where each field has the following keys:
     *   'name':    the name of the field.
     *   'content': the content of the field.
     */
    private function prepare_page_properties()
    {
        $fields = array();
        foreach($this->page_info as $name => $content)
        {
            if($content == null) continue;
            if($content == "unknown") continue;
            if($name == "id") continue;
            if($name == "id_navigation_section") continue;
            $fields[] = array(
                "name" => $name,
                "content" => $content
            );
        }
        return $fields;
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
