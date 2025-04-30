<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class for the two-factor authentication component.
 */
class TwoFactorAuthView extends StyleView
{
    /* Constructors ***********************************************************/

    // protected $controller;

    /**
     * Constructor for the TwoFactorAuthView.
     *
     * @param object $model
     *  The model instance that is used to provide the view with data.
     * @param object $controller
     *  The controller instance that is used to handle user interaction.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        // $this->controller = $controller;
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
        $local = array(__DIR__ . "/css/twoFactorAuth.css");
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
        if(empty($local)){
            $local = array(__DIR__ . "/js/twoFactorAuth.js");
        }
        return parent::get_js_includes($local);
    }
    /**
     * Render the component view.
     */
    public function output_content()
    {
        $controller = $this->controller;
        $code_remaining_time = TWO_FA_EXPIRATION * 60;
        $label_expiration_2fa = $this->model->get_db_field('label_expiration_2fa', 'Code expires in');
        $label = $this->model->get_db_field('label', 'Two-Factor Authentication');
        $text_md = $this->model->get_db_field('text_md', 'Please enter the 6-digit code sent to your email');
        require __DIR__ . "/tpl_twoFactorAuth.php";
    }

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }
}
?>
