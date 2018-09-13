<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The insert view class of the cms component.
 */
class CmsInsertView extends BaseView
{
    private $position_value;

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
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to create a new page.",
                )))
            ))
        );
        $position_value = "";
        $pages = $this->model->get_pages_header(
            $this->model->get_active_page_id());
        foreach($pages as $idx => $page)
        {
            $this->position_value .= (string)($idx * 10) . ",";
            $pages[$idx]["css"] = "fixed text-muted";
        }

        $pages[] = array("id" => "new", "title" => "New Page");
        $this->add_local_component("page-position",
            new BaseStyleComponent("sortableList", array(
                "is_sortable" => true,
                "is_editable" => true,
                "items" => $pages,
                "is_user_input" => false,
            ))
        );

        $this->position_value .= (string)(count($pages) * 10);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        if($this->controller->has_failed())
            $this->output_local_component("alert-fail");
    }

    /**
     * Render the sortable list to position the page in the header.
     */
    private function output_page_order()
    {
        $this->output_local_component("page-position");
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
        $local = array(__DIR__ . "/new_page.css");
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
        $local = array(__DIR__ . "/new_page.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $name = $this->controller->get_new_page_name();
            $url = $this->model->get_link_url("cmsSelect",
                array("pid" => $this->controller->get_new_pid()));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("cmsInsert",
                array("pid" => $this->model->get_active_page_id()));
            $cancel_url = $this->model->get_link_url("cmsSelect",
                array("pid" => $this->model->get_active_page_id()));
            require __DIR__ . "/tpl_cms_insert.php";
        }
    }
}
?>
