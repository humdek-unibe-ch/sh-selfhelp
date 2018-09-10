<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The view class of the cms component.
 */
class CmsView extends BaseView
{
    /* Private Properties *****************************************************/

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
    public function __construct($model, $controller = null)
    {
        parent::__construct($model, $controller);
        $this->page_info = $this->model->get_page_info();

        $this->add_local_component("new_page", new BaseStyleComponent("button",
            array(
                "label" => "Create New Page",
                "url" => $this->model->get_link_url("cmsInsert"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            )
        ));
        $this->add_local_component("new_child_page",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Create New Child Page",
                "children" => array(
                    new BaseStyleComponent("button", array(
                        "label" => "Create New Child Page",
                        "url" => $this->model->get_link_url("cmsInsert",
                            array("pid" => $this->model->get_active_page_id())),
                        "type" => "secondary",
                    )),
                ),
            ))
        );
        $this->add_local_component("delete_page",
            new BaseStyleComponent("card", array(
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "Delete Page",
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Deleting a page will remove all fields associated to this page. This cannot be undone. Sections and child pages are not affected.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Delete Page",
                        "url" => $this->model->get_link_url("cmsDelete",
                            array("pid" => $this->model->get_active_page_id())),
                        "type" => "danger",
                    )),
                ),
            ))
        );
        $this->add_local_component("delete_section",
            new BaseStyleComponent("card", array(
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "Delete Section",
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Deleting a section will remove all data associated to this section. This cannot be undone.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Delete Section",
                        "url" => $this->model->get_link_url("cmsDelete",
                            $this->model->get_current_url_params()),
                        "type" => "danger",
                    )),
                ),
            ))
        );

        $pages = $this->model->get_pages();
        $expand_pages = !$this->model->is_navigation_item();
        $this->add_list_component("page-list", "Page Index", $pages, "page",
            $expand_pages, $this->model->get_active_page_id());

        $global_sections = $this->model->get_accessible_sections();
        $this->add_list_component("global-section-list", "Choose a Section",
            $global_sections, "global-sections", false, 0, "Search");

        $this->page_sections = $this->model->get_page_sections();
        $this->add_list_component("page-section-list", "Page Sections",
            $this->page_sections, "sections-page", true,
            $this->model->get_active_section_id());

        $this->add_list_component("navigation-hierarchy-list",
            "Navigation Hierarchy", $this->model->get_navigation_hierarchy(),
            "navigation_sections", true,
            $this->model->get_active_root_section_id());

        if($this->model->get_active_section_id())
            $this->add_section_field_list();
        else if($this->model->get_active_page_id())
            $this->add_page_property_list();

        if($controller)
        {
            if($controller->has_insert_succeeded())
            {
                $msg = "Successfully added a new section.";
                $this->add_alert_component("alert_insert_success", "success",
                    $msg);
            }
            if($controller->has_insert_failed())
            {
                $msg = "Failed to add a new section";
                $this->add_alert_component("alert_insert_failed", "danger",
                    $msg);
            }
            if($controller->has_remove_succeeded())
            {
                $msg = "Successfully removed a link to a section.";
                $this->add_alert_component("alert_remove_success", "success",
                    $msg);
            }
            if($controller->has_remove_failed())
            {
                $msg = "Failed to remove a link to a section";
                $this->add_alert_component("alert_remove_failed", "danger",
                    $msg);
            }
            $success_count = $controller->get_update_success_count();
            if($success_count > 0)
            {
                $msg = "Successfully updated " . $success_count . " fields";
                $this->add_alert_component("alert_update_success", "success",
                    $msg);
            }
            $fail_count = $controller->get_update_fail_count();
            if($fail_count > 0)
            {
                $err_string = "Bad field value in ";
                $bad_field_count = 0;
                foreach($controller->get_bad_fields() as $name => $languages)
                    foreach($languages as $id => $field)
                    {
                        $lang = $this->model->get_language($id);
                        $err_string .= "'" . $name . " [" . $lang['locale'] . "]',";
                        $bad_field_count++;
                    }

                if($bad_field_count == 0)
                    $err_string = "An internal error occured. Try again or contact the system adiminstrator";
                $msg = "Failed to update " . $fail_count . " fields: " . $err_string;
                $this->add_alert_component("alert_update_failed", "danger",
                    $msg);
            }
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
                "is_collapsible" => false,
                "title" => "Page View",
                "children" => $page_components,
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
                "is_dismissable" => true
            )
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
     * @param string $search_text
     *  The placeholder  text to be displayed in the search form.
     */
    private function add_list_component($name, $title, $items, $prefix,
        $is_expanded_root = false, $id_active = 0, $search_text = "")
    {
        if(count($items) == 0) return;
        $content = new BaseStyleComponent("nestedList", array(
            "items" => $items,
            "id_prefix" => $prefix,
            "is_expanded" => false,
            "id_active" => $id_active,
            "search_text" => $search_text
        ));
        $this->add_local_component($name, new BaseStyleComponent("card",
            array(
                "is_expanded" => $is_expanded_root,
                "is_collapsible" => true,
                "title" => $title,
                "children" => array($content),
            )
        ));
    }

    /**
     * Add the page property list to the local component list. The page property
     * list is wrapped by a collapsible card component.
     */
    private function add_page_property_list()
    {
        $children = array();
        $children[] = new BaseStyleComponent("template", array(
            "path" => __DIR__ . "/tpl_page_properties.php",
            "items" => array(
                "keyword_title" => "Name:",
                "keyword" => $this->page_info['keyword'],
                "url_title" => "Url:",
                "url" => $this->page_info['url'],
                "protocol_title" => "Protocol:",
                "protocol" => $this->page_info['protocol'],
            ),
        ));
        $fields = $this->model->get_page_properties();

        $url_edit = "";
        if($this->model->get_mode() == "update")
        {
            $children[] = $this->create_field_form($fields);
            $type = "warning";
        }
        else
        {
            foreach($fields as $field)
                $children[] = $this->create_field_item($field);
            $type = "light";
            if($this->model->has_access("update",
                    $this->model->get_active_page_id()))
                $url_edit = $this->model->get_link_url("cmsUpdate",
                    $this->model->get_current_url_params());
        }
        $this->add_local_component("page-fields",
            new BaseStyleComponent("card", array(
                "is_collapsible" => false,
                "is_expanded" => true,
                "title" => "Page Properties",
                "children" => $children,
                "type" => $type,
                "url" => $url_edit,
            )
        ));
    }

    /**
     * Add the section field list to the local component list. The section field
     * list is wrapped by a card component.
     */
    private function add_section_field_list()
    {
        $children = array();
        $section_info = $this->model->get_section_info();
        $children[] = new BaseStyleComponent("template", array(
            "path" => __DIR__ . "/tpl_section_properties.php",
            "items" => array(
                "section_name_title" => "Section Name:",
                "section_name" => $section_info['name'],
                "section_style_title" => "Section Style:",
                "section_style" => $section_info['style']
            ),
        ));
        $type = ($this->model->get_mode() == "update") ? "warning" : "light";
        $fields = $this->model->get_section_properties();
        $url_edit = "";
        if(count($fields) == 0)
        {
            $text = "No section fields defined";
            $children[] = new BaseStyleComponent("plaintext", array(
                "text" => $text
            ));
        }
        else if($this->model->get_mode() == "update")
        {
            $children[] = $this->create_field_form($fields, true);
            $type = "warning";
        }
        else
        {
            foreach($fields as $field)
                $children[] = $this->create_field_item($field);
            $type = "light";
            if($this->model->has_access("update",
                    $this->model->get_active_page_id()))
                $url_edit = $this->model->get_link_url("cmsUpdate",
                    $this->model->get_current_url_params());
        }
        $this->add_local_component("section-fields",
            new BaseStyleComponent("card", array(
                "is_collapsible" => false,
                "title" => "Section Properties",
                "children" => $children,
                "type" => $type,
                "url" => $url_edit,
            )
        ));
    }

    /**
     * Creates a the field form that allows to update section and page fields.
     *
     * @param array $fields
     *  The fields array where each field is defined in
     *  CmsModel::add_property_item.
     * @retval object
     *  A form component.
     */
    private function create_field_form($fields, $render_margin=false)
    {
        $form_items = array();
        $form_items[] = new BaseStyleComponent("input", array(
            "value" => "update",
            "name" => "mode",
            "type-input" => "hidden",
            "is_user_input" => false,
        ));

        if($render_margin)
        {
            $css = $this->model->get_css();
            $form_items[] = new BaseStyleComponent("descriptionItem", array(
                "title" => "Margins",
                "locale" => "all",
                "children" => array(
                    new BaseStyleComponent("template", array(
                        "path" => __DIR__ . "/tpl_margin_input.php",
                        "items" => array(
                            "checked_top" => (strpos($css, "mt") === false) ? "" : "checked",
                            "checked_right" => (strpos($css, "mr") === false) ? "" : "checked",
                            "checked_bottom" => (strpos($css, "mb") === false) ? "" : "checked",
                            "checked_left" => (strpos($css, "ml") === false) ? "" : "checked",
                        )
                    )),
                ),
            ));
        }

        foreach($fields as $field)
            $form_items[] = $this->create_field_form_item($field);


        $params = $this->model->get_current_url_params();
        return new BaseStyleComponent("form", array(
            "url" => $_SERVER['REQUEST_URI'],
            "label" => "Submit Changes",
            "type" => "warning",
            "children" => $form_items,
            "cancel" => true,
            "cancel_url" => $this->model->get_link_url("cmsSelect", $params),
        ));

    }

    /**
     * Creates a form field item from components.
     *
     * @param array $field
     *  the field array with keys as definde in CmsModel::add_property_item.
     * @retval object
     *  A descriptionItem component.
     */
    private function create_field_form_item($field)
    {
        $children = array();
        $field_name_prefix = "fields[" . $field['name'] . "]["
            . $field['id_language'] . "]";
        $children[] = new BaseStyleComponent("input", array(
            "value" => $field['id'],
            "name" => $field_name_prefix . "[id]",
            "type-input" => "hidden",
            "is_user_input" => false,
        ));
        $children[] = new BaseStyleComponent("input", array(
            "value" => $field['type'],
            "name" => $field_name_prefix . "[type]",
            "type-input" => "hidden",
            "is_user_input" => false,
        ));
        $children[] = new BaseStyleComponent("input", array(
            "value" => $field['relation'],
            "name" => $field_name_prefix . "[relation]",
            "type-input" => "hidden",
            "is_user_input" => false,
        ));
        $field_name_content = $field_name_prefix . "[content]";
        if(in_array($field['type'],
                array("text", "number", "checkbox", "markdown-inline")))
            $children[] = new BaseStyleComponent("input", array(
                "value" => $field['content'],
                "name" => $field_name_content,
                "type-input" => $field['type'],
                "is_user_input" => false,
            ));
        else if(in_array($field['type'], array("textarea", "markdown", "json")))
            $children[] = new BaseStyleComponent("textarea", array(
                "value" => $field['content'],
                "name" => $field_name_content,
                "is_user_input" => false,
            ));
        else if($field['type'] == "type-input")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => ($field['content'] == "") ? "text" : $field['content'],
                "name" => $field_name_prefix . "[content]",
                "items" => array(
                    array("value" => "text", "text" => "text"),
                    array("value" => "checkbox", "text" => "checkbox"),
                    array("value" => "color", "text" => "color"),
                    array("value" => "date", "text" => "date"),
                    array("value" => "datetime-local", "text" => "datetime-local"),
                    array("value" => "email", "text" => "email"),
                    array("value" => "month", "text" => "month"),
                    array("value" => "number", "text" => "number"),
                    array("value" => "range", "text" => "range"),
                    array("value" => "search", "text" => "search"),
                    array("value" => "tel", "text" => "tel"),
                    array("value" => "time", "text" => "time"),
                    array("value" => "url", "text" => "url"),
                    array("value" => "week", "text" => "week"),
                ),
                "is_user_input" => false,
            ));
        }
        else if($field['type'] == "style-bootstrap")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => ($field['content'] == "") ? "primary" : $field['content'],
                "name" => $field_name_prefix . "[content]",
                "items" => array(
                    array("value" => "primary", "text" => "primary"),
                    array("value" => "secondary", "text" => "secondary"),
                    array("value" => "success", "text" => "success"),
                    array("value" => "danger", "text" => "danger"),
                    array("value" => "warning", "text" => "warning"),
                    array("value" => "info", "text" => "info"),
                    array("value" => "light", "text" => "light"),
                    array("value" => "dark", "text" => "dark"),
                ),
                "is_user_input" => false,
            ));
        }
        else if($field['type'] == "style-list")
        {
            $children[] = new BaseStyleComponent("input", array(
                "value" => "",
                "name" => $field_name_prefix . "[content]",
                "type-input" => "hidden",
                "is_user_input" => false,
            ));
            $children[] = new BaseStyleComponent("sortableList", array(
                "is_sortable" => true,
                "is_editable" => true,
                "items" => $field['content'],
                "is_user_input" => false,
            ));
        }
        return new BaseStyleComponent("descriptionItem", array(
            "title" => $field['name'],
            "type-input" => $field['type'],
            "locale" => $field['locale'],
            "children" => $children
        ));
    }

    /**
     * Creates a field item from components.
     *
     * @param array $field
     *  the field array with keys as definde in CmsModel::add_property_item.
     * @retval object
     *  A descriptionItem component.
     */
    private function create_field_item($field)
    {
        $children = array();
        if($field['type'] == "style-list")
        {
            $params = $this->model->get_current_url_params();
            $params['type'] = $field['relation'];
            $params_insert = $params;
            $params_insert['mode'] = "insert";
            $insert_target = "";
            if($this->model->has_access("insert",
                    $this->model->get_active_page_id()))
                $insert_target = $this->model->get_link_url("cmsUpdate",
                    $params_insert);
            $delete_target = "";
            $params_delete = $params;
            $params_delete['mode'] = "delete";
            $params_delete['did'] = ":did";
            if($this->model->has_access("delete",
                    $this->model->get_active_page_id()))
                $delete_target = $this->model->get_link_url("cmsUpdate",
                    $params_delete);
            $children[] = new BaseStyleComponent("sortableList", array(
                "is_editable" => true,
                "items" => $field['content'],
                "label" => "Add",
                "insert_target" => $insert_target,
                "delete_target" => $delete_target,
                "is_user_input" => false,
            ));
        }
        else if($field['content'] != null)
            $children[] = new BaseStyleComponent("rawText", array(
                "text" => $field['content']
            ));
        return new BaseStyleComponent("descriptionItem", array(
            "title" => $field['name'],
            "locale" => $field['locale'],
            "alt" => "field is not set",
            "children" => $children
        ));
    }

    /**
     * Renders alerts.
     */
    private function output_alerts()
    {
        $this->output_local_component("alert_insert_success");
        $this->output_local_component("alert_insert_failed");
        $this->output_local_component("alert_update_success");
        $this->output_local_component("alert_update_failed");
        $this->output_local_component("alert_remove_success");
        $this->output_local_component("alert_remove_failed");
    }

    /**
     * Renders the section path as breadcrumbs.
     */
    private function output_breadcrumb()
    {
        if($this->model->get_active_page_id() != null)
            require __DIR__ . "/tpl_breadcrumb.php";
    }

    /**
     * Renders all section path items as breadcrumb children.
     */
    private function output_breadcrumb_children()
    {
        foreach($this->model->get_section_path() as $item)
        {
            $label = $item["title"];
            $url = $item["url"];
            if($url == null)
                require __DIR__ . "/tpl_breadcrumb_item_active.php";
            else
                require __DIR__ . "/tpl_breadcrumb_item.php";
        }
    }

    private function output_create_new_button()
    {
        if($this->model->can_create_new_page())
            $this->output_local_component("new_page");
    }

    /**
     * Renders the description list components.
     */
    private function output_fields()
    {
        $this->output_local_component("page-fields");
        $this->output_local_component("section-fields");
        if($this->model->can_create_new_child_page())
            $this->output_local_component("new_child_page");
        if($this->model->can_delete_page())
        {
            if($this->model->get_active_section_id() == null)
                $this->output_local_component("delete_page");
            else
                $this->output_local_component("delete_section");
        }
    }

    /**
     * Renders all the nested lists.
     */
    private function output_lists()
    {
        $this->output_local_component("page-list");
        $this->output_local_component("navigation-hierarchy-list");
        $this->output_local_component("page-section-list");
    }

    /**
     * Renders the main content.
     */
    private function output_page_content()
    {
        if($this->model->get_active_page_id() == null)
            require __DIR__ . "/tpl_intro_cms.php";
        else
            require __DIR__ . "/tpl_cms.php";
    }

    /**
     * Renders the page preview card.
     */
    private function output_page_preview()
    {
        if($this->model->is_navigation_main())
            require __DIR__ . "/tpl_intro_nav.php";
        else
            $this->output_local_component("page-view");
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
        require __DIR__ . "/tpl_main.php";
    }
}
?>
