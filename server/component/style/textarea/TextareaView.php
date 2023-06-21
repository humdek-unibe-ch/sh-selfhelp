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
            $this->value = $this->model->get_entry_value($this->entry_data, $this->value); 
        }
        if($this->value === null)
            $this->value = $this->default_value;
        if($this->locked_after_submit == 1){
            $this->locked_after_submit = $this->value ? 1 : 0;
        }
        $css = ($this->label == "") ? $this->css : "";
        $required = ($this->is_required) ? "required" : "";
        require __DIR__ . "/tpl_textarea.php";
    }

    /* Public Methods *********************************************************/

    public function output_monaco_editor(){
        if ($this->type_input == "json") {
            require __DIR__ . "/tpl_json.php";
        } else if ($this->type_input == "css") {
            require __DIR__ . "/tpl_css.php";
        }
    }

    public function output_content_mobile()
    {        
        $style = parent::output_content_mobile();
        if($this->entry_data){
            // if entry data; take the value
            $style['value']['content'] = isset($this->entry_data[$this->name_base]) ? $this->entry_data[$this->name_base] : '';
        }
        return $style;
    }
}
?>
