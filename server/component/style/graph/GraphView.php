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

    /**
     * Show the graph. It is false if the name is dynamic and it cannot be loaded
     */
    private $show_graph = true;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param string $code
     * value from the url
     */
    public function __construct($model, $code = null)
    {
        parent::__construct($model);
        $this->title = $this->model->get_db_field("title");
        $this->traces = $this->model->get_db_field("traces");
        $this->layout = $this->model->get_db_field("layout");
        $this->config = $this->model->get_db_field("config");
        if ($this->is_dynamic()) {
            // if there is a dynamic name we check if this name comes as a paramter from the url
            // if it does not come we do not show the graph
            if (!$code) {
                $this->show_graph = false;
            } else {
                $this->set_dynamic_parameter($code);
            }
        }
    }

    /* Private  Methods *******************************************************/
    /**
     * Check if the graph is dynamic and waits for parameters from the url
     * @retval boolean return true if the graph is dynamic
     */
    private function is_dynamic(){
        if ($this->traces) {
            foreach ($this->traces as $trace) {
                if ($trace['data_source']['name'] == '@dynamic_name') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the dynamic parameter in the traces of the graph
     * @param string $code the value of the paramter that comes from the url
     */
    private function set_dynamic_parameter($code){
        foreach ($this->traces as $key => $value) {
            if($this->traces[$key]['data_source']['name'] == '@dynamic_name'){
                $this->traces[$key]['data_source']['name'] = $code;
            }
        }
    }

    /**
     * Render the graph data to be used by the js library to draw the graph.
     */
    private function output_graph_data()
    {
        if ($this->title !== "") {
            if ($this->layout === "") {
                $this->layout = array();
            }
            $this->layout["title"] = $this->title;
        }
        if (!isset($this->config["responsive"])) {
            if ($this->config === "") {
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

    /**
     * Check whether the config field entered through the CMS is valid.
     *
     * @retval boolean
     *  True if the config field is valid, False otherwise.
     */
    private function check_config()
    {
        if (!is_array($this->config) && $this->config != NULL)
            return false;
        return true;
    }

    /**
     * Check whether the layout field entered through the CMS is valid.
     *
     * @retval boolean
     *  True if the layout field is valid, False otherwise.
     */
    private function check_layout()
    {
        if (!is_array($this->layout) && $this->layout != NULL)
            return false;
        return true;
    }

    /**
     * Check whether the traces field entered through the CMS is valid.
     *
     * @retval boolean
     *  True if the traces field is valid, False otherwise.
     */
    private function check_traces()
    {
        if (!is_array($this->traces) && $this->traces != NULL)
            return false;
        if (is_array($this->traces)) {
            foreach ($this->traces as $idx => $trace) {
                if (isset($trace["data_source"])) {
                    if (!isset($trace["data_source"]["name"]))
                        return false;
                    /* if(!isset($trace["data_source"]["map"]) */
                    /*         && !is_array($trace["data_source"]["map"])) */
                    /*     return false; */
                    if (!isset($trace["data_source"]["single_user"]))
                        $trace["data_source"]["single_user"] = true;
                }
            }
        }
        return true;
    }

    /* Protected  Methods *****************************************************/

    /**
     * Allows to set the type of the graph.
     * This type is used to distinguish between different JS implementations
     * of the data source callback.
     *
     * @param string $name
     *  The name of the type. This can either of the following strings
     *   - 'sankey'
     *   - 'base'
     */
    protected function set_graph_type($name)
    {
        $this->graph_type = $name;
    }

    /**
     * Render the graph options. Graph options are used to communicate
     * additional options to JS which cannot be included in the traces. Refer
     * to GraphSankeyView::output_graph_opts() for an example.
     */
    protected function output_graph_opts()
    {
        echo "{}";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if ($this->show_graph) {
            if (!$this->check_traces()) {
                echo "parse error in <code>traces</code>";
            } else if (!$this->check_layout()) {
                echo "parse error in <code>layout</code>";
            } else if (!$this->check_config()) {
                echo "parse error in <code>layout</code>";
            } else {
                require __DIR__ . "/tpl_graph.php";
            }
        }
    }
}
?>
