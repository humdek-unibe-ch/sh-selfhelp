<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms component.
 */
class CmsController extends BaseController
{
    /* Private Properties *****************************************************/

    private $fields;

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
        $this->fields = array();
        foreach($_POST as $name => $content)
        {
            $type = $this->model->get_field_type($name);
            $this->fields[$name] = $this->secure_field($type, $content);
        }
    }

    /* Private Methods ********************************************************/

    private function secure_field($type, $content)
    {
        if($type === "input")
            echo htmlspecialchars($content);
    }

    /* Public Methods *********************************************************/
}
?>
