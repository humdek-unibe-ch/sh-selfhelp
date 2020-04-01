<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the autocomplete form input style component.
 */
class AutocompleteView extends FormFieldView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'callback_class' (emty string).
     * The name of the callback class. This class must implement the
     * method specified in 'callback-method'.
     */
    private $callback_class;

    /**
     * DB field 'callback_method' (emty string).
     * The name of the callback method. This method must be implemented in the
     * class specified in 'callback-class'.
     */
    private $callback_method;

    /**
     * DB field 'placeholder' (empty string).
     * The text to be displayed inside the input field.
     */
    private $placeholder;

    /**
     * DB field 'debug' (false).
     * If set to true, debug info is displayed, if set to false no such
     * information is shown.
     */
    private $debug;

    /**
     * DB field 'name_value_field' (empty string).
     * The name of the hidden input field holding the selected ID.
     */
    private $name_value_field;

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
        $this->callback_method = $this->model->get_db_field("callback_method");
        $this->callback_class = $this->model->get_db_field("callback_class");
        $this->debug = $this->model->get_db_field("debug", false);
        $this->name_value_field = $this->model->get_db_field("name_value_field");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the debug alert
     */
    private function output_autocomplete_debug()
    {
        if($this->debug) {
            require __DIR__ . "/tpl_debug.php";
        }
    }

    /**
     * Render the autocomplete text field
     */
    private function output_autocomplete_field()
    {
        $field = new BaseStyleComponent("input", array(
            "css" => "input-autocomplete-search",
            "type_input" => "text",
            "name" => $this->name_base,
            "value" => "",
            "is_required" => $this->is_required,
            "placeholder" => $this->placeholder,
        ));
        $field->output_content();

    }

    /* Protected Methods ********************************************************/

    /**
     * Render the atocomplete style.
     */
    protected function output_form_field()
    {
        $callback = $this->callback_class . "/" . $this->callback_method;
        require __DIR__ . "/tpl_autocomplete.php";
    }
}
?>
