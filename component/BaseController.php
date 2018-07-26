<?php
/**
 * The class to define the basic functionality of a controller.
 */
abstract class BaseController
{
    /* Private Properties *****************************************************/

    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this controller. Extensions of this
     * class ought to override this method. By default, a component includes no
     * js files.
     *
     * @retval array
     *  An array of js include files the contoller requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_js_includes()
    {
        return array();
    }
}
?>
