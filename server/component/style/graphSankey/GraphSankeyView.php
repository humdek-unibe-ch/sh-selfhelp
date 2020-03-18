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
        $color_idx = 0;
        $colors = array(
            '#46201A',
            '#5B2B36',
            '#643E57',
            '#5B5676',
            '#3F708B',
            '#14888E',
            '#279E80',
            '#63AF67',
            '#A4BB4E',
            '#E9BE4A'
        );
        $color_map = array();
        $transitions = array();
        for($i = 1; $i < count($cols); $i++) {
            array_push($transitions, array(
                "source" => $cols[$i-1],
                "target" => $cols[$i],
            ));
        }
        $nodes_ref = array();
        $node_idx = 0;
        $links = array(
            "source" => array(),
            "target" => array(),
            "value" => array()
        );
        foreach($transitions as $transition) {
            foreach($types as $type_src) {
                foreach($types as $type_tgt) {
                    $source = array(
                        "grade" => $transition["source"],
                        "type" => $type_src
                    );
                    $target = array(
                        "grade" => $transition["target"],
                        "type" => $type_tgt
                    );
                    $source_key = $source['grade'].'-'.$source['type'];
                    $target_key = $target['grade'].'-'.$target['type'];
                    $sum = $this->compute_sankey_link_sum($head, $body,
                        $source, $target);
                    if($sum > 80) {
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
        $nodes = array(
            "label" => array(),
            "color" => array(),
            "x" => array(),
            "y" => array()
        );
        foreach($nodes_ref as $key => $node_ref) {
            $nodes["label"][$node_ref['idx']] = $key;
            if(!array_key_exists($node_ref['type'], $color_map)) {
                $color_map[$node_ref['type']] = $colors[$color_idx];
                $color_idx++;
                if($color_idx == count($colors)) {
                    $color_idx = 0;
                }
            }
            $delta_fix = 0.00001;
            $nodes["color"][$node_ref['idx']] = $color_map[$node_ref['type']];
            $grade_idx = array_search($node_ref['grade'], $cols);
            $type_idx = array_search($node_ref['type'], $types);
            $grade_pos = $grade_idx/(count($cols) - 1) + $delta_fix;
            if($grade_pos > 1) $grade_pos = 1 - $delta_fix;
            $type_pos = $type_idx/(count($types) - 1) + $delta_fix;
            if($type_pos > 1) $type_pos = 1 - $delta_fix;
            $nodes["x"][$node_ref['idx']] = $grade_pos;
            $nodes["y"][$node_ref['idx']] = $type_pos;
        }
        return array(
            "link" => $links,
            "node" => $nodes
        );
    }

    /**
     *
     */
    private function compute_sankey_link_sum($head, $body, $source, $target)
    {
        $col_idx_src = array_search($source['grade'], $head);
        $col_idx_tgt = array_search($target['grade'], $head);
        $sum = 0;
        foreach($body as $row) {
            if(($row[$col_idx_src] == $source['type'])
                    && ($row[$col_idx_tgt] == $target['type'])) {
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
            "grade7", "grade8", "grade9", "t4_2014", "t4_2015", "t5_2016",
            "t6_2017", "t7_2018"
        ), array(
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 98, 99
        )));
        require __DIR__ . "/tpl_graph_sankey.php";
    }
}
?>

