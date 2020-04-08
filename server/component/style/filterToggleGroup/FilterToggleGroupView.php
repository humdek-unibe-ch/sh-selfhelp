<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../filter/FilterView.php";

/**
 * The view class of the filterToggleGroup style component.
 */
class FilterToggleGroupView extends FilterView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'labels' (JSON array).
     * The label to be rendered on the filter button.
     */
    private $labels;

    /**
     * DB field 'values' (JSON array).
     * The values to be filtered with.
     */
    private $values;

    /**
     * DB field 'type' (primary).
     * The color of the button.
     */
    private $type;

    /**
     * DB field 'is_vertical' (primary).
     * If true the button group is rendered as a vertical stack, otherwise as a
     * horizontal list.
     */
    private $is_vertical;

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
        $this->values = $this->model->get_db_field("values", array());
        $this->labels = $this->model->get_db_field("labels", array());
        $this->is_vertical = $this->model->get_db_field("is_vertical", false);
        $values = array();
        foreach($this->values as $value) {
            array_push($values, $this->name . "='" . $value . "'");
        }
        $this->values = $values;
        $this->set_filter_value($this->values);
        $this->set_filter_type("toggle-group");
    }

    /* Private  Methods *******************************************************/

    /**
     * Render the buttons inside the button group
     */
    private function output_buttons() {
        foreach($this->labels as $idx => $label) {
            $is_active = isset($_SESSION['data_filter'][$this->data_source][$this->name]) &&
                in_array($this->values[$idx],
                    $_SESSION['data_filter'][$this->data_source][$this->name]);
            require __DIR__ . "/tpl_button.php";
        }
    }

    /**
     *
     */
    protected function output_filter() {
        if(count($this->labels) !== count($this->values)) {
            echo "item count of field <code>labels</code> must match with item count of field <code>values</code>";
            return;
        }
        require __DIR__ . "/tpl_filterToggleGroup.php";
    }


    /* Public Methods *********************************************************/
}
?>
