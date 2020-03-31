<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the slider form style component.
 */
class SliderView extends FromFieldView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'callback' (emty string).
     * The name of the callback method. This method must be implemented in the
     * class AjaxSearch.
     */
    private $callback;

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
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->placeholder = $this->model->get_db_field("placeholder");
        $this->callback = $this->model->get_db_field("callback");
    }

    /* Private Methods ********************************************************/

    /* Protected Methods ********************************************************/

    /**
     * Render the Atocomplete field.
     */
    protected function output_form_field()
    {
        $div = new BaseStyleComponent("div", array(
            "css" => "input-autocomplete " . $this->css,
            "children" => array(
                new BaseStyleComponent("input", array(
                    "type_input" => "text",
                    "name" => $this->name_base,
                    "value" => $this->default_value,
                    "label" => $this->label,
                    "is_required" => $this-is_required,
                    "placeholder" => $this->placeholder,
                )),
                new BaseStyleComponent("input", array(
                    "type_input" => "hidden",
                    "name" => "atutocomplete_value",
                )),
                new BaseStyleComponent("div",
                    array('css' => 'search-target mb-3')),
            )
        ));
        $div->output_content();
    }

    /* Public Methods *********************************************************/
}
?>
