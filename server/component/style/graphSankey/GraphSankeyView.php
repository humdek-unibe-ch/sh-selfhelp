<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphView.php";

/**
 * The view class of the graphSankey style component.
 * This style component is a visual container that allows to represent Sankey
 * diagrams.
 */
class GraphSankeyView extends GraphView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'raw' (string).
     * A JSON string of precomputed sanky data which can be fed to plotly.
     * The precomputation is done in the model by using the CMS update callback.
     */
    private $raw_data;

    /**
     * DB field 'has_type_labels' (boolean).
     * If set to true the node labels will be rendered, if set to false the
     * node labels will be hidden.
     */
    private $has_node_labels;

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
        $this->set_graph_type("sankey");

        $this->has_node_labels = $this->model->get_db_field("has_type_labels",
            false);
        $this->raw_data = $this->model->get_db_field("raw");

        $this->traces = array(array(
            "type" => "sankey",
            "arrangement" => "snap",
            "data_source" => array(
                "name" => $this->model->get_data_source(),
                "single_user" => $this->model->get_single_user()
            )
        ));
        if($this->model->get_has_col_labels()) {
            if($this->layout === "") {
                $this->layout = array();
            }
            $this->layout["annotations"] =
                $this->model->prepare_sankey_annotations();
        }
    }

    /* Protected Methods ******************************************************/

    protected function output_graph_opts() {
        echo json_encode(array(
            "cols" => $this->model->get_data_cols(),
            "types" => $this->model->get_data_types(),
            "link_color" => $this->model->get_link_color(),
            "link_alpha" => $this->model->get_link_alpha(),
            "link_hovertemplate" => $this->model->get_link_hovertemplate(),
            "node_hovertemplate" => $this->model->get_node_hovertemplate(),
            "has_node_labels" => $this->has_node_labels ? true : false,
            "is_grouped" => $this->model->get_is_grouped(),
            "min" => $this->model->get_min(),
            "pre_computation" => json_decode($this->raw_data)
        ));
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if(!$this->model->check_cols()) {
            echo "parse error in <code>form_field_names</code>";
        } else if(!$this->model->check_types()) {
            echo "parse error in <code>value_types</code>";
        } else {
            parent::output_content();
        }
    }
}
?>
