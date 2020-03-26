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
     * DB field 'title' (string).
     * The title of the sanket graph.
     */
    private $name;
    private $raw_data;

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
        /* $this->name = $this->model->get_db_field("name"); */
        $this->raw_data = $this->model->get_db_field("raw");
    }

    /* Private  Methods *******************************************************/

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
            $this->name = $this->model->name;
            require __DIR__ . "/tpl_graph_sankey.php";
        }
    }
}
?>
