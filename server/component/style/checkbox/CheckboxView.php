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
class CheckboxView extends FormFieldView
{
    /* Private Properties *****************************************************/


    /**
     * DB field 'disable_autocomplete' (false).
     * Flag to enable or disable browser autocomplete.
     */
    private $section_id;


    /**
     * DB field 'data_config' ('').
     * Data configuration field for the input
     */
    private $data_config;

    /**
     * When enabled and the type is checkbox, then the input will be loaded as toggle switch
     */
    private $toggle_switch;

    /**
     * What is the value when the control is checked
     */
    private $checkbox_value;

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
        $this->data_config = $this->model->get_db_field("data_config");
        $this->toggle_switch = $this->model->get_db_field('toggle_switch', 0);
        $this->checkbox_value = $this->model->get_db_field('checkbox_value', 1);
        $this->css_group = "form-check";
        $this->css_label = "form-check-label";
        if ($this->label == "") $this->label = "&zwnj;";
    }

    /** Private Methods */


    /* Protected Methods ******************************************************/

    /**
     * Render an input form field
     */
    protected function output_form_field()
    {
        $checked = $this->value != '' ? 'checked' : null;
        if ($this->toggle_switch == 1) {
            require __DIR__ . "/tpl_switch.php";
        } else {
            require __DIR__ . "/tpl_checkbox.php";
        }
    }

    /**
     * Output mobile json
     */
    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style['value']['content'] = $style['last_value'] ? $style['last_value'] : '';
        $style['value']['default'] = $style['checkbox_value']['content'];
        if ($this->entry_data && $this->name_base != DELETE_RECORD_ID) {
            // if entry data; take the value
            $style['value']['content'] = isset($this->entry_data[$this->name_base]) ? $this->entry_data[$this->name_base] : '';
        }
        return $style;
    }
}
?>
