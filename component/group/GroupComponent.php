<?php
require_once __DIR__ . "/../BaseComponent.php";

/**
 * The group component.
 */
class GroupComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model, the View, and the
     * Controller.
     *
     */
    public function __construct($model, $view, $controller=null)
    {
        $this->model = $model;
        parent::__construct($view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid groups.
     *
     * @retval bool
     *  True if the group exists, false otherwise
     */
    public function has_access()
    {
        if($this->model->get_gid() != null
            && $this->model->get_selected_group() == null)
            return false;
        return parent::has_access();
    }
}
?>
