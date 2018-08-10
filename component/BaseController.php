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
}
?>
