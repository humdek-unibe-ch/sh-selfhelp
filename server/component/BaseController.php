<?php
/**
 * The class to define the basic functionality of a controller.
 */
abstract class BaseController
{
    /* Protected Properties ***************************************************/

    /**
     * The model instance of the component.
     */
    protected $model;

    /**
     * The success status.
     */
    protected $success;

    /**
     * The fail status.
     */
    protected $fail;

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
        $this->success = false;
        $this->fail = false;
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the failure status
     *
     * @retval bool
     *  true if the operation has failed, false otherwise.
     */
    public function has_failed()
    {
        return $this->fail;
    }

    /**
     * Returns the success status
     *
     * @retval bool
     *  true if the operation has succeeded, false otherwise.
     */
    public function has_succeeded()
    {
        return $this->success;
    }
}
?>
