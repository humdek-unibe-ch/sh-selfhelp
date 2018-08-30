<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms component.
 */
class CmsInsertController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success;
    private $fail;
    private $pid;
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
        $this->success = false;
        $this->fail= false;
        $this->pid = null;
        $this->name = "";
        if(isset($_POST['keyword']) && isset($_POST['url'])
            && isset($_POST['protocol']) && isset($_POST['type']))
        {
            $this->name = $_POST["keyword"];
            $protocol = implode('|', $_POST['protocol']);
            $this->pid = $model->create_new_page($_POST['keyword'],
                $_POST['url'], $protocol, intval($_POST['type']));
            if($this->pid)
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Return the newly created page id.
     *
     * @return int
     *  The newly created page id.
     */
    public function get_new_pid()
    {
        return $this->pid;
    }

    /**
     * Return the newly created page name.
     *
     * @return int
     *  The newly created page name.
     */
    public function get_new_page_name()
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
