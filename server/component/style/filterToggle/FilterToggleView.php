<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the filterToggle style component.
 */
class FilterToggleView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'data_source' (empty string).
     * The data source is either a static table which was uploaded as asset or
     * dynamic data collected from user input.
     */
    private $data_source;

    /**
     * DB field 'lable' (empty string).
     * The label to be rendered on the filter button.
     */
    private $label;

    /**
     * DB field 'name' (empty string).
     * The name of a filter links the filter to the data source column or field
     * name.
     */
    private $name;

    /**
     * DB field 'value' (empty string).
     * The value to be filtered against.
     */
    private $value;


    /**
     * DB field 'type' (primary).
     * The color of the button.
     */
    private $type;

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
        $this->type = $this->model->get_db_field("type", "primary");
        $this->name = $this->model->get_db_field("name");
        $this->value = $this->name . "='" . $this->model->get_db_field("value") . "'";
        $this->label = $this->model->get_db_field("label");
        $this->data_source = $this->model->get_db_field("data-source");
    }

    /* Private  Methods *******************************************************/

    /**
     * Render the JSON object, holding the filter data.
     */
    private function output_filter_data() {
        echo json_encode(array(
            "value" => $this->value,
            "name" => $this->name,
            "data_source" => $this->data_source
        ));
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $is_active = isset($_SESSION['data_filter'][$this->data_source][$this->name]) &&
            $_SESSION['data_filter'][$this->data_source][$this->name] === $this->value;
        if($this->name === "") {
            echo "field <code>name</code> cannot be empty";
            return;
        }
        if($this->label === "") {
            $this->label = $this->name;
        }
        require __DIR__ . "/tpl_filter_toggle.php";
    }
}
?>
