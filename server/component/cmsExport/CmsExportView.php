<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class CmsExportView extends BaseView
{

    /* Private Properties *****************************************************/

    /**
     *  The export type.
     */
    private $type;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the user insert component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->type = $this->model->type;
    }

    /* Private Methods ********************************************************/    

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Encode the json in string for javascript
     */
    private function export_json()
    {
        echo htmlspecialchars(json_encode($this->model->json, JSON_HEX_APOS|JSON_HEX_QUOT));
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_cmsExport.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Output back button
     */
    public function output_back_button()
    {
        $backButton = new BaseStyleComponent("button", array(
            "label" => "Back to the " . ($this->type == 'section' ? 'section' : 'page'),
            "url" => $this->model->get_services()->get_router()->get_url("#back"),
            "type" => "primary",
        ));
        $backButton->output_content();
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
        $local = array(__DIR__ . "/js/export.js");
        return parent::get_js_includes($local);
    }    
}
?>
