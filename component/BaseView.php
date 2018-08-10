<?php
/**
 * The class to define the basic functionality of a view.
 */
abstract class BaseView
{
    /* Private Properties *****************************************************/

    protected $model;
    protected $controller;
    private $local_components;
    private $children;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance that is used to provide the view with data.
     * @param object $controller
     *  The controler instance that is used to handle user interaction.
     */
    public function __construct($model = null, $controller = null)
    {
        $this->model = $model;
        $this->children = array();
        if($model != null) $this->children = $model->get_children();
        $this->controller = $controller;
        $this->local_components = array();
    }

    /* Protected Methods ******************************************************/

    /**
     * Add a component to the local component list. Thes components were
     * instantiated inside this view.
     *
     * @param string name
     *  The name of the component.
     * @param object $component
     *  A component object.
     */
    protected function add_local_component($name, $component)
    {
        $this->local_components[$name] = $component;
    }

    /**
     * Render the content of all children of this view instance.
     */
    protected function output_children()
    {
        foreach($this->children as $child)
            $child->output_content();
    }

    /**
     * Get a local component given a component name.
     *
     * @param string name
     *  The name of the component.
     * @retval object
     *  A component object.
     */
    protected function output_local_component($name)
    {
        $component = $this->get_local_component($name);
        if($component != null)
            $component->output_content();
    }

    /**
     * Get a local component given a component name.
     *
     * @param string name
     *  The name of the component.
     * @retval object
     *  A component object.
     */
    protected function get_local_component($name)
    {
        if(array_key_exists($name, $this->local_components))
            return $this->local_components[$name];
        else
            return null;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    abstract public function output_content();

    /**
     * Get css include files required for this view. By default the css files of
     * the children of a section are included.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of css include files the view requires. If no overridden, an
     *  empty array is returned.
     */
    public function get_css_includes($local = array())
    {
        $css_includes = array();
        foreach($this->children as $child)
        {
            $css_includes = array_merge($css_includes,
                $child->get_css_includes());
        }
        foreach($this->local_components as $component)
            $css_includes = array_merge($css_includes,
                $component->get_css_includes());
        return array_unique(array_merge($local, $css_includes));
    }

    /**
     * Get js include files required for this view. By default the js files of
     * the children of a section are included.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of js include files the view requires. If no overridden, an
     *  empty array is returned.
     */
    public function get_js_includes($local = array())
    {
        $js_includes = array();
        foreach($this->children as $child)
            $js_includes = array_merge($js_includes,
                $child->get_js_includes());
        foreach($this->local_components as $component)
            $js_includes = array_merge($js_includes,
                $component->get_js_includes());
        return array_unique(array_merge($local, $js_includes));
    }
}
?>
