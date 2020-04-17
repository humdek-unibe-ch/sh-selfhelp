<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the graph style component.
 * This style component is a visual container that allows to represent graph
 * boxes.
 */
class GraphView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'title' (empty string)
     * The title of the graph. If set this will be added to the layout
     * definition.
     */
    private $title;

    /**
     * DB field 'traces' (empty string)
     * A JSON array holding trace definitions.
     */
    protected $traces;

    /**
     * DB field 'layout' (empty string)
     * The layout definition of the graph.
     */
    protected $layout;

    /**
     * DB field 'config' (empty string)
     * The configuration definition of the graph.
     */
    private $config;

    /**
     * The graph type name name is appended to the css class name of the root
     * graph element.
     */
    private $graph_type = "base";

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
        $this->title = $this->model->get_db_field("title");
        $this->traces = $this->model->get_db_field("traces");
        $this->layout = $this->model->get_db_field("layout");
        $this->config = $this->model->get_db_field("config");
    }

    /* Private  Methods *******************************************************/

    private function output_graph_data()
    {
        if($this->title !== "") {
            if($this->layout === "") {
                $this->layout = array();
            }
            $this->layout["title"] = $this->title;
        }
        if(!isset($this->config["responsive"])) {
            if($this->config === "") {
                $this->config = array();
            }
            $this->config["responsive"] = true;
        }

        echo json_encode(array(
            "graph_type" => $this->graph_type,
            "layout" => $this->layout ? $this->layout : new stdClass,
            "config" => $this->config ? $this->config : new stdClass,
            "traces" => $this->traces ? $this->traces : array()
        ));
    }

    private function check_config()
    {
        if(!is_array($this->config) && $this->config != NULL)
            return false;
        return true;
    }

    private function check_layout()
    {
        if(!is_array($this->layout) && $this->layout != NULL)
            return false;
        return true;
    }

    private function check_traces()
    {
        if(!is_array($this->traces) && $this->traces != NULL)
            return false;
        if(is_array($this->traces)) {
            foreach($this->traces as $idx => $trace)
            {
                if(isset($trace["data_source"])) {
                    if(!isset($trace["data_source"]["name"]))
                        return false;
                    /* if(!isset($trace["data_source"]["map"]) */
                    /*         && !is_array($trace["data_source"]["map"])) */
                    /*     return false; */
                    if(!isset($trace["data_source"]["single_user"]))
                        $trace["data_source"]["single_user"] = true;
                }
            }
        }
        return true;
    }

    /* Protected  Methods *****************************************************/

    protected function set_graph_type($name)
    {
        $this->graph_type = $name;
    }

    protected function output_graph_opts() {
        echo "{}";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if(!$this->check_traces()) {
            echo "parse error in <code>traces</code>";
        } else if(!$this->check_layout()) {
            echo "parse error in <code>layout</code>";
        } else if(!$this->check_config()) {
            echo "parse error in <code>layout</code>";
        } else {
            require __DIR__ . "/tpl_graph.php";
        }
    }
}
?>
