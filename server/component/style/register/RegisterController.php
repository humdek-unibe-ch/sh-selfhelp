<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the register component.
 */
class RegisterController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Submitted credentials are checked.
     *
     * @param object $model
     *  The model instance of the register component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['email']) && isset($_POST['code']))
        {
        }
    }

    /* Public Methods *********************************************************/
}
?>
