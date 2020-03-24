<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphView.php";

/**
 * The view class of the graph style component.
 * This style component is a visual container that allows to represent Sankey
 * graph boxes.
 */
class GraphSankeyView extends GraphView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'type' ('primary').
     * The style of the alert. E.g. 'warning', 'danger', etc.
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
    }

    /* Private  Methods *******************************************************/

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
            "color" => $type['color'],
        );
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
    private function prepare_sankey_data($head, $body, $cols, $types)
    {
        $transitions = $this->prepare_sankey_transitions($head, $cols);

        // prepare links based on transitions and a node reference table which
        // will be used to build the final node list
        $nodes_ref = array();
        $node_idx = 0;
        $links = array(
            "source" => array(),
            "target" => array(),
            "value" => array()
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
                    if($sum > 10) {
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
                    }
                }
            }
        }

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
            $nodes["x"][$node_ref['idx']] = $this->compute_position(
                count($cols), $node_ref['col']['idx']);
            $y_idx = array_search($node_ref['type']['idx'],
                $col_counts[$node_ref['col']['key']]);
            $nodes["y"][$node_ref['idx']] = $this->compute_position(
                count($col_counts[$node_ref['col']['key']]), $y_idx);
        }

        return array(
            "link" => $links,
            "node" => $nodes,
            "annotations" => $this->prepare_sankey_annotations($cols)
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

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $data = $this->read_sample_csv();
        $fields = json_encode($this->prepare_sankey_data($data['head'], $data['body'], array(
            array( "key" => "grade7", "label" => "Grade 7" ),
            array( "key" => "grade8", "label" => "Grade 8" ),
            array( "key" => "grade9", "label" => "Grade 9" ),
            array( "key" => "t4_2014", "label" => "2014" ),
            array( "key" => "t4_2015", "label" => "2015" ),
            array( "key" => "t5_2016", "label" => "2016" ),
            array( "key" => "t6_2017", "label" => "2017" ),
            array( "key" => "t7_2018", "label" => "2018" )
        ), array(
            array( "color" => "#EA8571", "key" => 1, "label" => "Grundanforderungen" ),
            array( "color" => "#DE7C89", "key" => 2, "label" => "erweiterte Anforderungen" ),
            array( "color" => "#C27C9D", "key" => 3, "label" => "Gymnasium" ),
            array( "color" => "#9C80A7", "key" => 4, "label" => "ohne Selektion, Werkschule, Rilz" ),
            array( "color" => "#6F84A4", "key" => 5, "label" => "Berufsausbildung" ),
            array( "color" => "#478394", "key" => 6, "label" => "Schulische Ausbildung" ),
            array( "color" => "#31807A", "key" => 7, "label" => "Zwischenlösung" ),
            array( "color" => "#38795D", "key" => 8, "label" => "bezahlte Arbeit" ),
            array( "color" => "#496F42", "key" => 9, "label" => "vollzeitliche berufliche Weiterbildung" ),
            array( "color" => "#59632E", "key" => 10, "label" => "Arbeitslos" ),
            array( "color" => "#665624", "key" => 11, "label" => "Studium" ),
            array( "color" => "#6D4926", "key" => 98, "label" => "anderes" ),
            array( "color" => "#6E3D2E", "key" => 99, "label" => "fehlende Infromation" )
        )));
        require __DIR__ . "/tpl_graph_sankey.php";
    }
}
?>
