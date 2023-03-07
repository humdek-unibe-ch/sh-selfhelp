<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the actionConfigBuilderBuilder style component.
 */
class ActionConfigBuilderView extends FormFieldView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'placeholder' (empty string).
     * The text to be displayed inside the input field.
     */
    private $placeholder;

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
        $this->placeholder = '';
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
        // $actionConfig = new BaseStyleComponent("div", array(
        //             "css" => "actionConfig_builder"
        //         ));
        // $actionConfig->output_content();
        $modal = new BaseStyleComponent('modal', array(
            'title' => "Action Config Builder",
            "css" => "actionConfig_builder_modal_holder",
            'children' => array(
                new BaseStyleComponent("div", array(
                    "css" => "actionConfig_builder"
                )),
                new BaseStyleComponent("div", array(
                    "css" => "modal-footer",
                    "children" => array(
                        new BaseStyleComponent("button", array(
                            "label" => "Save",
                            "url" => "#",
                            "type" => "secondary",
                            "css" => "saveActionConfigBuilder btn-sm"
                        )),
                    )
                ))
            ),
        ));
        $modal->output_content();
        $modalCondition = new BaseStyleComponent('modal', array(
            'title' => "Action Condition Builder",
            "css" => "action_condition_builder_modal_holder",
            'children' => array(
                new BaseStyleComponent("div", array(
                    "css" => "action_condition_builder"
                )),
                new BaseStyleComponent("div", array(
                    "css" => "modal-footer",
                    "children" => array(
                        new BaseStyleComponent("button", array(
                            "label" => "Save",
                            "url" => "#",
                            "type" => "secondary",
                            "css" => "saveActionConditionBuilder btn-sm"
                        )),
                    )
                ))
            ),
        ));
        $modalCondition->output_content();
        require __DIR__ . "/tpl_action_config_builder.php";
    }
}
?>
