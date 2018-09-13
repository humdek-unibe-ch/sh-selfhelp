<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms insert component.
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
     *  The model instance of the cms insert component.
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
            $this->name = filter_var($_POST["keyword"], FILTER_SANITIZE_STRING);
            foreach($_POST['protocol'] as $protocol)
                if($protocol != "GET" && $protocol != "POST"
                    && $protocol != "PATCH" && $protocol != "PUT"
                    && $protocol != "DELTE")
                {
                    $this->fail = true;
                    return;
                }
            $protocol = implode('|', $_POST['protocol']);
            $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
            $type = filter_var($_POST['type'], FILTER_SANITIZE_NUMBER_INT);
            if(!$this->name || !$url || !$type)
            {
                $this->fail = true;
                return;
            }
            $position = isset($_POST['set-position']) ? $_POST['set-position'] : null;
            $this->pid = $model->create_new_page($this->name, $url, $protocol,
                intval($type), $position, $this->model->get_active_page_id());
            if($this->pid)
                $this->success = true;
            else
                $this->fail = true;
        }
    }

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
