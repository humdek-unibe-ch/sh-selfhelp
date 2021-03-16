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
class CmsImportView extends BaseView
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

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        $cancel_url = $this->model->get_link_url("cmsSelect");
        $action_url = $this->model->get_link_url("cmsImport",array("type"=>$this->model->type));
        require __DIR__ . "/tpl_cmsImport.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
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
        $local = array(__DIR__ . "/js/import.js");
        return parent::get_js_includes($local);
    }

    /**
     * render the app version
     */
    public function get_app_version(){
        echo rtrim(shell_exec("git describe --tags"));
    }

    /**
     * render the db version
     */
    public function get_db_version(){
        echo $this->model->get_services()->get_db()->query_db_first('SELECT version FROM version')['version'];
    }
}
?>
