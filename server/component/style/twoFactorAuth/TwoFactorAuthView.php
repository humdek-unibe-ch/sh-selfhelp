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
     * Get js include files required for this view.
     *
     * @param array $local
     *  An array of include files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of js include files the view requires.
     */
    public function get_js_includes($local = array())
    {
        return array_merge(
            parent::get_js_includes($local),
            array("/component/style/twoFactorAuth/js/twoFactorAuth.js")
        );
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
