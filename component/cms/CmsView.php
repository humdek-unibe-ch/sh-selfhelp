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
        parent::__construct($model, $controller);
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

        if($this->model->get_active_page_id())
            $this->add_page_property_list();
        if($this->model->get_active_section_id())
            $this->add_section_field_list();

        $success_count = $controller->get_update_success_count();
        if($success_count > 0)
        {
            $msg = "Successfully updated " . $success_count . " fields";
            $this->add_alert_component("alert_success", "success", $msg);
        }
        $fail_count = $controller->get_update_fail_count();
        if($fail_count > 0)
        {
            $msg = "Failed to update " . $fail_count . " fields";
            $this->add_alert_component("alert_failed", "danger", $msg);
        }

        $page_components = array();
        if($this->model->get_active_root_section_id() == null)
            foreach($this->page_sections as $section)
                $page_components[] = new StyleComponent(
                    $this->model->get_services(),
                    intval($section['id']),
                    $this->model->get_active_section_id());
        else
            $page_components[] = new StyleComponent(
                $this->model->get_services(),
                $this->model->get_active_root_section_id(),
                $this->model->get_active_section_id());
        if(count($page_components) == 0)
        {
            $text = new BaseStyleComponent("plaintext", array(
                "text" => "No CMS view available for this page."
            ));
            $page_components[] = $text;
        }
        $this->add_local_component("page-view",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Page View",
                "children" => $page_components
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Helper function to create an alert component and add it to the local
     * component list.
     *
     * @param string $name
     *  The name of the local component.
     * @param string $type
     *  The type of the alert, e.g 'success', 'danger', etc.
     * @param string $msg
     *  The message to be displayed in the alert.
     */
    private function add_alert_component($name, $type, $msg)
    {
        $this->add_local_component($name, new BaseStyleComponent( "alert",
            array(
                "type" => $type,
                "children" => array(
                    new BaseStyleComponent("plaintext", array("text" => $msg))
                ),
                "dismiss" => true
            ),
            true
        ));
    }

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
        $content = new BaseStyleComponent("nestedList", array(
            "search_text" => "Search",
            "items" => $items,
            "id_prefix" => $prefix,
            "is_expanded" => false,
            "id_active" => $id_active,
            "root_name" => "root element"
        ));
        $this->add_local_component($name, new BaseStyleComponent("card",
            array(
                "is_expanded" => $is_expanded_root,
                "is_collapsible" => true,
                "title" => $title,
                "children" => array($content)
            )
        ));
    }

    /**
     * Add the page property list to the local component list. The page property
     * list is wrapped by a collapsible card component.
     */
    private function add_page_property_list()
    {
        $type = ($this->model->get_mode() == "update") ? "warning" : "light";
        $this->add_local_component("page-properties",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Page Properties",
                "children" => array(new BaseStyleComponent("descriptionList",
                     array(
                        "mode" => $this->model->get_mode(),
                        "fields" => $this->model->get_page_properties()
                    ))),
                "type" => $type
            )
        ));
    }

    /**
     * Add the section field list to the local component list. The section field
     * list is wrapped by a card component.
     */
    private function add_section_field_list()
    {
        $section_info = $this->model->get_section_info();
        $properties = new BaseStyleComponent("template", array(
            "path" => __DIR__ . "/tpl_section_properties.php",
            "fields" => array(
                "section_name_title" => "Section Name:",
                "section_name" => $section_info['name'],
                "section_style_title" => "Section Style:",
                "section_style" => $section_info['style']
            ),
        ));
        $type = ($this->model->get_mode() == "update") ? "warning" : "light";
        $fields = $this->model->get_section_fields();
        if(count($fields) == 0)
        {
            $content = new BaseStyleComponent("plaintext", array(
                "text" => "No section fields defined."
            ));
        }
        else
        {
            $content = new BaseStyleComponent("descriptionList", array(
                "mode" => $this->model->get_mode(),
                "fields" => $fields
            ));
        }
        $this->add_local_component("section-fields",
            new BaseStyleComponent("card", array(
                "is_collapsible" => false,
                "title" => "Section Fields",
                "children" => array($properties, $content),
                "type" => $type
            )
        ));
    }

    /**
     * Renders alerts.
     */
    private function output_alerts()
    {
        $this->output_local_component("alert_failed");
        $this->output_local_component("alert_success");
    }

    /**
     * Renders the control buttons.
     */
    private function output_controls()
    {
        if($this->model->get_active_page_id() == null) return;
        $sid = $this->model->get_active_root_section_id();
        $ssid = $this->model->get_active_section_id();
        if($sid == null)
        {
            $sid = $ssid;
            $ssid = null;
        }
        $params = array(
            "pid" => $this->model->get_active_page_id(),
            "sid" => $sid,
            "ssid" => $ssid
        );
        require __DIR__ . "/tpl_controls.php";
    }

    /**
     * Render a control button
     *
     * @param string $mode
     *  Indication the current cms mode. E.g 'update', 'select', etc.
     * @param array $params
     *  An array where the link parameter are stored as key => value pairs.
     * @param string $title
     *  The title of the control button
     */
    private function output_control_item($mode, $params, $title)
    {
        if($this->model->get_mode() == $mode) return;
        if(!$this->model->has_access($mode,
            $this->model->get_active_page_id())) return;

        $type = "light";
        if($mode == "update") $type = "warning text-white";
        if($mode == "delete") $type = "danger text-white";
        $url = $this->model->get_link_url("cms_" . $mode, $params);
        require __DIR__ . "/tpl_control_item.php";
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
            $this->output_local_component("page-view");
    }

    /**
     * Renders the description list components.
     */
    private function output_fields()
    {
        if($this->model->get_active_section_id())
            require __DIR__ . "/tpl_fields.php";
    }

    /**
     * Renders the description list components.
     */
    private function output_section_fields()
    {
        $this->output_local_component("section-fields");
    }

    /**
     * Renders the page properties. Page properties only rendered of a page is
     * active and the active page is not a navigation item.
     */
    private function output_page_properties()
    {
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
        $index = 0;
        foreach($this->page_info as $name => $content)
        {
            if($content == null) continue;
            if($content == "unknown") continue;
            if($name == "id") continue;
            if($name == "id_navigation_section") continue;
            $fields[] = array(
                "name" => $name,
                "content" => $content,
                "id" => "property-" . $index,
                "type" => "text",
                "id_language" => 1,
                "edit" => ($name == "title")
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
