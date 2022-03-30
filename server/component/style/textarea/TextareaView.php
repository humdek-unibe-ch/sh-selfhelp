<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the textarea style component.
 */
class TextareaView extends FormFieldView
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
        $this->placeholder = $this->model->get_db_field("placeholder");
        $this->type_input = $this->model->get_db_field('type_input');        
    }

    /* Protected Methods ********************************************************/

    /**
     * Render the textarea.
     */
    protected function output_form_field()
    {
        if($this->entry_data){
            // if entry data; reset the value
            $this->value = $this->get_entry_value($this->entry_data, $this->model->get_db_field("value", "")); 
        }
        if($this->value === null)
            $this->value = $this->default_value;
        $css = ($this->label == "") ? $this->css : "";
        $required = ($this->is_required) ? "required" : "";
        require __DIR__ . "/tpl_textarea.php";
    }

    /* Public Methods *********************************************************/

    public function output_json(){        
       if($this->type_input == "json"){
            require __DIR__ . "/tpl_json.php";
       }
    }

    /**
     * Render the builder buttons and modal forms if they are needed
     */
    public function output_builder()
    {
        if ($this->name == "fields[data_config][1][1][content]") {
            $modal = new BaseStyleComponent('modal', array(
                    'title' => "Data Config Builder",
                    "css" => "data_config_builder_modal_holder",
                    'children' => array(
                        new BaseStyleComponent("div", array(
                            "css" => "data_config_builder"
                        )),
                        new BaseStyleComponent("div", array(
                            "css" => "modal-footer",
                            "children" => array(
                                new BaseStyleComponent("button", array(
                                    "label" => "Save",
                                    "url" => "#",
                                    "type" => "primary",
                                    "css" => "saveDataConfig"
                                )),
                            )
                        ))
                    ),
                ));
            $modal->output_content();
            require __DIR__ . "/tpl_data_config_builder.php";
        } else if ($this->name == "fields[condition][1][1][content]") {
            $modal = new BaseStyleComponent('modal', array(
                    'title' => "Condition Builder",
                    "css" => "condition_builder_modal_holder",
                    'children' => array(
                        new BaseStyleComponent("div", array(
                            "css" => "condition_builder"
                        )),
                        new BaseStyleComponent("div", array(
                            "css" => "modal-footer",
                            "children" => array(
                                new BaseStyleComponent("button", array(
                                    "label" => "Save",
                                    "url" => "#",
                                    "type" => "primary",
                                    "css" => "saveConditionBuilder"
                                )),
                            )
                        ))
                    ),
                ));
            $modal->output_content();
            require __DIR__ . "/tpl_condition_builder.php";
        } else if(strpos($this->css, 'qualtricsSurveyConfig') !== false){
            $modal = new BaseStyleComponent('modal', array(
                    'title' => "Qualtrics Survey Config Builder",
                    "css" => "qualtricsSurveyConfig_builder_modal_holder",
                    'children' => array(
                        new BaseStyleComponent("div", array(
                            "css" => "qualtricsSurveyConfig_builder"
                        )),
                        new BaseStyleComponent("div", array(
                            "css" => "modal-footer",
                            "children" => array(
                                new BaseStyleComponent("button", array(
                                    "label" => "Save",
                                    "url" => "#",
                                    "type" => "primary",
                                "css" => "savequaltricsSurveyConfigBuilder"
                            )),
                        )
                    ))
                ),
            ));
            $modal->output_content();
            require __DIR__ . "/tpl_qualtricsSurveyConfig_builder.php";
        } else if (strpos($this->css, 'actionConfig') !== false) {
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
                                "type" => "primary",
                                "css" => "saveActionConfigBuilder"
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
                                    "type" => "primary",
                                    "css" => "saveActionConditionBuilder"
                                )),
                            )
                        ))
                    ),
                ));
            $modalCondition->output_content();
            require __DIR__ . "/tpl_actionConfig_builder.php";
        }
    }
}
?>
