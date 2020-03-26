<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphModel.php";

/**
 * This class is used to prepare all data related to the emailFormBase style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class GraphSankeyModel extends GraphModel
{
    /* Private Properties *****************************************************/

    private $title;
    private $data_cols;
    private $data_types;
    private $link_color;
    private $link_alpha;
    private $link_hovertemplate;
    private $node_hovertemplate;
    private $min;
    private $has_node_labels;
    private $has_col_labels;
    private $is_grouped;
    public $name;


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
        $this->title = $this->get_db_field("title");
        $this->name = $this->get_db_field("name");
        $this->data_types = $this->get_db_field("value_types");
        $this->data_cols = $this->get_db_field("form_field_names");
        $this->link_color = $this->get_db_field("link_color");
        $this->link_alpha = $this->get_db_field("link_alpha", 0.5);
        $this->link_hovertemplate = $this->get_db_field(
            "link_hovertemplate", "%{source.label} &rarr; %{target.label}");
        $this->node_hovertemplate = $this->get_db_field(
            "node_hovertemplate", "%{label}");
        $this->min = $this->get_db_field("min", 1);
        $this->has_node_labels = $this->get_db_field("has_type_labels",
            false);
        $this->has_col_labels = $this->get_db_field("has_field_labels",
            true);
        $this->is_grouped = $this->get_db_field("is_grouped",
            true);
    }

    /* Private Methods ********************************************************/

    /**
     *
     */
    private function compute_position($size, $idx) {
        $delta_fix = 0.00001;
        $pos = $idx/($size - 1) + $delta_fix;
        if($pos > 1) $pos = 1 - $delta_fix;
        return $pos;
    }

    /**
     *
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
     *
     */
    private function get_col_node($head, $cols, $idx) {
        return array(
            "head_idx" => array_search($cols[$idx]['key'], $head),
            "idx" => $idx,
            "key" => $cols[$idx]['key'],
            "label" => $cols[$idx]['label'],
        );
    }

    /**
     *
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
     *
     */
    private function prepare_sankey_transitions($head, $cols) {
        $transitions = array();
        for($i = 1; $i < count($cols); $i++) {
            array_push($transitions, array(
                "source" => $this->get_col_node($head, $cols, $i - 1),
                "target" => $this->get_col_node($head, $cols, $i)
            ));
        }
        return $transitions;
    }

    /**
     *
     */
    private function prepare_sankey_annotations($cols) {
        if(!$this->has_col_labels || !$this->is_grouped)
            return array();
        $annotations = array();
        foreach($cols as $idx => $col) {
            array_push($annotations, array(
                "text" => $col['label'],
                "x" => $this->compute_position(count($cols), $idx),
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
            array_push($body, $data);
        }
        return array(
            "head" => $head,
            "body" => $body
        );
    }

    /* Public Methods *********************************************************/

    public function check_cols() {
        if(!is_array($this->data_cols)) return false;
        foreach($this->data_cols as $idx => $item)
        {
            if(!isset($item["key"]))
                return false;
            if(!isset($item["label"]))
                $this->data_cols[$idx]["label"] = $item["key"];
        }
        return true;
    }

    public function check_types() {
        if(!is_array($this->data_types)) return false;
        foreach($this->data_types as $idx => $item)
        {
            if(!isset($item["key"]))
                return false;
            if(!isset($item["label"]))
                return false;
        }
        return true;
    }

    /**
     * Prepare the sankey data such taht it can easily be displayed with
     * plotly.js.
     *
     * @param array $head
     *  An array of strings describing the data column headings
     * @param array $body
     *  An array of rows where each row is an array of values.
     */
    public function prepare_sankey_data($head, $body, $cols, $types)
    {
        $transitions = $this->prepare_sankey_transitions($head, $cols);

        // prepare links based on transitions and a node reference table which
        // will be used to build the final node list
        $max_sum = 1;
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
                        if($sum > $max_sum) $max_sum = $sum;
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
            if($this->link_alpha === "sum") {
                $alpha = $links['value'][$i] / $max_sum * 255;
                if( $alpha < 64 ) $alpha = 64;
                else if( $alpha > 192 ) $alpha = 192;
            } else {
                $alpha = (int)(255 * $this->link_alpha);
                if($alpha < 0) $alpha = 0;
                if($alpha > 255) $alpha = 255;
            }
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
            "link" => $links,
            "node" => $nodes,
            "annotations" => $this->prepare_sankey_annotations($cols),
            "title" => $this->title,
            "name" => $this->name,
            "has_node_labels" => $this->has_node_labels ? true : false,
        );
    }

    public function cms_update_callback($cms_model)
    {
        $data = $this->read_sample_csv();
        $raw_data = json_encode($this->prepare_sankey_data(
            $data['head'], $data['body'], $this->data_cols,
            $this->data_types ));
        $cms_model->update_db($this->db->fetch_field_id("raw"),
            ALL_LANGUAGE_ID, MALE_GENDER_ID, $raw_data, "section_field");
    }
}
?>
