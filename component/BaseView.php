<?php
/**
 * The class to define the basic functionality of a view.
 */
abstract class BaseView
{
    /* Private Properties *****************************************************/

    protected $model;
    protected $controller;
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
        $this->controller = $controller;
        if($model != null)
            $this->children = $this->model->get_db_field("content");
        if(($this->children == null) || ($this->children == ""))
            $this->children = array();
    }

    /* Protected Methods ******************************************************/

    /**
     * Render the content of all children of this view instance.
     */
    protected function output_children()
    {
        foreach($this->children as $child)
            $child->output_content();
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
     * @retval array
     *  An array of css include files the view requires. If no overridden, an
     *  empty array is returned.
     */
    public function get_css_includes()
    {
        $css_includes = array();
        foreach($this->children as $child)
            $css_includes += $child->get_css_includes();
        return array_unique($css_includes);
    }
}
?>
