<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the jobConfigBuilder style component.
 */
class JobConfigView extends FormFieldView
{
    /* Private Properties *****************************************************/


    /**
     * The type of the text area
     */
    private $type_input;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->type_input = 'textarea';
    }

    /* Protected Methods ********************************************************/

    /**
     * Render the textarea.
     */
    protected function output_form_field()
    {
        if ($this->value === null)
            $this->value = $this->default_value;
        require __DIR__ . "/tpl_textarea.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the builder buttons and modal forms if they are needed
     */
    public function output_builder()
    {
        $jobConfig = new BaseStyleComponent("div", array(
            "css" => "jobConfig"
        ));
        $jobConfig->output_content();
    }
}
?>
