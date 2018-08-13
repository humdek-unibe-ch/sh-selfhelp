<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms component.
 */
class CmsController extends BaseController
{
    /* Private Properties *****************************************************/

    private $update_success_count;
    private $update_fail_count;

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
        $this->update_success_count = 0;
        $this->update_fail_count = 0;
        foreach($_POST as $name => $fields)
            foreach($fields as $id_language => $field)
            {
                $fields = array();
                $type = $field['type'];
                $id = intval($field['id']);
                $content = $field['content'];
                if($type == "internal")
                {
                    $info = $this->model->get_field_info($name);
                    $type = $info['type'];
                    $id = intval($info['id']);
                }
                $fields["content"] = $this->secure_field($type, $content);
                if($this->model->update_db($id, $id_language, $fields))
                    $this->update_success_count++;
                else
                    $this->update_fail_count++;
            }
    }

    /* Private Methods ********************************************************/

    private function secure_field($type, $content)
    {
        if(in_array($type, array("text", "textarea")))
            return htmlspecialchars($content);
        if(in_array($type, array("markdown", "markdown-inline")))
            return $content;
        if($type === "number")
            return intval($content);
    }

    /* Public Methods *********************************************************/

    public function get_update_success_count()
    {
        return $this->update_success_count;
    }

    public function get_update_fail_count()
    {
        return $this->update_fail_count;
    }
}
?>
