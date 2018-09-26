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

    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms update component.
     * @param string $mode
     *  This describes the update mode which can have the values
     *   - update: update the propertiy fields of a section or page.
     *   - insert: add a new section to a section or a page.
     *   - delete: remove a section from a section or a page.
     */
    public function __construct($model, $controller, $mode)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->add_local_component("allowed-sections",
            new BaseStyleComponent("card", array(
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "Sections in Use",
                "children" => array(new BaseStyleComponent("nestedList", array(
                    "items" => $this->model->get_accessible_sections(),
                    "id_prefix" => "sections-search-accessible",
                    "is_expanded" => false,
                    "is_collapsible" => false,
                    "id_active" => 0,
                    "search_text" => "Search"
                )))
            ))
        );

        $this->add_local_component("unassigned-sections",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Unassigned Sections",
                "children" => array(new BaseStyleComponent("nestedList", array(
                    "items" => $this->model->get_unassigned_sections(),
                    "id_prefix" => "sections-search-unassigned",
                    "is_expanded" => false,
                    "is_collapsible" => false,
                    "id_active" => 0,
                    "search_text" => "Search"
                )))
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the list of styles.
     */
    private function output_style_list()
    {
        $styles = $this->model->get_style_list();
        foreach($styles as $style)
        {
            $value = intval($style['id']);
            $name = $style['name'];
            require __DIR__ . "/tpl_select_option.php";
        }
    }

    /**
     * Render the list of all available sections.
     */
    private function output_section_search_list()
    {
        $this->output_local_component("unassigned-sections");
        $this->output_local_component("allowed-sections");
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
        if($relation == "page_nav" || $relation == "section_nav")
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
        if($relation == "page_nav" || $relation == "section_nav")
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
        require __DIR__ . "/tpl_cms_insert.php";
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->mode == "insert")
            $this->output_content_insert();
        else if($this->mode == "delete")
            $this->output_content_delete();
    }
}
?>
