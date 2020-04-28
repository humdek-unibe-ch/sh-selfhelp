<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphView.php";

/**
 * The view class of the graphBar style component.
 * This style component is a visual container that allows to represent bar
 * graphs.
 */
class GraphBarView extends GraphView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the column to be used to compute the pie chart.
     */
    private $name;

    /**
     * DB field 'value_types' (empty string)
     * A JSON string to define a label and a color for each distinct data value.
     */
    private $value_types;

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
        $this->set_graph_type("base");

        $this->name = $this->model->get_db_field("name");
        $this->value_types = $this->model->get_db_field("value_types");
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if(!$this->model->check_value_types($this->value_types)) {
            echo "parse error in <code>value_types</code>";
        } else {
            $labels = $this->model->extract_labels($this->value_types);
            $colors = $this->model->extract_colors($this->value_types);
            $this->traces = array(array(
                "type" => "bar",
                "data_source" => array(
                    "name" => $this->model->get_data_source(),
                    "map" => array(
                        "y" => array(
                            "name" => $this->name,
                            "options" => array(
                                "op" => "count",
                            ),
                            "maps" => array(
                                "x" => $labels,
                                "marker.color" => $colors
                            )
                        )
                    )
                )
            ));
            parent::output_content();
        }
    }
}
?>
