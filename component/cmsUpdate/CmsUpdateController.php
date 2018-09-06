<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms update component.
 */
class CmsUpdateController extends BaseController
{
    /* Private Properties *****************************************************/

    private $update_success_count;
    private $update_fail_count;
    private $insert_success;
    private $insert_fail;
    private $remove_success;
    private $remove_fail;
    private $bad_fields;

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
        $this->bad_fields = array();
        if(!isset($_POST['mode'])) return;
        else if($_POST['mode'] == "update" && isset($_POST["fields"]))
            $this->update($_POST["fields"]);
        else if($_POST['mode'] == "insert"
                && isset($_POST['relation']) && $_POST['relation'] != "")
            $this->insert();
        else if($_POST['mode'] == "delete" && $_POST['relation'] != "")
            $this->delete();

    }

    /* Private Methods ********************************************************/

    /**
     * Checks whether the content of a input field corresponds to the xpected
     * type.
     *
     * @param string $type
     *  The type of the input field.
     * @param string $value
     *  The value of the input field.
     */
    private function check_content($type, $value)
    {
        if($type === "json")
        {
            if($value === "") return true;
            json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        if($type === "number")
            return is_numeric($value);
        return true;
    }

    /**
     * Performs delete operations:
     *  - Remove section links.
     */
    private function delete()
    {
        if(isset($_POST['remove-section-link']))
            $this->remove_section_link(intval($_POST['remove-section-link']),
                $_POST['relation']);
    }

    /**
     * Performs insert operations:
     *  - Create new sections.
     *  - Add new section links.
     */
    private function insert()
    {
        if(isset($_POST['add-section-link'])
                && $_POST['add-section-link'] != "")
            $this->insert_section_link(intval($_POST['add-section-link']),
                $_POST['relation']);

        else if(isset($_POST['section-name']) && isset($_POST['section-style']))
            $this->insert_new_section(htmlspecialchars($_POST['section-name']),
                intval($_POST['section-style']), $_POST['relation']);

    }

    /**
     * Creates a link between a section and another section or a page.
     *
     * @param int $id
     *  The id of the section to which the current section or page will be
     *  linked.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     */
    private function insert_section_link($id, $relation)
    {
        if($this->model->insert_section_link($id, $relation))
        {
            $this->insert_success = true;
            $this->model->set_mode("select");
        }
        else
            $this->insert_fail = true;
    }

    /**
     * Creates a new section and adds a link of the newly created section to the
     * current page or section.
     *
     * @param string $name
     *  The name of the new section
     * @param int $id_style
     *  The id of the style of the new section.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     */
    private function insert_new_section($name, $id_style, $relation)
    {
        $new_id = $this->model->insert_new_section($name, $id_style, $relation);
        if($new_id)
        {
            $this->insert_success = true;
            $sid = $this->model->get_active_root_section_id();
            if($relation == "page_nav" || $relation == "section_nav")
            {
                $sid = $new_id;
                $new_id = null;
            }
            header('Location: ' . $this->model->get_link_url("cmsUpdate", array(
                "type" => "prop",
                "mode" => "update",
                "pid" => $this->model->get_active_page_id(),
                "sid" => ($sid == null) ? $new_id : $sid,
                "ssid" => $new_id
            )));
        }
        else
            $this->insert_fail = true;
    }

    /**
     * Remove a section link form a navigation or a children list.
     *
     * @param int $id_section
     *  The id of the section to which the link to remove is pointing.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     */
    private function remove_section_link($id_section, $relation)
    {
        if($this->model->remove_section_association($id_section, $relation))
        {
            $this->remove_success = true;
            $this->model->set_mode("select");
        }
        else
            $this->remove_fail = true;
    }

    /**
     * Perform field update operations.
     *
     * @param array $fields
     *  An array of fields where each field has multiple languages, stored as 
     *  key => value pairs where the key is the language id and the value the
     *  field. A field has the follwing keys:
     *   'type':    The type of the field.
     *   'id':      The id of the field.
     *   'content'  The content of the field.
     *   'relation' The database relation of the field.
     */
    private function update($fields)
    {
        if(isset($_POST['set_margin']))
        {
            $css = "";
            if(isset($_POST['margin'])) $css = implode(" ", $_POST['margin']);
            $this->model->update_db(CSS_FIELD_ID, 1, $css, "section_field");
        }

        foreach($fields as $name => $languages)
        {
            if(!is_array($languages))
            {
                if(DEBUG == 1)
                    echo "Error: A field must be an array in CmsController::update_fields()";
                continue;
            }
            foreach($languages as $id_language => $field)
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
                $res = false;
                if($this->check_content($type, $content))
                    $res = $this->model->update_db($id, $id_language,
                        $this->secure_field($type, $content), $relation);
                else
                    $this->bad_fields[$name][$id_language] = $field;
                // res can be null which means that nothing was changed
                if($res === true)
                    $this->update_success_count++;
                else if($res === false)
                    $this->update_fail_count++;
            }
        }
        if($this->update_success_count >= 0 && $this->update_fail_count == 0)
            $this->model->set_mode("select");
    }

    /**
     * Sanitice input fields in order to prevent unwanted injection.
     *
     * @param string $type
     *  The type of the input field.
     * @param string $content
     *  The content of the input field.
     * @retval string
     *  The sanitized content.
     */
    private function secure_field($type, $content)
    {
        if(in_array($type, array("text", "textarea", "style-list")))
            return htmlspecialchars($content);
        if(in_array($type, array("markdown", "markdown-inline", "checkbox",
                "json", "style-bootstrap")))
            return $content;
        if($type === "number")
            return intval($content);
    }

    /* Public Methods *********************************************************/

    public function get_bad_fields()
    {
        return $this->bad_fields;
    }

    /**
     * Get the number of successfully update fields.
     *
     * @retval int
     *  The number of successfully updated fields.
     */
    public function get_update_success_count()
    {
        return $this->update_success_count;
    }

    /**
     * Get the number of fields where the update failed.
     *
     * @retval int
     *  The number of fields where the update failed.
     */
    public function get_update_fail_count()
    {
        return $this->update_fail_count;
    }

    /**
     * Gets the insert success falg.
     *
     * @retval bool
     *  True if the insert operation succeeded, false if no successful insert
     *  operation took place.
     */
    public function has_insert_succeeded()
    {
        return $this->insert_success;
    }

    /**
     * Gets the insert fail falg.
     *
     * @retval bool
     *  True if the insert operation failed, false if no failed insert
     *  operation took place.
     */
    public function has_insert_failed()
    {
        return $this->insert_fail;
    }

    /**
     * Gets the delete success falg.
     *
     * @retval bool
     *  True if the delete operation succeeded, false if no successful delete
     *  operation took place.
     */
    public function has_remove_succeeded()
    {
        return $this->remove_success;
    }

    /**
     * Gets the delete fail falg.
     *
     * @retval bool
     *  True if the delete operation failed, false if no failed delete
     *  operation took place.
     */
    public function has_remove_failed()
    {
        return $this->remove_fail;
    }
}
?>
