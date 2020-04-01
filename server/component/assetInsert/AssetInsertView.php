<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset insert component.
 */
class AssetInsertView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', or 'static').
     */
    public function __construct($model, $controller, $mode)
    {
        $this->mode = $mode;
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
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
            $title = array(
                "css" => "Upload a CSS File",
                "asset" => "Upload an Asset File",
                "static" => "Upload a Static Data File",
            );
            $cancel_url = $this->model->get_link_url("assetSelect");
            $action_url = $this->model->get_link_url("assetInsert",
                array('mode' => $this->mode));
            require __DIR__ . "/tpl_insert.php";
        }
    }
}
?>
