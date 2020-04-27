<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../moduleQualtrics/ModuleQualtricsView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleQualtricsSurveyView extends ModuleQualtricsView
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

    /**
     * Render the asset list.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    private function output($mode)
    {
        echo $mode;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/../moduleQualtrics/tpl_moduleQualtrics.php";
    }

    /**
     * call the navbar render
     */
    public function output_navbar($title)
    {
        parent::output_navbar('Surveys');
    }

    /**
     * render the page content
     */
    public function output_page_content()
    {
        echo "Surveys";
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        $button = new BaseStyleComponent("button", array(
            "label" => "Create New Survey",
            "url" => $this->model->get_link_url("moduleQualtricsSurvey", array("sid" => 0)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $button->output_content();
    }
}
?>
