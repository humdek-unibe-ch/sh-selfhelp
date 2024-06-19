<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the input item style component.
 */
class InputView extends FormFieldView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'type_input' ('value').
     * The type of the input field.
     */
    private $type;

    /**
     * DB field 'placeholder' (empty string).
     * The text to be displayed inside the input field.
     */
    private $placeholder;

    /**
     * DB field 'disable_autocomplete' (false).
     * Flag to enable or disable browser autocomplete.
     */
    private $disable_autocomplete;

    /**
     * DB field 'disable_autocomplete' (false).
     * Flag to enable or disable browser autocomplete.
     */
    private $section_id;

    /**
     * DB field 'format' ('').
     * Format field for the input
     */
    private $format;   

    /**
     * DB field 'data_config' ('').
     * Data configuration field for the input
     */
    private $data_config; 

    /**
     * This number will determine the minimum character size required for your input. The input will need to have at least this many characters to be valid
     */
    private $min;

    /**
     * This number will determine the maximum character size allowed for your input. The input should not exceed this character limit to be valid.
     */
    private $max;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     * @param int $id
     *  The section id of this navigation component.
     */
    public function __construct($model, $id = null)
    {
        parent::__construct($model);
        $this->section_id = $id;
        $this->type = $this->model->get_db_field("type_input", "text");
        $this->placeholder = $this->model->get_db_field("placeholder");
        $this->format = $this->model->get_db_field("format", "");
        $this->disable_autocomplete = $this->model->get_db_field(
            "disable_autocomplete", false);
        if($this->type == "checkbox")
        {
            $this->css_group = "form-check";
            $this->css_label = "form-check-label";
            if($this->label == "") $this->label = "&zwnj;";
        }
        $this->data_config = $this->model->get_db_field("data_config");
        $this->min = $this->model->get_db_field('min');
        $this->max = $this->model->get_db_field('max');
    }

    /** Private Methods */


    /* Protected Methods ******************************************************/

    /**
     * Render an input form field
     */
    protected function output_form_field()
    {
        $autocomplete = '';
        if($this->disable_autocomplete) {
            $autocomplete = 'autocomplete="off"';
        }
        $css_input = "form-control";
        if($this->label == "") $css_input .= " " . $this->css;
        $checked = null;
        if($this->type == "checkbox")
        {
            $css_input = "form-check-input position-static float-left";
            if($this->is_user_input())
            {                
                if($this->default_value == "") return;
                if(($this->value !== null && $this->value !== "")
                    || ($this->value === null && $this->placeholder != ""))
                    $checked = "checked";
            }
            else if($this->default_value != "" && $this->default_value != 0)
            {
                $checked = "checked";                
            }
            if ($this->model->get_db_field("checkbox_value")) {
                $this->default_value = $this->model->get_db_field("checkbox_value");
            }
            $this->value = $this->default_value;
        }
        else if($this->value === null)
            $this->value = $this->default_value;
        if(
            $this->type == 'date' || $this->type == 'datetime'
        ) {
            require __DIR__ . "/tpl_input_date.php";
        } else if ($this->type == 'time') {
            require __DIR__ . "/tpl_input_time.php";
        } else {            
            require __DIR__ . "/tpl_input.php";
        }
    }

    public function output_content_mobile()
    {        
        $style = parent::output_content_mobile();
        $style['value']['content'] = $this->value;
        $style['value']['default'] = $this->default_value;
        if(!$style['value']['content'] && $style['value']['default']){
            // if there is no value, assigned the default value
            $style['value']['content'] = $style['value']['default'];
        }
        if ($this->entry_data && $this->name_base != DELETE_RECORD_ID) {
            // if entry data; take the value
            $style['value']['content'] = isset($this->entry_data[$this->name_base]) ? $this->entry_data[$this->name_base] : '';
        }
        if($this->type == "checkbox") {
            $style['value']['content'] = 0;
            if ($this->default_value == "") {
            } else if (($this->value !== null && $this->value !== "")) {
                $style['value']['content'] = 1;
            }
        }
        return $style;
    }

}
?>
