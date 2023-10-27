<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the radio style component.
 */
class RadioView extends FormFieldView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'items' (empty array).
     * A list of options. where each element has the following keys
     *  'value':    The id of the option item.
     *  'text':     The content of the option item.
     */
    private $items;


    /**
     * DB field 'is_inline' (true).
     * If set to true the radio buttons are displayed in a row. If set to false
     * they are displayed in a column.
     */
    private $is_inline;

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
        $items = $this->model->get_db_field("items", array());
        $this->items = $this->model->json_style_parse($items);
        $this->is_inline = $this->model->get_db_field("is_inline", true);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the radio items.
     */
    private function output_radio_items()
    {
        $inline = ($this->is_inline) ? "form-check-inline" : "";
        foreach($this->items as $field)
        {
            if(!isset($field['value']) || !isset($field['text'])) continue;
            $value = htmlspecialchars($field['value']);
            $text = $field['text'];
            $checked = ($value == $this->value) ? "checked" : "";
            $required = ($this->is_required) ? "required" : "";
            require __DIR__ . "/tpl_radio.php";
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Render an input form field
     */
    protected function output_form_field()
    {
        if(!is_array($this->items)) return;
        if($this->value === null)
            $this->value = $this->default_value;
        $css = ($this->label == "") ? $this->css : "";        
        require __DIR__ . "/tpl_radio_group.php";
    }
}
?>
