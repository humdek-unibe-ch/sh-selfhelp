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
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->placeholder = $this->model->get_db_field("placeholder");
        $this->type_input = $this->model->get_db_field('type_input');
        $this->min = $this->model->get_db_field('min');
        $this->max = $this->model->get_db_field('max');
    }

    /* Protected Methods ********************************************************/

    /**
     * Render the textarea.
     */
    protected function output_form_field()
    {
        if ($this->entry_data) {
            // if entry data; reset the value
            $this->value = $this->model->get_entry_value($this->entry_data, $this->value);
        }
        if ($this->value === null)
            $this->value = $this->default_value;
        if ($this->locked_after_submit == 1) {
            $this->locked_after_submit = $this->value ? 1 : 0;
        }
        $css = ($this->label == "") ? $this->css : "";
        $required = ($this->is_required) ? "required" : "";
        require __DIR__ . "/tpl_textarea.php";
    }

    /* Public Methods *********************************************************/

    public function output_monaco_editor()
    {
        if ($this->type_input == "json") {
            $this->output_json();
        } else if ($this->type_input == "css") {
            require __DIR__ . "/tpl_css.php";
        }
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        if ($this->entry_data) {
            // if entry data; take the value
            $style['value']['content'] = isset($this->entry_data[$this->name_base]) ? $this->entry_data[$this->name_base] : '';
        }
        return $style;
    }

    /**
     * Output JSON field
     */
    public function output_json()
    {
        $button_label = 'Add JSON mapping';
        $button_class = "btn-primary";
        if (isset($this->value)) {
            if ($this->value) {
                $button_label = 'Edit JSON mapping';
                $button_class = "btn-warning";
            }
        }
        $field_name = '';
        $pattern = '/\[([^\]]+)\]/';
        if (preg_match($pattern, $this->name, $matches)) {
            // $matches[1] will contain the word between the first pair of square brackets
            $field_name = $matches[1];
        }
        require __DIR__ . "/tpl_json.php";
    }

    /** Output the modal form for the JSON mapper */
    public function output_json_mapper_modal()
    {
        $modal = new BaseStyleComponent('modal', array(
            'title' => 'JSON Mapper <span class="json-mapper-title-field rounded bg-light text-dark btn-sm"></span> <span class="json-mapper-error-status rounded bg-danger text-light btn-sm d-none">Error</span>',
            "css" => "json_mapper_modal_holder",
            'children' => array(
                new BaseStyleComponent("div", array(
                    "css" => "d-flex justify-content-between p-3",
                    "children" => array(
                        new BaseStyleComponent("div", array(
                            "css" => "json_tree border rounded p-2 bg-light"
                        )),
                        new BaseStyleComponent("div", array(
                            "css" => "json_mapped_items bg-light rounded border"
                        )),
                    )
                )),
                new BaseStyleComponent("div", array(
                    "css" => "modal-footer",
                    "children" => array(
                        new BaseStyleComponent("button", array(
                            "label" => "Save",
                            "url" => "#",
                            "type" => "secondary",
                            "css" => "saveJsonMapper bnt-sm"
                        )),
                    )
                ))
            ),
        ));
        $modal->output_content();
    }
}
?>
