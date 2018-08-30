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
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to create a new page.",
                )))
            ))
        );
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
            $action_url = $this->model->get_link_url("cmsInsert");
            $cancel_url = $this->model->get_link_url("cmsSelect");
            require __DIR__ . "/tpl_cms_insert.php";
        }
    }
}
?>
