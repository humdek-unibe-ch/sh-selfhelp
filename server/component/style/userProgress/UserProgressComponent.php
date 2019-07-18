<?php
require_once __DIR__ . "/UserProgressView.php";
require_once __DIR__ . "/UserProgressModel.php";

/**
 * A component class for the userProgress style component.
 * This component renders a progress bar indicating the user progress.
 */
class UserProgressComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this component.
     */
    public function __construct($services, $id)
    {
        $model = new UserProgressModel($services, $id);
        $view = new UserProgressView($model);
        parent::__construct($model, $view);
    }
}
?>
