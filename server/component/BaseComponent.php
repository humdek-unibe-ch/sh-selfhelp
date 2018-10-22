<?php
spl_autoload_register(function ($class_name) {
    $folder = str_replace("Component", "", $class_name);
    $folder = lcfirst(str_replace("View", "", $folder));
    $file = "/" . $folder . "/" . $class_name . ".php";
    if(file_exists(__DIR__ . "/style" . $file))
        require_once __DIR__ . "/style" . $file;
    else if(file_exists(__DIR__ . $file))
        require_once __DIR__ . $file;
});
/**
 * The class to define the basic functionality of a component.
 */
abstract class BaseComponent
{
    /* Private Properties *****************************************************/

    /**
     * The view instance of the component.
     */
    protected $view;

    /**
     * The controller instance of the component.
     */
    protected $controller;

    /**
     * The model instance of the component.
     */
    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor requires the view instance of a component to iprovide a
     * function to render the output of the component. It further requires the
     * view and the controller instance to include the necessary css and js
     * files.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $view
     *  The view instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $view, $controller=null)
    {
        $this->view = $view;
        $this->controller = $controller;
        $this->model = $model;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    public function output_content()
    {
        if($this->view)
            $this->view->output_content();
    }

    /**
     * Get css include files required for this component. Extensions of this
     * class ought to override this method. By default, a component includes no
     * css files.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of css include files the component requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_css_includes($local = array())
    {
        if($this->view == null) return $local;
        return array_merge($local, $this->view->get_css_includes());
    }

    /**
     * Get js include files required for this component. Extensions of this
     * class ought to override this method. By default, a component includes no
     * js files.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of js include files the component requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_js_includes($local = array())
    {
        if($this->view == null) return $local;
        return array_merge($local, $this->view->get_js_includes());
    }

    /**
     * Gets all the child components of this component.
     *
     * @return array
     *  A key => value array of components.
     */
    public function get_children()
    {
        if($this->model == null) return array();
        return $this->model->get_children();
    }

    /**
     * Returns the view instance of this component.
     *
     * @retval object
     *  The view instance of this component.
     */
    public function get_view()
    {
        return $this->view;
    }

    /**
     * Always returns true. A component extending the base component should
     * overwrite this method if invalid url parameters are passed.
     *
     * @retval bool
     *  True
     */
    public function has_access()
    {
        return true;
    }
}
?>
