<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the filter style component.
 */
abstract class FilterView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     *
     */
    private $filter_value = null;

    /**
     *
     */
    private $filter_type = null;

    /* Protected Properties ***************************************************/

    /**
     * DB field 'data_source' (empty string).
     * The data source is either a static table which was uploaded as asset or
     * dynamic data collected from user input.
     */
    protected $data_source;

    /**
     * DB field 'name' (empty string).
     * The name of a filter links the filter to the data source column or field
     * name.
     */
    protected $name;

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
        $this->name = $this->model->get_db_field("name");
        $this->data_source = $this->model->get_db_field("data-source");
    }

    /* Private  Methods *******************************************************/

    /**
     * Render the JSON object, holding the filter data.
     */
    private function output_filter_data() {
        echo json_encode(array(
            "value" => $this->filter_value,
            "name" => $this->name,
            "data_source" => $this->data_source
        ));
    }

    /**
     *
     */
    abstract protected function output_filter();

    /**
     *
     */
    protected function set_filter_value($value) {
        $this->filter_value = $value;
    }

    /**
     *
     */
    protected function set_filter_type($value) {
        $this->filter_type = $value;
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->filter_value === null) {
            echo "filter value is not set";
            return;
        }
        if($this->filter_type === null) {
            echo "filter type is not set";
            return;
        }
        if($this->data_source === "") {
            echo "field <code>data_source</code> cannot be empty";
            return;
        }
        if($this->name === "") {
            echo "field <code>name</code> cannot be empty";
            return;
        }
        require __DIR__ . "/tpl_filter.php";
    }
}
?>
