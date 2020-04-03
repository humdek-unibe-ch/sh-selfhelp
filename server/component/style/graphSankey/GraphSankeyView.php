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
     * DB field 'form_field_names' (json).
     * Defines the which form fields or data colomns to use.
     */
    private $data_cols;

    /**
     * DB field 'value_types' (json).
     * Defines the types of entered user data to be used for drwaing a Sankey
     * diagram.
     */
    private $data_types;

    /**
     * DB field 'link_color' (text).
     * Define the color of the links.
     */
    private $link_color;

    /**
     * DB field 'link_alpha' (text).
     * Define the alpha value of the color of the links.
     */
    private $link_alpha;

    /**
     * DB field 'min' (number).
     * The minimal required link sum for a link to be drawn.
     */
    private $min;

    /**
     * DB field 'has_type_labels' (boolean).
     * If set to true the node labels will be rendered, if set to false the
     * node labels will be hidden.
     */
    private $has_node_labels;

    /**
     * DB field 'has_field_labels' (boolean).
     * If set to true the column labels will be rendered, if set to false the
     * column labels will be hidden.
     */
    private $has_col_labels;

    /**
     * DB field 'is_grouped' (boolean).
     * If set to true the nodes of the Sankey diagram will be positioned
     * according to the data provided in GraphSankeyModel::data_cols and
     * GraphSankeyModel::data_types.
     */
    private $is_grouped;

    /**
     * The hovertemplate of links.
     */
    private $link_hovertemplate;

    /**
     * The hovertemplate of nodes.
     */
    private $node_hovertemplate;

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
        $this->raw_data = $this->model->get_db_field("raw");

        $this->data_types = $this->model->get_db_field("value_types", array());
        $this->data_cols = $this->model->get_db_field("form_field_names", array());
        $this->link_color = $this->model->get_db_field("link_color");
        $this->link_alpha = $this->model->get_db_field("link_alpha", 0.5);
        $this->link_hovertemplate = $this->model->get_db_field(
            "link_hovertemplate", "%{source.label} &rarr; %{target.label}");
        $this->node_hovertemplate = $this->model->get_db_field(
            "node_hovertemplate", "%{label}");
        $this->min = $this->model->get_db_field("min", 1);
        $this->has_node_labels = $this->model->get_db_field("has_type_labels",
            false);
        $this->has_col_labels = $this->model->get_db_field("has_field_labels",
            true);
        $this->is_grouped = $this->model->get_db_field("is_grouped",
            true);
        $this->traces = array(array(
            "type" => "sankey",
            "arrangement" => "snap",
            "data_source" => array(
                "name" => $this->model->get_data_source(),
                "single_user" => $this->model->get_single_user()
            )
        ));
        if($this->has_col_labels) {
            if($this->layout === "") {
                $this->layout = array();
            }
            $this->layout["annotations"] =
                $this->model->prepare_sankey_annotations($this->data_cols);
        }
    }

    /* Protected Methods ******************************************************/

    protected function output_graph_opts() {
        echo json_encode(array(
            "cols" => $this->data_cols,
            "types" => $this->data_types,
            "link_color" => $this->link_color,
            "link_alpha" => $this->link_alpha,
            "link_hovertemplate" => $this->link_hovertemplate,
            "node_hovertemplate" => $this->node_hovertemplate,
            "has_node_labels" => $this->has_node_labels ? true : false,
            "is_grouped" => $this->is_grouped ? true : false,
            "min" => $this->min
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
