<?php
/**
 * The class to define the basic functionality of a component.
 */
abstract class BaseComponent
{
    /* Private Properties *****************************************************/

    private $view;

    /* Constructors ***********************************************************/

    /**
     * The constructor requires the view instance of a component to iprovide a 
     * function to render the output of the component.
     *
     * @param object $view
     *  The view instance of the component.
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    public function output_content()
    {
        $this->view->output_content();
    }

    /**
     * Get css include files required for this component. Extensions of this
     * class ought to override this method. By default, a component includes no
     * css files.
     *
     * @retval array
     *  An array of css include files the component requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_css_includes()
    {
        return array();
    }

    /**
     * Get js include files required for this component. Extensions of this
     * class ought to override this method. By default, a component includes no
     * js files.
     *
     * @retval array
     *  An array of js include files the component requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_js_includes()
    {
        return array();
    }
}
?>
