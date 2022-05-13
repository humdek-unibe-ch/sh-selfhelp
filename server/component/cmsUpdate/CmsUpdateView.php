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
 * The insert view class of the cms component.
 */
class CmsUpdateView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     *  This describes the update mode which can have the values
     *   - update: update the propertiy fields of a section or page.
     *   - insert: add a new section to a section or a page.
     *   - delete: remove a section from a section or a page.
     */
    private $mode;

    /**
     *  This describes the page type     
     */
    private $type;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms update component.
     * @param string $mode
     *  See CmsUpdateView::mode
     * @param string $type
     *  See CmsUpdateView::type
     */
    public function __construct($model, $controller, $mode, $type = null)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->type = $type;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the warning when an existing section is selected.
     */
    private function output_alert()
    {
        $alert = new BaseStyleComponent("alert", array(
            "css" => "alert-reuse-section d-none",
            "type" => "warning",
            "is_dismissable" => true,
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Note that a section refers to a single set of section fields. This means that changes to section fields will affect all views of the section when using the same section in different places.",
                )),
            ),
        ));
        $alert->output_content();
        $this->output_controller_alerts_fail();
    }

    /**
     * Render the list of styles.
     */
    private function output_style_list()
    {
        $styles = $this->model->get_style_list();
        foreach($styles as $style)
        {
            $value = intval($style['value']);
            $name = $style['text'];
            require __DIR__ . "/tpl_select_option.php";
        }
    }

    /**
     * Render the list of all available sections.
     */
    private function output_section_search_list()
    {
        $unassigned_sections = $this->model->get_unassigned_sections();
        $unassigned = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => true,
            "title" => "Select an Unassigned Section",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $unassigned_sections,
                "id_prefix" => "sections-search-unassigned",
                "is_expanded" => false,
                "is_collapsible" => false,
                "id_active" => 0,
                "search_text" => "Search"
            )))
        ));
        $unassigned->output_content();  
        if(count($unassigned_sections) > 0){
            // if there are unassigned sections show the delete button
            $this->output_delete_unassigned_sections_btn();
        }   
        // $allowed = new BaseStyleComponent("card", array(
        //     "css" => "mb-3",
        //     "is_expanded" => false,
        //     "is_collapsible" => true,
        //     "title" => "Select an Existing Section",
        //     "children" => array(new BaseStyleComponent("nestedList", array(
        //         "items" => $this->model->get_accessible_sections(),
        //         "id_prefix" => "sections-search-accessible",
        //         "is_expanded" => false,
        //         "is_collapsible" => false,
        //         "id_active" => 0,
        //         "search_text" => "Search"
        //     )))
        // ));
        // $allowed->output_content();
        $reference_sections = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "Select an Reference Section",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_reference_sections(),
                "id_prefix" => "sections-search-accessible",
                "is_expanded" => false,
                "is_collapsible" => false,
                "id_active" => 0,
                "search_text" => "Search"
            )))
        ));
        $reference_sections->output_content();
    }

    /**
     * Render delete unassigned sections button.
     */
    private function output_delete_unassigned_sections_btn()
    {
        $params = $this->model->get_current_url_params();
        $params["type"] = "unassigned_sections";
        $params["mode"] = "delete";
        $url = $this->model->get_link_url("cmsUpdate", $params);
        $deleteUnassignedBtn = new BaseStyleComponent("button", array(
                    "label" => "Delete All Unassigned Sections",
                    "url" => $url,
                    "type" => "danger",
                    "css" => "d-block mb-3",
                ));
        $deleteUnassignedBtn->output_content();
    }

    /**
     * Render the style group tabs.
     */
    private function output_style_tabs()
    {
        $tabs = array();
        $groups = $this->model->get_style_groups();
        $first = true;
        foreach($groups as $index => $group)
        {
            $id = intval($group['id']);
            $styles = $this->model->get_style_list($id);
            $description = $group['description'] . "\r\n\r\n";
            foreach($styles as $style)
                $description .= "- `" . $style['text'] . "` "
                    . $style['description'] . "\r\n";
            $tabs[] = new BaseStyleComponent("tab", array(
                "label" => $group["name"],
                "type" => "light",
                "id" => $index,
                "is_expanded" => $first,
                "children" => array(
                    new BaseStyleComponent("markdown", array(
                        "text_md" => $description,
                    )),
                    new BaseStyleComponent("select", array(
                        "name" => "helper-style-" . $id,
                        "alt" => "select a style",
                        "items" => $this->model->get_style_list($id),
                    )),
                ),
            ));
            $first = false;
        }

        $tabs_wrapper = new BaseStyleComponent("tabs", array(
            "children" => $tabs,
        ));
        $tabs_wrapper->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/cms_insert.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the view to delete a section.
     */
    public function output_content_delete()
    {
        $params = $this->model->get_current_url_params();
        $params["type"] = "prop";
        $params["mode"] = "update";
        $params["did"] = null;
        $url_cancel = $this->model->get_link_url("cmsSelect", $params);
        $url = $this->model->get_link_url("cmsUpdate", $params);
        $relation = $this->model->get_relation();
        if($relation == RELATION_PAGE_NAV || $relation == RELATION_SECTION_NAV)
            $child = "navigation";
        else
            $child = "children";

        $page_info = $this->model->get_page_info();
        $target_section_info = $this->model->get_section_info();
        if($this->model->get_active_section_id() == null)
            $target = "the page <code>" . $page_info['keyword'] . "</code>.";
        else
            $target = "the section <code>" . $target_section_info['name'] . "</code>"
                . " on page <code>" . $page_info['keyword'] . "</code>.";

        $did = $this->model->get_delete_id();
        $del_section_info = $this->model->get_section_info($did);
        $del_section = $del_section_info['name'];

        require __DIR__ . "/tpl_cms_delete.php";
    }

    public function output_content_delete_unassigned_sections(){
        $relation = $this->model->get_relation();
        $params = $this->model->get_current_url_params();
        $params["type"] = RELATION_PAGE_CHILDREN;
        $params["mode"] = "insert";
        $url_cancel = $this->model->get_link_url("cmsUpdate", $params);    
        $params["type"] = 'unassigned_sections';    
        $params["mode"] = "delete";
        $url = $this->model->get_link_url("cmsUpdate", $params);
        $delete_unassigned_sections = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => true,
            "type" => "danger",
            "title" => "Delete All Unassigned Sections",
            "children" => array(new BaseStyleComponent("form", array(
                "label" => "Delete",
                "url" => $url,
                "type" => "danger",
                "url_cancel" => $url_cancel,
                "children" => array(
                    new BaseStyleComponent("input", array(
                        "type_input" => "text",
                        "name" => "delete_all_unassigned_sections",
                        "is_required" => true,
                        "css" => "mb-3",
                        "placeholder" => "Enter verification",
                    )),
                    new BaseStyleComponent("input", array(
                        "type_input" => "hidden",
                        "name" => "mode",
                        "value" => "delete",
                        "is_required" => true,
                    )),
                )
            )))
        ));
        $delete_unassigned_sections->output_content();
    }

    /**
     * Render the view to insert a new section.
     */
    public function output_content_insert()
    {
        $relation = $this->model->get_relation();
        $params = $this->model->get_current_url_params();
        $params["type"] = $relation;
        $params["mode"] = "update";
        $url_cancel = $this->model->get_link_url("cmsSelect", $params);
        $url = $this->model->get_link_url("cmsUpdate", $params);
        if($relation == RELATION_PAGE_NAV || $relation == RELATION_SECTION_NAV)
            $child = "navigation";
        else
            $child = "children";

        $page_info = $this->model->get_page_info();
        $section_info = $this->model->get_section_info();
        if($this->model->get_active_section_id() == null)
            $target = "the page <code>" . $page_info['keyword'] . "</code>.";
        else
            $target = "the section <code>" . $section_info['name'] . "</code>"
                . " on page <code>" . $page_info['keyword'] . "</code>.";
        if($child == "navigation")
            require __DIR__ . "/tpl_cms_insert_nav.php";
        else
            require __DIR__ . "/tpl_cms_insert.php";
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if ($this->mode == "insert") {
            $this->output_content_insert();
        } else if ($this->mode == "delete" && $this->type == 'unassigned_sections') {
            if ($this->controller->has_succeeded()) {
                $relation = $this->model->get_relation();
                $params = $this->model->get_current_url_params();
                $params["type"] = $relation;
                $params["type"] = RELATION_PAGE_CHILDREN;
                $params["mode"] = "insert";
                $url = $this->model->get_link_url("cmsUpdate", $params);
                require __DIR__ . "/tpl_cms_delete_unassigned_sections_success.php";
            } else {
                require __DIR__ . "/tpl_cms_delete_unassigned_sections.php";
            }
        } else if ($this->mode == "delete") {
            $this->output_content_delete();
        }
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
