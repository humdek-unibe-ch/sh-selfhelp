<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the select form style component.
 * See SelectComponent for more details.
 */
class SelectView extends FormFieldView
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
     * DB field 'is_multiple' (false).
     * If set to true the selection form is a multiple select. If set to false
     * the selection form is a dropdown, single select.
     */
    private $is_multiple;

    private $live_search;

    private $max;

    private $disabled;

    private $image_selector;

    /**
     * DB field 'allow_clear' (false).
     * If set to true the selected value can be cleared
     */
    private $allow_clear;

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
        $this->items = $this->model->get_db_field("items", array());
        $this->is_multiple = $this->model->get_db_field("is_multiple", false);
        $this->live_search = $this->model->get_db_field("live_search", false);
        $this->max = $this->model->get_db_field("max", 5);
        $this->disabled = $this->model->get_db_field("disabled", 0);
        $this->allow_clear = $this->model->get_db_field("allow_clear", 0);
        $this->image_selector = $this->model->get_db_field("image_selector", 0);     
    }

    /* Private Methods ********************************************************/

    /**
     * Prepare the img paths if the select loads images
     */
    private function prepare_imgs_paths()
    {
        foreach ($this->items as $key => $item) {
            if (!filter_var($item['text'], FILTER_VALIDATE_URL)) {
                $this->items[$key]['text'] = ASSET_PATH . '/' . $item['text'];
            }
        }
    }

    /**
     * Render a select option.
     */
    private function output_fields()
    {
        if($this->value == "" && !$this->is_multiple && !$this->required)
        {
            $empty = $this->model->get_db_field("alt");
            require __DIR__ . "/tpl_select_empty.php";
        }
        if(!is_array($this->items)) return;
        foreach($this->items as $field)
        {
            if($this->is_multiple){
                //set selected values for multi select
                if (is_array($this->value)) {
                    foreach ($this->value as $val) {
                        $selected = (htmlspecialchars($field['value']) == $val) ? 'selected="selected"' : "";
                        if (htmlspecialchars($field['value']) == $val) {
                            break;
                        }
                    }
                } else {
                    $selected = "";
                }
            }
            if(!isset($field['value']) || !isset($field['text'])) continue;
            $value = htmlspecialchars($field['value']);
            $text = htmlspecialchars($field['text']);
            if(!$this->is_multiple){
                $selected = ($value == $this->value) ? 'selected="selected"' : "";
            }
            require __DIR__ . "/tpl_select_item.php";
        }
    }

    /* Protected Methods ********************************************************/

    /**
     * Render the select form.
     */
    protected function output_form_field()
    {
        if($this->value === null)
            $this->value = $this->default_value;
        
        $css = ($this->label == "") ? $this->css : "";
        $multiple = ($this->is_multiple) ? "multiple" : "";
        $required = ($this->is_required) ? "required" : "";
        if ($this->is_multiple && $this->value && !is_array($this->value)) {
            $this->value = json_decode(html_entity_decode($this->value)); //if not array yet and if the select is multiple convert the json to an array
        }
        if ($this->image_selector) {
            $this->prepare_imgs_paths();
            require __DIR__ . "/tpl_select_image.php";
        } else {
            require __DIR__ . "/tpl_select.php";
        }
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        if ($this->image_selector) {
            foreach ($this->items as $key => $item) {
                if (!filter_var($item['text'], FILTER_VALIDATE_URL)) {
                    $this->items[$key]['text'] = ASSET_FOLDER . '/' . $item['text'];
                }
            }            
        }
        $style['items'] = $this->items;
        if ($this->is_multiple) {
            $style['last_value'] = $style['last_value'] ? json_decode(html_entity_decode($style['last_value'])) : $style['last_value']; //if not array yet and if the select is multiple convert the json to an array
            $style['value']['content'] = json_decode(html_entity_decode($style['value']['content']));
        }        
        return $style;
    }

    /**
     * Return field name 
     * @return string
     * The form name
     */
    public function get_field_name()
    {
        return $this->name . ($this->is_multiple ? "[]" : ""); // if it is multiple select make the form_name an array 
    }
}
?>
