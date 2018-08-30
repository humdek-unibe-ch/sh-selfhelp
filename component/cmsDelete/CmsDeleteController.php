<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms component.
 */
class CmsDeleteController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success;
    private $fail;
    private $name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->name = "";
        $this->success = false;
        $this->fail= false;
        if(isset($_POST["name"]))
        {
            $res = false;
            if($this->model->get_active_section_id() == null)
            {
                $info = $this->model->get_page_info();
                if($_POST["name"] == $info["keyword"])
                    $res = $this->model->delete_page(
                        $this->model->get_active_page_id());
            }
            else
            {
                $info = $this->model->get_section_info();
                if($_POST["name"] == $info["name"])
                    $res = $this->model->delete_section(
                        $this->model->get_active_section_id());
            }
            if($res)
            {
                $this->success = true;
                $this->name = $_POST['name'];
            }
            else
                $this->fail = true;
        }
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Return the name of the deleted element.
     *
     * @return string
     *  The name of the deleted element.
     */
    public function get_deleted_name()
    {
        return $this->name;
    }

    /**
     * Gets the insert success falg.
     *
     * @retval bool
     *  True if the insert operation succeeded, false if no successful insert
     *  operation took place.
     */
    public function has_succeeded()
    {
        return $this->success;
    }

    /**
     * Gets the insert fail falg.
     *
     * @retval bool
     *  True if the insert operation failed, false if no failed insert
     *  operation took place.
     */
    public function has_failed()
    {
        return $this->fail;
    }
}
?>
