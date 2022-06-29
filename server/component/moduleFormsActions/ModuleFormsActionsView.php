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
class ModuleFormsActionsView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_moduleFormsActions.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * render the page content - form actions
     */
    public function output_page_content()
    {
        require __DIR__ . "/tpl_formActions.php";   
    }

    /**
     * Render the rows for the actions
     */
    protected function output_actions_rows()
    {
        foreach ($this->model->get_formActions() as $action) {
            require __DIR__ . "/tpl_formActions_row.php";
        }
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        //show create button
            $createButton = new BaseStyleComponent("button", array(
                "label" => "Create New Action",
                "url" => $this->model->get_link_url("moduleFormsAction", array("mode" => INSERT)),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $createButton->output_content();
    }

    /**
     * Render the alert message.
     */
    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
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
        if (empty($local)) {
            $local = array(__DIR__ . "/js/formActions.js");
        }
        return parent::get_js_includes($local);
    }

}
?>
