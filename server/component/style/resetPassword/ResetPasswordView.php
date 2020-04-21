<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";
require_once __DIR__ . "/../emailFormBase/EmailFormBaseView.php";

/**
 * The view class of the ResetPasswordComponent.
 * This style is not available for selection in the CMS.
 */
class ResetPasswordView extends EmailFormBaseView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'text_md' (empty string).
     * The text to be placed in the jumbotron.
     */
    private $text;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     * @param object $controller
     *  The controller instance of the login component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->text = $this->model->get_db_field('text_md');
        $this->label = $this->model->get_db_field('label_pw_reset');
    }

    /* Private Methods ********************************************************/

    /**
     * Render the email form.
     */
    private function output_form()
    {
        parent::output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_reset.php";
    }
}
?>
