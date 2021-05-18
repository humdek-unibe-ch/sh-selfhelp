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
        $this->value = $this->model->get_db_field("value", "");
        if($this->data_config){
            $this->retrieve_data();
        }
    }

    /** Private Methods */

    /**
     * Retrieve data from database - base dont the JSON configuration
     */
    private function retrieve_data(){
        $fields = $this->model->retrieve_data($this->data_config);
        if ($fields) {
            foreach ($fields as $field_name => $field_value) {
                $this->value = str_replace($field_name, $field_value, $this->value);
            }
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Render an input form field
     */
    protected function output_form_field()
    {
        if($this->entry_data){
            // if entry data; reset the value
            $this->value = $this->model->get_db_field("value", "");
        }
        $autocomplete = '';
        if($this->disable_autocomplete) {
            $autocomplete = 'autocomplete="off"';
        }
        $css_input = "form-control";
        if($this->label == "") $css_input .= " " . $this->css;
        $checked = "";
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
            else if($this->default_value != "")
            {
                $checked = "checked";
            }
            $this->value = $this->default_value;
        }
        else if($this->value === null)
            $this->value = $this->default_value;
        if($this->entry_data){
            $orig_val = $this->model->get_db_field("value", "");
            $param = $this->get_entry_param($orig_val);
            $this->value = isset($this->entry_data[$param]) ? str_replace('$' . $param, $this->entry_data[$param], $orig_val) : $this->value; // if the param is not set, return the original
        }
        if(
        $this->type == 'date' || $this->type == 'datetime') {
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
        $curr_value = $this->model->get_form_field_value();
        // $style['value']['content'] = $curr_value  ? $curr_value : ($this->type == "checkbox" ? $curr_value : $this->default_value);
        $style['value']['content'] = $this->value;
        $style['value']['default'] = $this->default_value;
        return $style;
    }

    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {
        $style = parent::output_content_mobile();
        $param = $this->get_entry_param($this->value);
        $style['value']['content'] = isset($entry_value[$param]) ? str_replace('$' . $param, $entry_value[$param], $this->value) : $this->value; // if the param is not set, return the original
        $style['value']['default'] = $this->default_value;
        return $style;
    }

}
?>
