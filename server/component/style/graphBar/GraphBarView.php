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
     * DB field 'labels' (empty string)
     * A JSON string to define provide a lable for each distinct data value.
     */
    private $labels;

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
        $this->labels = $this->model->get_db_field("labels");

        $this->traces = array(array(
            "type" => "bar",
            "data_source" => array(
                "name" => $this->model->get_data_source(),
                "map" => array(
                    "y" => array(
                        "name" => $this->name,
                        "op" => "count",
                        "labels" => array(
                            "key" => "x",
                            "map" => $this->labels
                        )
                    )
                )
            )
        ));
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if(!$this->model->check_label_map($this->labels)) {
            echo "parse error in <code>label_map</code>";
        } else {
            parent::output_content();
        }
    }
}
?>
