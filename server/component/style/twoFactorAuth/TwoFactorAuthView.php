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
        $code_remaining_time = 5*60;
        require __DIR__ . "/tpl_twoFactorAuth.php";
    }
}
?>
