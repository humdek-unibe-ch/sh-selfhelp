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
    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {
        $style = parent::output_content_mobile();
        $style['value']['content'] = $this->get_entry_value($entry_value, $this->model->get_db_field("value", "")); 
        $style['value']['default'] = $this->default_value;
        return $style;
    }
}
?>
