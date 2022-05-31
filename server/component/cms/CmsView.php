<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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

    /**
     * The set of information for the selected page. See PageDB::fetch_page_info
     */
    private $page_info;

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
        $_SESSION['active_section_id'] = $this->model->get_active_section_id();
        $this->page_info = $this->model->get_page_info();
        $this->create_settings_card();
        $this->add_local_component("new_page", new BaseStyleComponent("link",
            array(
                "url" => $this->model->get_link_url("cmsInsert"),
                "css" => "ui-side-menu-button list-group-item list-group-item-action",
                "children" => array(
                    new BaseStyleComponent("markdownInline", array(
                        "text_md_inline" => '<div><span id="collapse-icon" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Create New Page" class="fas fa-file fa-fw"></span><span id="collapse-text" class="ml-1 menu-collapsed">Create New Page</span></div>',
                        "css" => ""
                    ))
                )
            )
        ));
        $this->add_local_component("import", new BaseStyleComponent("link",
            array(
                "url" => $this->model->get_link_url("cmsImport", array(
                    "type" => "section"
                )),
                "css" => "ui-side-menu-button list-group-item list-group-item-action",
                "children" => array(
                    new BaseStyleComponent("markdownInline", array(
                        "text_md_inline" => '<div><span id="collapse-icon" class="fas fa-file-upload fa-fw" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Import Section"></span><span id="collapse-text" class="ml-1 menu-collapsed">Import Section</span></div>',
                        "css" => ""
                    ))
                )
            )
        ));
        $this->add_local_component("page_preview", new BaseStyleComponent("link",
            array(
                "url" => $this->model->get_link_url($this->page_info['keyword'], array("nav" => $this->model->get_active_root_section_id())),
                "css" => "ui-side-menu-button list-group-item list-group-item-action",
                "open_in_new_tab" => true,
                "children" => array(
                    new BaseStyleComponent("markdownInline", array(
                        "text_md_inline" => '<div><span id="collapse-icon" class="fas fa-eye fa-fw" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Page Preview"></span><span id="collapse-text" class="ml-1 menu-collapsed">Page Preview</span></div>',
                        "css" => ""
                    ))
                )
            )
        ));
        $this->add_local_component("new_child_page",
            new BaseStyleComponent("card", array(
                "css" => "mb-3",
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
                "css" => "mb-3",
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
        $this->add_local_component(
            "export_section",
            new BaseStyleComponent("card", array(
                "css" => "mb-3",
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "Export Section",
                "type" => "primary",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Exporting a section will create a JSON file that contains information about the section and all its children.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Export Section",
                        "url" => $this->model->get_link_url(
                            "cmsExport",
                            array(
                                "type" => "section",
                                "id" => $this->model->get_active_section_id()
                            )
                        ),
                        "type" => "primary",
                    )),
                ),
            ))
        );
        $this->add_local_component("delete_section",
            new BaseStyleComponent("card", array(
                "css" => "mb-3",
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
        $expand_pages = ($this->model->get_active_section_id() == null);
        $this->add_list_component("page-list", "Page Index", $pages, "page",
            $expand_pages, $this->model->get_active_page_id(), ' ');

        $page_sections = $this->model->get_page_sections();
        if(!$this->model->get_services()->get_user_input()->is_new_ui_enabled()){
            // if it is old UI show sections            
            $this->add_list_component("page-section-list", "Page Sections",
                $page_sections, "sections-page", true,
                $this->model->get_active_section_id());
        }

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
            foreach($page_sections as $section)
                $page_components[] = new StyleComponent(
                    $this->model->get_services(),
                    intval($section['id']), array(),
                    $this->model->get_cms_page_id());
        else
            $page_components[] = new StyleComponent(
                $this->model->get_services(),
                $this->model->get_active_root_section_id(), array(),
                $this->model->get_cms_page_id());
        if(count($page_components) == 0)
        {
            $text = new BaseStyleComponent("plaintext", array(
                "text" => "No CMS view available for this page."
            ));
            $page_components[] = $text;
        }
        $this->add_local_component("page-view",
            new BaseStyleComponent("card", array(
                "id" => "page-view",
                "title" => "Page View",
                "is_collapsible" => true,
                "is_expanded" => ($this->model->get_active_section_id() == null),
                "css" => "mb-3 section-view w-100",
                "children" => $page_components,
            ))
        );
        if($this->model->get_active_section_id() != null)
            $this->add_local_component("section-view",
                new BaseStyleComponent("card", array(
                    "css" => "mb-3 section-view",
                    "is_collapsible" => true,
                    "title" => "Section View",
                    "id" => "section-view",
                    "children" => array(new StyleComponent(
                        $this->model->get_services(),
                        $this->model->get_active_section_id(),
                        array(),
                        $this->model->get_cms_page_id()
                    ))
                ))
            );

        // debug
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
                    new BaseStyleComponent("plaintext", array("text" => "[".date("H:i:s")."] " . $msg))
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
     *  Indicates whether the card style is expanded or not.
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
                "css" => "mt-3 mb-3 ui-card-list menu-collapsed",
                "is_expanded" =>  $this->model->get_services()->get_user_input()->is_new_ui_enabled() || $is_expanded_root, // if the new UI is enabled always expand the page index
                "is_collapsible" => true,
                "title" => $title,
                "children" => array($content),
                "id" => "ui-page-index"
            )
        ));
    }

    /**
     * Creates the view of section and page fields.
     *
     * @param array $fields
     *  The fields array where each field is defined in
     * @param boolean $is_new_ui = false
     * If true, the fields are loaded for the new UI
     *  CmsModel::add_property_item.
     * @retval array
     *  Return array with fields to be displayed.
     */
    private function get_children_fields_view_mode($fields, $is_new_ui){
        $children = [];
        $content_fields = [];
            $properties = [];
            foreach ($fields as $field) {
                $new_field_item = $this->create_field_item($field);
                if ($new_field_item) {
                    if ($is_new_ui && $field['type']) {
                        if (isset($field['display']) && $field['display'] == 1) {
                            $content_fields[] = $new_field_item;
                        } else {
                            $properties[] = $new_field_item;
                        }
                    } else {
                        $children[] = $new_field_item;
                    }
                }
            }
            if (count($content_fields) > 0) {
                // if there are content fields we put them in a card
                $card_content_fields = new BaseStyleComponent("card", array(
                    "css" => "ui-card-list",
                    "id" => "ui-card-content",
                    "is_expanded" => false,
                    "is_collapsible" => true,
                    "title" => "Content",
                    "children" => $content_fields
                ));
                $children[] = $card_content_fields;
            }

            if (count($properties) > 0) {
                // if there are content fields we put them in a card
                $card_properties_fields = new BaseStyleComponent("card", array(
                    "css" => "ui-card-list properties-fields",
                    "id" => "ui-card-properties",
                    "is_expanded" => false,
                    "is_collapsible" => true,
                    "title" => "Properties",
                    "children" => $properties
                ));
                $children[] = $card_properties_fields;
            }
            return $children;
    }

    /**
     * Add the page property list to the local component list. The page property
     * list is wrapped by a collapsible card component.
     */
    private function add_page_property_list()
    {
        $is_new_ui = $this->model->get_services()->get_user_input()->is_new_ui_enabled();
        $children = array();
        if ($is_new_ui) {
            // show page properties and make them editable
        } else {
            $children[] = new BaseStyleComponent("template", array(
                "path" => __DIR__ . "/tpl_page_properties.php",
                "items" => array(
                    "keyword_title" => "Name:",
                    "keyword" => $this->page_info['keyword'],
                    "url_title" => "Url:",
                    "url" => $this->page_info['url'],
                    "protocol_title" => "Protocol:",
                    "protocol" => $this->page_info['protocol'],
                    "page_access_title" => "Page Access:",
                    "page_access" => $this->model->get_db()->get_lookup_value_by_id($this->page_info['id_pageAccessTypes'])
                ),
            ));
        }
        $fields = $this->model->get_page_properties();

        $url_edit = "";
        if($this->model->get_mode() == "update")
        {
            $children[] =  $this->create_field_form($fields, $is_new_ui);
            $type = "warning";
        }
        else
        {
            $fields_view_mode = $this->get_children_fields_view_mode($fields, $is_new_ui);
            $children = array_merge($children, $fields_view_mode);

            $type = "light";
            if($this->model->has_access("update",
                    $this->model->get_active_page_id()))
                $url_edit = $this->model->get_link_url("cmsUpdate",
                    $this->model->get_current_url_params());
        }
        $this->add_local_component("page-fields",
            new BaseStyleComponent("card", array(
                "css" => "mb-3 ui-card-properties properties-collapsed",  
                "id" => "ui-fields-holder",
                "is_collapsible" => false,
                "is_expanded" => true,
                "title" => "Page Properties",
                "children" => $children,
                "type" => $type,
                "url_edit" => $url_edit,
            )
        ));
    }

    /**
     * Add the section field list to the local component list. The section field
     * list is wrapped by a card component.
     */
    private function add_section_field_list()
    {
        $is_new_ui = $this->model->get_services()->get_user_input()->is_new_ui_enabled();
        $children = array();
        $section_info = $this->model->get_section_info();
        if (!$this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            // if old UI show static name and style type
            $children[] = new BaseStyleComponent("template", array(
                "path" => __DIR__ . "/tpl_section_properties.php",
                "items" => array(
                    "section_name_title" => "Section Name:",
                    "section_name" => $section_info['name'],
                    "section_style_title" => "Section Style:",
                    "section_style" => $section_info['style']
                ),
            ));
        }
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
            $children[] = $this->create_field_form($fields, $is_new_ui);
            $type = "warning";
        }
        else
        {
            $fields_view_mode = $this->get_children_fields_view_mode($fields, $is_new_ui);
            $children = array_merge($children, $fields_view_mode);
            $type = "light";
            if($this->model->has_access("update",
                    $this->model->get_active_page_id()))
                $url_edit = $this->model->get_link_url("cmsUpdate",
                    $this->model->get_current_url_params());
        }
        $this->add_local_component("section-fields",
            new BaseStyleComponent("card", array(
                "css" => "mb-3 ui-card-properties properties-collapsed",
                "id" => "ui-fields-holder",
                "is_collapsible" => false,
                "title" => "Section Properties",
                "children" => $children,
                "type" => $type,
                "url_edit" => $url_edit,
            )
        ));
    }

    /**
     * Creates a the field form that allows to update section and page fields.
     *
     * @param array $fields
     *  The fields array where each field is defined in
     * @param boolean $is_new_ui = false
     * If true, the fields are loaded for the new UI
     *  CmsModel::add_property_item.
     * @retval object
     *  A form component.
     */
    private function create_field_form($fields, $is_new_ui = false)
    {
        $content_fields = [];
        $properties = [];

        $form_items = array();
        $form_items[] = new BaseStyleComponent("input", array(
            "value" => "update",
            "name" => "mode",
            "type_input" => "hidden",
        ));

        $form_items[] = new BaseStyleComponent("input", array(
            "value" => $this->model->get_id_root_section(),
            "name" => "id_section",
            "type_input" => "hidden",
        ));

        if ($is_new_ui) {
            foreach ($fields as $field) {
                if (isset($field['display']) && $field['display'] == 1) {
                    // it is a content field
                    $new_field = $this->create_field_form_item($field);
                    if ($new_field) {
                        $content_fields[] = $new_field;
                    }
                } else {
                    // it is a property field
                    $new_field = $this->create_field_form_item($field);
                    if ($new_field) {
                        $properties[] = $new_field;
                    }
                }
            }
            if (count($content_fields) > 0) {
                // if there are content fields we put them in a card
                $card_content_fields = new BaseStyleComponent("card", array(
                    "css" => "ui-card-list",
                    "id" => "ui-card-content",
                    "is_expanded" => false,
                    "is_collapsible" => true,
                    "title" => "Content",
                    "children" => $content_fields
                ));
                $form_items[] = $card_content_fields;
            }

            if (count($properties) > 0) {
                // if there are content fields we put them in a card
                $card_properties_fields = new BaseStyleComponent("card", array(
                    "css" => "ui-card-list properties-fields",
                    "id" => "ui-card-properties",
                    "is_expanded" => false,
                    "is_collapsible" => true,
                    "title" => "Properties",
                    "children" => $properties
                ));
                $form_items[] = $card_properties_fields;
            }
        } else {
            foreach ($fields as $field) {
                $new_field = $this->create_field_form_item($field);
                if ($new_field) {
                    $form_items[] = $new_field;
                }
            }
        }
        $section_info = $this->model->get_section_info();
        if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            $form_items[] = new BaseStyleComponent("div", array(
                "css" => "w-100 p-2",
                "children" => array(
                    new BaseStyleComponent("button", array(
                        "label" => "Create New Child Page",
                        "url" => $this->model->get_id_root_section() || !$this->model->can_create_new_child_page() ? null : $this->model->get_link_url("cmsInsert", array("pid" => $this->model->get_active_page_id())),
                        "css" => "w-100 mb-2 btn-sm",
                        "id" => "new-ui-create-child-page"
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Export " . ($this->model->get_id_root_section() ? 'section' : 'page'),
                        "url" => $this->model->get_link_url(
                            "cmsExport",
                            array(
                                "type" => "section",
                                "id" => $this->model->get_active_section_id()
                            )
                        ),
                        "css" => "w-100 mb-2 btn-sm",
                        "id" => "new-ui-export"
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Delete " . ($this->model->get_id_root_section() ? 'section' : 'page'),
                        "url" => '#',
                        "type" => "danger",
                        "css" => "w-100 btn-sm",
                        "id" => "new-ui-delete",
                        "data" => array(
                            "name" => $this->model->get_id_root_section() ? $section_info['name'] : $this->model->get_page_info()['keyword'],
                            "id" => $this->model->get_id_root_section() ? $this->model->get_id_root_section() : $this->model->get_active_page_id(),
                            "del_url" => $this->model->get_id_root_section() ? $this->model->get_link_url("cmsDelete", $this->model->get_current_url_params()) : $this->model->get_link_url("cmsDelete", array("pid" => $this->model->get_active_page_id())),
                            "cms_url" => $this->model->get_id_root_section() ? $this->model->get_link_url("cmsUpdate", array("pid" => $this->model->get_active_page_id(), "mode" => UPDATE, "type" => "prop")) : $this->model->get_link_url("cmsSelect", array("pid" => null)),
                            "relation" => $this->model->get_id_root_section() ? RELATION_SECTION : RELATION_PAGE
                        )
                    ))
                )
            ));
        }

        $params = $this->model->get_current_url_params();
        return new BaseStyleComponent("form", array(
            "url" => $_SERVER['REQUEST_URI'],
            "label" => "Save",
            "type" => "warning",
            "children" => $form_items,
            "url_cancel" => $this->model->get_link_url("cmsSelect", $params),
        ));
    }

    /**
     * Creates a form field item from components.
     *
     * @param array $field
     *  the field array with keys as definde in CmsModel::add_property_item.
     * @retval object or false
     *  A descriptionItem component.
     */
    private function create_field_form_item($field)
    {
        if ($field['type'] == "style-list" && $this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            // children are not needed for the new UI
            return false;
        }
        $children = array();
        $field_name_prefix = "fields[" . $field['name'] . "]["
            . $field['id_language'] . "]" . "[" . $field['id_gender'] . "]";
        $children[] = new BaseStyleComponent("input", array(
            "value" => $field['id'],
            "name" => $field_name_prefix . "[id]",
            "type_input" => "hidden",
        ));
        $children[] = new BaseStyleComponent("input", array(
            "value" => $field['type'],
            "name" => $field_name_prefix . "[type]",
            "type_input" => "hidden",
        ));
        $children[] = new BaseStyleComponent("input", array(
            "value" => $field['relation'],
            "name" => $field_name_prefix . "[relation]",
            "type_input" => "hidden",
        ));
        $field_name_content = $field_name_prefix . "[content]";
        if(in_array($field['type'],
                array("text", "number", "markdown-inline", "time", "date")))
            $children[] = new BaseStyleComponent("input", array(
                "value" => $field['content'],
                "name" => $field_name_content,
                "type_input" => $field['type'],
                "is_required" => isset($field['is_required']) ? $field['is_required'] : 0,
                "format" => isset($field['format']) ? $field['format'] : '',
            ));
        if($field['type'] === "checkbox")
            $children[] = new BaseStyleComponent("input", array(
                "value" => ($field['content'] != '0') ? $field['content'] : "",
                "name" => $field_name_content,            
                "type_input" => $field['type'],
            ));
        else if(in_array($field['type'], array("textarea", "markdown", "json", "code", "email")))
            $children[] = new BaseStyleComponent("textarea", array(
                "value" => $field['content'],
                "name" => $field_name_content,
                "css" => "style-" . $field['type'],
                "type_input" => $field['type'],
            ));
        else if($field['type'] == "type-input")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => ($field['content'] == "") ? "text" : $field['content'],
                "name" => $field_name_prefix . "[content]",
                "type_input" => $field['type'],
                "items" => array(
                    array("value" => "checkbox", "text" => "checkbox"),
                    array("value" => "color", "text" => "color"),
                    array("value" => "date", "text" => "date"),
                    array("value" => "datetime-local", "text" => "datetime-local"),
                    array("value" => "datetime", "text" => "datetime"),
                    array("value" => "email", "text" => "email"),
                    array("value" => "month", "text" => "month"),
                    array("value" => "number", "text" => "number"),
                    array("value" => "password", "text" => "password"),
                    array("value" => "range", "text" => "range"),
                    array("value" => "search", "text" => "search"),
                    array("value" => "tel", "text" => "tel"),
                    array("value" => "text", "text" => "text"),
                    array("value" => "time", "text" => "time"),
                    array("value" => "url", "text" => "url"),
                    array("value" => "week", "text" => "week"),
                ),
            ));
        }
        else if($field['type'] == "style-bootstrap")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => ($field['content'] == "") ? "primary" : $field['content'],
                "name" => $field_name_prefix . "[content]",
                "type_input" => $field['type'],
                "items" => array(
                    array("value" => "primary", "text" => "primary"),
                    array("value" => "secondary", "text" => "secondary"),
                    array("value" => "success", "text" => "success"),
                    array("value" => "danger", "text" => "danger"),
                    array("value" => "warning", "text" => "warning"),
                    array("value" => "info", "text" => "info"),
                    array("value" => "light", "text" => "light"),
                    array("value" => "dark", "text" => "dark"),
                    array("value" => "none", "text" => "none"),
                ),
            ));
        } else if ($field['type'] == "style-list") {
            $children[] = new BaseStyleComponent("input", array(
                "value" => "",
                "name" => $field_name_prefix . "[content]",
                "type_input" => "hidden",
            ));
            $children[] = new BaseStyleComponent("sortableList", array(
                "is_sortable" => true,
                "is_editable" => true,
                "items" => (in_array($field['relation'], array(RELATION_PAGE_CHILDREN, RELATION_PAGE_NAV, RELATION_SECTION_NAV)) ? $field['content'] : $this->model->fetch_section_hierarchy($field['id_sections'], false)),
            ));
        }
        else if($field['type'] == "data-source")
        {
            $children[] = new BaseStyleComponent("autocomplete", array(
                "value" => $field['content'],
                "name_value_field" => $field_name_content,
                "placeholder" => "Search for a stored Data Source",
                "name" => "data_source_search",
                "callback_class" => "AjaxSearch",
                "callback_method" => "search_data_source",
                "show_value" => true
            ));
        }
        else if($field['type'] == "anchor-section")
        {
            $children[] = new BaseStyleComponent("autocomplete", array(
                "value" => $field['content'],
                "name_value_field" => $field_name_content,
                "placeholder" => "Search for an Anchor Section Name",
                "name" => "anchor_section_search",
                "callback_class" => "AjaxSearch",
                "callback_method" => "search_anchor_section",
                "show_value" => true
            ));
        }
        else if($field['type'] == "select-group")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field_name_prefix . "[content]",
                "items" => $this->model->get_db()->fetch_table_as_select_values('groups', 'id', array('name'))
            ));
        } else if ($field['type'] == "select-qualtrics-survey") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field_name_prefix . "[content]",
                "max" => 10,
                "live_search" => 1,
                "is_required" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('qualtricsSurveys', 'id', array('name', 'qualtrics_survey_id'))
            ));
        } else if ($field['type'] == "select-platform") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field_name_prefix . "[content]",
                "max" => 10,
                "live_search" => 1,
                "is_required" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'id', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => pageAccessTypes))
            ));
        } else if ($field['type'] == "select-formName") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field_name_prefix . "[content]",
                "max" => 10,
                "live_search" => 1,
                "is_required" => 1, 
                "items" => $this->model->get_db()->fetch_table_as_select_values('view_data_tables', 'form_id_plus_type', array('orig_name'))
            ));
        }
        else if($field['type'] == "select-plugin")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field_name_prefix . "[content]",
                "max" => 10,
                "live_search" => 1,
                "is_required" => 1, 
                "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => plugins))
            ));
        }
        else if($field['type'] == "condition")
        {
            $children[] = new BaseStyleComponent("conditionBuilder", array(
                "value" => $field['content'],
                "name" => $field_name_content
            ));
        }
        else if($field['type'] == "data-config")
        {
            $children[] = new BaseStyleComponent("dataConfigBuilder", array(
                "value" => $field['content'],
                "name" => $field_name_content
            ));
        }

        return new BaseStyleComponent("descriptionItem", array(
            "gender" => isset($field['gender']) ? $field['gender'] : '',
            "title" => isset($field['label']) ? $field['label'] : $field['name'],
            "type_input" => $field['type'],
            "locale" => isset($field['gender']) ? $field['locale'] : '',
            "help" => $field['help'],
            "display" => isset($field['display']) ? $field['display'] : 0,
            "css" => ($field['hidden']  == 1 ? 'd-none' : ($this->model->get_services()->get_user_input()->is_new_ui_enabled() ? 'border-0' : '')),
            "children" => $children
        ));
    }

    /**
     * Creates a field item from components.
     *
     * @param array $field
     *  the field array with keys as definde in CmsModel::add_property_item.
     * @retval object or false
     *  A descriptionItem component.
     */
    private function create_field_item($field)
    {
        if ($field['type'] == "style-list" && $this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            // children are not needed for the new UI
            return false;
        }
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
                "items" => (in_array($field['relation'], array(RELATION_PAGE_CHILDREN, RELATION_PAGE_NAV, RELATION_SECTION_NAV)) ? $field['content'] : $this->model->fetch_section_hierarchy($field['id_sections'], false)),
                "label_add" => "Add",
                "url_add" => $insert_target,
                "url_delete" => $delete_target,
            ));
        }
        else if($field['type'] === "checkbox" && $field['content'] != "")
            $children[] = new BaseStyleComponent("template", array(
                "path" => __DIR__ . "/tpl_checkbox_field.php",
                "items" => array("is_checked" => ($field['content'] != "0")),
            ));
        else if ($field['type'] == "select-group") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field['name'],
                "disabled" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('groups', 'id', array('name'))
            ));
        }
        else if ($field['type'] == "select-qualtrics-survey") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field['name'],
                "disabled" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('qualtricsSurveys', 'id', array('name', 'qualtrics_survey_id'))
            ));
        }
        else if ($field['type'] == "select-platform") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field['name'],
                "disabled" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => pageAccessTypes))
            ));
        }
        else if ($field['type'] == "select-formName") {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field['name'],
                "disabled" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('view_data_tables', 'form_id_plus_type', array('orig_name'))
            ));
        }
        else if($field['type'] == "select-plugin")
        {
            $children[] = new BaseStyleComponent("select", array(
                "value" => $field['content'],
                "name" => $field['name'],
                "disabled" => 1,
                "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => plugins))
            ));
        }
        else if($field['type'] == "condition")
        {
            // do not show the whole condition as it takes a lof of space. 
            $children[] = new BaseStyleComponent("rawText", array(
                "text" => $field['content'] && $field['content'] != 'null' ? 'exists' : $field['content']
            ));
        }
        else if($field['type'] == "data-config")
        {
            // do not show the whole condition as it takes a lof of space. 
            $children[] = new BaseStyleComponent("rawText", array(
                "text" => $field['content'] && $field['content'] != '[]' ? 'exists' : $field['content']
            ));
        }
        else if($field['content'] != null)
            $children[] = new BaseStyleComponent("rawText", array(
                "text" => $field['content']
            ));
        $ar = array(
            "gender" => isset($field['gender']) ? $field['gender'] : '',
            "title" => isset($field['label']) ? $field['label'] : $field['name'],
            "locale" => isset($field['gender']) ? $field['locale'] : '',
            "alt" => "field is not set",
            "help" => $field['help'],
            "display" => isset($field['display']) ? $field['display'] : 0,
            "css" => ($field['hidden']  == 1 ? 'd-none' : ($this->model->get_services()->get_user_input()->is_new_ui_enabled() ? 'border-0' : '')),
            "children" => $children
        );
        return new BaseStyleComponent("descriptionItem", $ar);
    }

    /**
     * Create the card where CMS settings can be entered. These settings
     * include language and gender settings.
     */
    private function create_settings_card()
    {
        $languages = $this->model->get_db()->fetch_languages();
        $genders = $this->model->get_db()->fetch_genders();
        foreach ($languages as $language) {
            $languages_options[] = array(
                "value" => $language['id'],
                "text" => $language['language']
            );
        }
        foreach ($genders as $gender) {
            $gender_options[] = array(
                "value" => $gender['id'],
                "text" => $gender['name']
            );
        }
        $this->add_local_component("settings-card", new BaseStyleComponent(
            "card",
            array(
                "css" => "mb-3 menu-collapsed ui-card-list",
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "CMS Settings",
                "id" => "cms-settings",
                "children" => array(new BaseStyleComponent("form", array(
                    "url" => $this->model->get_link_url(
                        "cmsSelect",
                        $this->model->get_current_url_params()
                    ),
                    "label" => "Save",
                    "children" => array(
                        new BaseStyleComponent("select", array(
                            "label" => "Select CMS Content Field Language",
                            "value" => explode(',', $_SESSION['cms_language']),
                            "name" => "cms_language[]",
                            "items" => $languages_options,
                            "is_multiple" => true,
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Select CMS Content Field Gender",
                            "value" => explode(',', $_SESSION['cms_gender']),
                            "name" => "cms_gender[]",
                            "items" => $gender_options,
                            "is_multiple" => true,
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Select CMS Preview Language",
                            "value" => $_SESSION['language'],
                            "name" => "language",
                            "items" => $languages_options,
                            "is_multiple" => false,
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Select CMS Preview Gender",
                            "value" => $_SESSION['gender'],
                            "name" => "gender",
                            "items" => $gender_options,
                            "is_multiple" => false,
                        ))
                    )
                ))),
            )
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
        if($this->model->get_active_page_id() != null){
            if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
                require __DIR__ . "/tpl_new_ui/tpl_breadcrumb.php";
            } else {
                require __DIR__ . "/tpl_breadcrumb.php";
            }
        }            
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

    /**
     * Render the new page button.
     */
    private function output_create_new_button()
    {
        if($this->model->can_create_new_page())
            $this->output_local_component("new_page");
    }

    /**
     * Render the page preview button.
     */
    private function output_page_preview_button()
    {
        $this->output_local_component("page_preview");
    }

    /**
     * Render the import page/section button.
     */
    private function output_import_button()
    {
        if($this->model->can_import_section())
            $this->output_local_component("import");
    }

    /**
     * Renders the description list components.
     */
    private function output_fields()
    {
        $this->output_local_component("page-fields");
        $this->output_local_component("section-fields");
        if (!$this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            // if not new UI output these
            if($this->model->can_create_new_child_page())
                $this->output_local_component("new_child_page");
            if ($this->model->can_export_section()) {
                $this->output_local_component("export_section");
            }
            if($this->model->can_delete_page())
            {
                if($this->model->get_active_section_id() == null) {
                    $this->output_local_component("delete_page");
                }
                else if($this->model->can_delete_section()) {
                    $this->output_local_component("delete_section");
                }
            }     
        }   
    }

    /**
     * Renders all the nested lists.
     */
    private function output_lists()
    {
        $this->output_local_component("navigation-hierarchy-list");
        $this->output_local_component("page-list");        
        $this->output_local_component("page-section-list");
        $this->output_local_component("settings-card");
    }

    /**
     * Renders the main content.
     */
    private function output_page_content()
    {
        if ($this->model->get_active_page_id() == null)
            require __DIR__ . "/tpl_intro_cms.php";
        else {
            if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
                require __DIR__ . "/tpl_new_ui/tpl_cms.php";
            } else {
                require __DIR__ . "/tpl_cms.php";
            }
        }
    }

    /**
     * Renders the page preview card.
     */
    private function output_page_overview()
    {
        $url = $this->model->get_link_url($this->page_info['keyword'],
                array("nav" => $this->model->get_active_root_section_id()));
        if($url === "")
            return;
        if($this->model->get_active_section_id())
            $url .= '#section-' . $this->model->get_active_section_id();
        $_SESSION['cms_edit_url'] = $this->model->get_current_url_params();
        $button = new BaseStyleComponent("button", array(
            "label" => "To the Page",
            "css" => "d-block m-1 mb-3",
            "url" => $url,
            "type" => "secondary",
        ));
        $button->output_content();
        $div = new BaseStyleComponent("div", array(
            "css" => "cms-page-overview page-view"
        ));
        $div->output_content();
    }

    /**
     * Renders the page preview card.
     */
    private function output_page_preview()
    {
        if ($this->model->is_navigation_main())
            require __DIR__ . "/tpl_intro_nav.php";
        else {
            if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
                // show section if a section is selected otherwise show the whole page
                $section_view = $this->get_local_component('section-view');
                if ($section_view != null) {
                    $this->output_local_component("section-view");
                } else {
                    $this->output_local_component("page-view");
                }
            } else {
                $this->output_local_component("section-view");
                $this->output_local_component("page-view");
            }
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
        if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            array_push($local, __DIR__ . "/cms_ui.css");
        }
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
        if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            array_push($local, __DIR__ . "/../cmsImport/js/import.js");
            array_push($local, __DIR__ . "/cms_ui.js");
        }
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            require __DIR__ . "/tpl_new_ui/tpl_main.php";
        } else {
            require __DIR__ . "/tpl_main.php";
        }
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the modal for the new UI for add section if it is needed
     */
    public function output_modal_add_section()
    {
        if (
            method_exists($this->model, "is_cms_page") && $this->model->is_cms_page() &&
            method_exists($this->model, "is_cms_page_editing") && $this->model->is_cms_page_editing() &&
            $this->model->get_services()->get_user_input()->is_new_ui_enabled()
        ) {
            $import_url = $this->model->get_link_url("cmsImport", array("type" => RELATION_SECTION));
            require __DIR__ . "/tpl_new_ui/tpl_modal_add_section.php";
        }
    }

    /**
     * Render output new section
     */
    public function output_add_new_section()
    {
        $styles = $this->model->get_style_list();
        require __DIR__ . "/tpl_new_ui/tpl_add_new_section.php";
    }

    /**
     * Render output add unassigned section
     */
    public function output_add_unassigned_section()
    {
        $unassigned_sections = $this->model->fetch_unassigned_sections();
        require __DIR__ . "/tpl_new_ui/tpl_add_unassigned_section.php";
    }

    /**
     * Render output add reference section
     */
    public function output_add_reference_section()
    {
        $reference_sections = $this->model->get_reference_sections();
        require __DIR__ . "/tpl_new_ui/tpl_add_reference_section.php";
    }
}
?>
