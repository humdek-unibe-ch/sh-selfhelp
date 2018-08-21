<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The insert view class of the cms component.
 */
class CmsDeleteView extends BaseView
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
    }

    /* Private Methods ********************************************************/

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
        $target_section_info = $this->model->get_section_info();
        if($this->model->get_active_section_id() == null)
            $target = "the page '" . $page_info['keyword'] . "'.";
        else
            $target = "the section '" . $target_section_info['name'] . "'"
                . " on page '" . $page_info['keyword'] . "'.";

        $did = $this->model->get_delete_id();
        $del_section_info = $this->model->get_section_info($did);
        $del_section = $del_section_info['name'];

        require __DIR__ . "/tpl_cms_delete.php";
    }
}
?>
