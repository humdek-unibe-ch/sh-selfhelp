<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphModel.php";

/**
 * This class is used to prepare all data related to the garphSankey style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class GraphSankeyModel extends GraphModel
{
    /* Private Properties *****************************************************/

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
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->data_types = $this->get_db_field("value_types", array());
        $this->data_cols = $this->get_db_field("form_field_names", array());
        $this->link_color = $this->get_db_field("link_color");
        $this->link_alpha = $this->get_db_field("link_alpha", 0.5);
        $this->link_hovertemplate = $this->get_db_field(
            "link_hovertemplate", "%{source.label} &rarr; %{target.label}");
        $this->node_hovertemplate = $this->get_db_field(
            "node_hovertemplate", "%{label}");
        $this->min = $this->get_db_field("min", 1);
        $this->has_col_labels = $this->get_db_field("has_field_labels",
            true);
        $this->is_grouped = $this->get_db_field("is_grouped",
            true);
    }

    /* Private Methods ********************************************************/

    /**
     * Compute the normalised position of a node.
     *
     * @param int $size
     *  The total element count
     * @param int $idx
     *  The index of the elemnt to compute the normalized position.
     * @retval int
     *  The normalized position
     */
    private function compute_position($size, $idx) {
        $delta_fix = 0.00001;
        $pos = $idx/($size - 1) + $delta_fix;
        if($pos > 1) $pos = 1 - $delta_fix;
        return $pos;
    }

    /**
     * Compute the sum of a Sankey link.
     *
     * @param array $body
     *  A list of rwos containing the user data
     * @param array $source
     *  The source node
     * @param array $target
     *  The target node
     * @retval int
     *  The computed link sum
     */
    private function compute_sankey_link_sum($body, $source, $target)
    {
        $col_idx_src = $source['col']['head_idx'];
        $col_idx_tgt = $target['col']['head_idx'];
        $sum = 0;
        foreach($body as $row) {
            if(($row[$col_idx_src] == $source['type']['key'])
                    && ($row[$col_idx_tgt] == $target['type']['key'])) {
                $sum++;
            }
        }
        return $sum;
    }

    /**
     * Prepare the data of the column part of a node.
     *
     * @param array $cols
     *  The data provided with the DB field `form_field_names`
     * @param number $idx
     *  The column index
     * @retval array
     *  The column node array with the following keys:
     *  - `head_idx`: the index in the head array
     *  - `idx`: the index in the cols array
     *  - `key`: the field identifier
     *  - `label`: a human-readable text of the field identifier
     */
    private function get_col_node($cols, $idx) {
        return array(
            "head_idx" => $cols[$idx]['key'],
            "idx" => $idx,
            "key" => $cols[$idx]['key'],
            "label" => $cols[$idx]['label'],
        );
    }

    /**
     * Prepare the data of the type part of a node.
     *
     * @param array $type
     *  One item from the list provided by the DB field `value_types`.
     * @param number $idx
     *  The index of the item
     * @retval array
     *  The type node array with the following keys:
     *  - `idx`: the index in the types array
     *  - `key`: the field identifier
     *  - `label`: a human-readable text of the field identifier
     *  - `color`: the color of a node of the given type
     */
    private function get_type_node($type, $idx) {
        return array(
            "idx" => $idx,
            "key" => $type['key'],
            "label" => $type['label'],
            "color" => isset($type['color']) ? $type['color'] : "",
        );
    }

    /**
     * Prepare the Sankey transitions. The transitions are used to generate
     * the links. Here, two consecutive column pairs are used.
     *
     * @param array $cols
     *  The data provided with the DB field `form_field_names`
     * @retval array
     *  The transitions array with the following keys:
     *  - `source`: the source node
     *  - `target`: the target node
     */
    private function prepare_sankey_transitions($cols) {
        $transitions = array();
        for($i = 1; $i < count($cols); $i++) {
            array_push($transitions, array(
                "source" => $this->get_col_node($cols, $i - 1),
                "target" => $this->get_col_node($cols, $i)
            ));
        }
        return $transitions;
    }

    /**
     * Debug function to lead data samples. This function is obsolete for the
     * release version.
     *
     * @retval array
     *  An associative array with the following keys:
     *   - `head`: An array of strings describing the csv head.
     *   - `body`: An array of rows where each row is an array of values.
     */
    private function read_sample_csv()
    {
        $fh = fopen(__DIR__ . '/sankey_sample.csv', 'r');
        $body = array();
        $head = fgetcsv( $fh );
        while(($data = fgetcsv( $fh )) !== false) {
            $row = array();
            foreach($head as $idx => $col) {
                $row[$col] = $data[$idx];
            }
            array_push($body, $row);
        }
        return $body;
    }

    /* Public Methods *********************************************************/

    /**
     * Checks wether the cols array provided through the CMS contains all
     * required fields.
     *
     * @retval boolean
     *  True on success, false on failure.
     */
    public function check_cols() {
        if(!is_array($this->data_cols) || count($this->data_cols) === 0)
            return false;
        foreach($this->data_cols as $idx => $item)
        {
            if(!isset($item["key"]))
                return false;
            if(!isset($item["label"]))
                $this->data_cols[$idx]["label"] = $item["key"];
        }
            return true;
    }

    /**
     * Checks wether the types array provided through the CMS contains all
     * required fields.
     *
     * @retval boolean
     *  True on success, false on failure.
     */
    public function check_types() {
        return $this->check_value_types($this->data_types);
    }

    /**
     * Getter for GraphSankeyModel::data_cols
     */
    public function get_data_cols()
    {
        return $this->data_cols;
    }

    /**
     *
     * Getter for GraphSankeyModel::data_types
     */
    public function get_data_types()
    {
        return $this->data_types;
    }

    /**
     *
     * Getter for GraphSankeyModel::is_grouped
     */
    public function get_is_grouped()
    {
        return $this->is_grouped ? true : false;
    }

    /**
     *
     * Getter for GraphSankeyModel::has_col_labels
     */
    public function get_has_col_labels()
    {
        return $this->has_col_labels;
    }

    /**
     *
     * Getter for GraphSankeyModel::link_alpha
     */
    public function get_link_alpha()
    {
        return $this->link_alpha;
    }

    /**
     *
     * Getter for GraphSankeyModel::link_color
     */
    public function get_link_color()
    {
        return $this->link_color;
    }

    /**
     *
     * Getter for GraphSankeyModel::link_hovertemplate
     */
    public function get_link_hovertemplate()
    {
        return $this->link_hovertemplate;
    }

    /**
     *
     * Getter for GraphSankeyModel::min
     */
    public function get_min()
    {
        return $this->min;
    }

    /**
     *
     * Getter for GraphSankeyModel::node_hovertemplate
     */
    public function get_node_hovertemplate()
    {
        return $this->node_hovertemplate;
    }

    /**
     * Prepare the annotations which will form the column labels.
     *
     * @retval array
     *  The annotation array with all the necessary keys to be passed to the
     *  plotly function.
     */
    public function prepare_sankey_annotations() {
        if(!$this->has_col_labels || !$this->is_grouped)
            return array();
        $annotations = array();
        foreach($this->data_cols as $idx => $col) {
            array_push($annotations, array(
                "text" => $col['label'],
                "x" => $this->compute_position(count($this->data_cols), $idx),
                "y" => 1,
                "yshift" => 5,
                "font" => array( "size" => 15 ),
                "showarrow" => false,
                "xanchor" => "center",
                "yanchor" => "bottom",
            ));
        }
        return $annotations;
    }

    /**
     * Prepare the sankey data such that it can easily be displayed with
     * plotly.js.
     *
     * @param array $body
     *  An array of rows where each row is an array of values.
     * @param array $cols
     *  The data provided with the DB field `form_field_names`
     * @param array $types
     *  The data provided with the DB field `value_types`.
     * @retval array
     *  A raw data array with all the necessary keys to be passed to plotly.js
     */
    public function prepare_sankey_data($body, $cols, $types)
    {
        $transitions = $this->prepare_sankey_transitions($cols);

        // prepare links based on transitions and a node reference table which
        // will be used to build the final node list
        $nodes_ref = array();
        $node_idx = 0;
        $links = array(
            "source" => array(),
            "target" => array(),
            "value" => array(),
            "color" => $this->link_color === "" ? null : array(),
        );
        foreach($transitions as $transition) {
            foreach($types as $idx_src => $type_src) {
                foreach($types as $idx_tgt => $type_tgt) {
                    $source = array(
                        "col" => $transition["source"],
                        "type" => $this->get_type_node($type_src, $idx_src)
                    );
                    $target = array(
                        "col" => $transition["target"],
                        "type" => $this->get_type_node($type_tgt, $idx_tgt)
                    );
                    $source_key = $source['col']['key'].'-'.$source['type']['key'];
                    $target_key = $target['col']['key'].'-'.$target['type']['key'];
                    $sum = $this->compute_sankey_link_sum($body,
                        $source, $target);
                    if($sum >= $this->min) {
                        array_push($links['value'], $sum);

                        if(!array_key_exists($source_key, $nodes_ref)) {
                            $source['idx'] = $node_idx;
                            $nodes_ref[$source_key] = $source;
                            $node_idx++;
                        }
                        $source_idx = $nodes_ref[$source_key]['idx'];
                        array_push($links['source'], $source_idx);

                        if(!array_key_exists($target_key, $nodes_ref)) {
                            $target['idx'] = $node_idx;
                            $nodes_ref[$target_key] = $target;
                            $node_idx++;
                        }
                        $target_idx = $nodes_ref[$target_key]['idx'];
                        array_push($links['target'], $target_idx);
                        if($this->link_color === "source"
                                && $source['type']['color'] !== "") {
                            $color = $source['type']['color'];
                        } else if($this->link_color === "target"
                                && $target['type']['color'] !== "") {
                            $color = $target['type']['color'];
                        } else {
                            $color = $this->link_color;
                        }
                        if($color !== "") {
                            array_push($links['color'], $color);
                        }
                    }
                }
            }
        }
        for($i = 0; $i < count($links['value']); $i++) {
            $alpha = (int)(255 * $this->link_alpha);
            if($alpha < 0) $alpha = 0;
            if($alpha > 255) $alpha = 255;
            $hex = dechex($alpha);
            if(strlen($hex) === 1) $hex = "0". $hex;
            if(isset($links['color'][$i])) {
                $links['color'][$i] .= $hex;
            }
        }
        $links['hovertemplate'] = $this->link_hovertemplate;

        // Count the number of nodes per column which will be displayed.
        // This is used to compute the node position on the y axis.
        $col_counts = array();
        foreach($cols as $idx => $col) {
            $col_counts[$col['key']] = array();
        }
        foreach($nodes_ref as $key => $node_ref) {
            array_push($col_counts[$node_ref['col']['key']],
                $node_ref['type']['idx']);
        }
        foreach($col_counts as $idx => $col_count) {
            sort($col_counts[$idx]);
        }

        // pepare nodes
        $nodes = array(
            "label" => array(),
            "color" => array(),
            "x" => array(),
            "y" => array()
        );
        foreach($nodes_ref as $key => $node_ref) {
            $nodes["label"][$node_ref['idx']] = $node_ref['type']['label'];
            $nodes["color"][$node_ref['idx']] = $node_ref['type']['color'];
            if($this->is_grouped) {
                $nodes["x"][$node_ref['idx']] = $this->compute_position(
                    count($cols), $node_ref['col']['idx']);
                $y_idx = array_search($node_ref['type']['idx'],
                    $col_counts[$node_ref['col']['key']]);
                $nodes["y"][$node_ref['idx']] = $this->compute_position(
                    count($col_counts[$node_ref['col']['key']]), $y_idx);
            }
        }
        $nodes["hovertemplate"] = $this->node_hovertemplate;
        return array(
            "type" => "sankey",
            "arrangement" => "snap",
            "link" => $links,
            "node" => $nodes,
        );
    }

    /**
     * The callback function to be called upon updating the style via CMS.
     * This function transforms the user data (which comes in a table form)
     * into a form such that it can be used by plotly.js to draw a Sankey
     * diagram.
     * The transformed data is stored in the DB within a hidden style field.
     *
     * @param object $cms_model
     *  The CMS model instance.
     */
    public function cms_update_callback($cms_model)
    {
        /* $data = $this->read_sample_csv(); */
        $data = $this->read_data_source();
        if($data !== false) {
            $raw_data = json_encode($this->prepare_sankey_data(
                $data, $this->data_cols, $this->data_types));
            $cms_model->update_db($this->db->fetch_field_id("raw"),
                ALL_LANGUAGE_ID, MALE_GENDER_ID, $raw_data, "section_field");
        }
    }
}
?>
