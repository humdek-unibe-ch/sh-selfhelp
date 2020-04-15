<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../filter/FilterView.php";

/**
 * The view class of the filterToggle style component.
 */
class FilterToggleView extends FilterView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'lable' (empty string).
     * The label to be rendered on the filter button.
     */
    private $label;

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
        $this->value = $this->name . "='" . $this->model->get_db_field("value") . "'";
        $this->label = $this->model->get_db_field("label");
        $this->set_filter_value(array($this->value));
        $this->set_filter_type("toggle");
    }

    /* Private  Methods *******************************************************/

    protected function output_filter() {
        $is_active = isset($_SESSION['data_filter'][$this->data_source][$this->name][0]) &&
            $_SESSION['data_filter'][$this->data_source][$this->name][0] === $this->value;
        if($this->label === "") {
            $this->label = $this->name;
        }
        require __DIR__ . "/tpl_filter_toggle.php";
    }

    /* Public Methods *********************************************************/
}
?>
