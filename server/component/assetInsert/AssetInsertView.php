<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset insert component.
 */
class AssetInsertView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        if(!$this->controller->has_failed()) return;
        $alert = new BaseStyleComponent("alert", array(
            "type" => "danger",
            "children" => array(new BaseStyleComponent("plaintext", array(
                "text" => $this->controller->get_error_msg(),
            )))
        ));
        $alert->output_content();
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
        $local = array(__DIR__ . "/insert.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $group = $this->controller->get_new_name();
            $url = $this->model->get_link_url("assetSelect");
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $cancel_url = $this->model->get_link_url("assetSelect");
            $action_url = $this->model->get_link_url("assetInsert");
            require __DIR__ . "/tpl_insert.php";
        }
    }
}
?>
