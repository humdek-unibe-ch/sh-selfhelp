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
    private $insert_success;
    private $insert_fail;
    private $remove_success;
    private $remove_fail;

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
        $this->insert_success = false;
        $this->insert_fail= false;
        $this->remove_success = false;
        $this->remove_fail= false;
        if(isset($_POST['remove-section-link']))
            $this->remove_section_link(intval($_POST['remove-section-link']));

        else if(isset($_POST['add-section-link'])
                && $_POST['add-section-link'] != "")
            $this->insert_section(intval($_POST['add-section-link']));

        else if(isset($_POST['section-name']) && isset($_POST['section-style']))
            $this->insert_new_section(htmlspecialchars($_POST['section-name']),
                intval($_POST['section-style']));

        else
            $this->update_fields($_POST);
    }

    /* Private Methods ********************************************************/

    private function remove_section_link($id_section)
    {
        if($this->model->remove_section_association($id_section))
            $this->remove_success = true;
        else
            $this->remove_fail = true;
    }

    private function insert_section($id)
    {
        if($this->model->insert_section_link($id))
            $this->insert_section_success($id);
        else
            $this->insert_fail = true;
    }

    private function insert_new_section($name, $id_style)
    {
        $new_id = $this->model->insert_section($name, $id_style);
        if($new_id)
            $this->insert_section_success($new_id);
        else
            $this->insert_fail = true;
    }

    private function insert_section_success($id)
    {
        $this->insert_success = true;
        $sid = $this->model->get_active_root_section_id();
        header('Location: ' . $this->model->get_link_url("cms_update", array(
            "pid" => $this->model->get_active_page_id(),
            "sid" => ($sid == null) ? $id : $sid,
            "ssid" => $id
        )));
    }

    private function update_fields($post)
    {
        foreach($post as $name => $fields)
        {
            if(!is_array($fields))
            {
                if(DEBUG == 1)
                    echo "Error: A field must be an array in CmsController::update_fields()";
                continue;
            }
            foreach($fields as $id_language => $field)
            {
                $type = $field['type'];
                $id = intval($field['id']);
                if($type == "checkbox")
                    $content = isset($field['content']);
                else
                    $content = $field['content'];
                $relation = $field['relation'];
                if($type == "internal")
                {
                    $info = $this->model->get_field_info($name);
                    $type = $info['type'];
                    $id = intval($info['id']);
                }
                $res = $this->model->update_db($id, $id_language,
                    $this->secure_field($type, $content), $relation);
                // res can be null which means that nothing was changed
                if($res === true)
                    $this->update_success_count++;
                else if($res === false)
                    $this->update_fail_count++;
            }
        }
    }

    private function secure_field($type, $content)
    {
        if(in_array($type, array("text", "textarea", "style-list")))
            return htmlspecialchars($content);
        if(in_array($type, array("markdown", "markdown-inline", "checkbox")))
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

    public function has_insert_succeeded()
    {
        return $this->insert_success;
    }

    public function has_insert_failed()
    {
        return $this->insert_fail;
    }

    public function has_remove_succeeded()
    {
        return $this->remove_success;
    }

    public function has_remove_failed()
    {
        return $this->remove_fail;
    }
}
?>
