<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The insert view class of the cms component.
 */
class CmsInsertView extends BaseView
{
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
        $model->update_insert_properties();

        $this->add_local_component("allowed-sections",
            new BaseStyleComponent("card", array(
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "Sections in Use",
                "children" => array(new BaseStyleComponent("nestedList", array(
                    "items" => $this->model->get_accessible_sections(),
                    "id_prefix" => "sections-search-accessible",
                    "is_expanded" => false,
                    "has_chevron" => false,
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
                    "has_chevron" => false,
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
        $local = array(__DIR__ . "/cms_insert.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url("cms_update",
            $this->model->get_current_url_params());
        $relation = $this->model->get_relation();
        if($relation == "page_nav" || $relation == "section_nav")
            $child = "navigation";
        else
            $child = "children";

        $page_info = $this->model->get_page_info();
        $section_info = $this->model->get_section_info();
        if($this->model->get_active_section_id() == null)
            $target = "the page '" . $page_info['keyword'] . "'.";
        else
            $target = "the section '" . $section_info['name'] . "'"
                . " on page '" . $page_info['keyword'] . "'.";
        require __DIR__ . "/tpl_cms_insert.php";
    }
}
?>
