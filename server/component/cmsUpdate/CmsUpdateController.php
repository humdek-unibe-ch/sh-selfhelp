<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
require_once __DIR__ . "/../style/StyleComponent.php";
/**
 * The controller class of the cms update component.
 */
class CmsUpdateController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * Number of successful field changes.
     */
    private $update_success_count;

    /**
     * Number of failed fields changes.
     */
    private $update_fail_count;

    /**
     * Success flag for adding new items.
     */
    private $insert_success;

    /**
     * Fail flag for adding new items.
     */
    private $insert_fail;

    /**
     * Success flag for removing items.
     */
    private $remove_success;

    /**
     * Fail flag for removing items.
     */
    private $remove_fail;

    /**
     * A list of fields where the update failed.
     * See CmsUpdateController::get_bad_fields.
     */
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
        if($_POST['mode'] == "update" && isset($_POST["fields"]))
        {
            $style = null;
            $section_id = $this->model->get_active_section_id();
            if($section_id != null) {
                $style = new StyleComponent(
                    $this->model->get_services(),
                    $section_id
                );
            }
            if($style != null) {
                $style->cms_pre_update_callback($model, $_POST["fields"]);
            }
            $res = $this->update($_POST["fields"], $style);
            if($res && $style != null) {
                $style->cms_post_update_callback($model, $_POST["fields"]);
            }
        } else if ($_POST['mode'] == "insert" && isset($_POST['relation']) && $_POST['relation'] != "") {
            $this->insert();
            $this->delete();
        } else if ($_POST['mode'] == "change_parent" && isset($_POST['relation']) && $_POST['relation'] != "") {
            $this->insert();
        } else if ($_POST['mode'] == "delete" && isset($_POST['delete_all_unassigned_sections'])) {
            if ($_POST['delete_all_unassigned_sections'] == 'DELETE_ALL') {
                if ($this->model->delete_all_unassigned_sections()) {
                    $this->success = true;
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "Verification failed!";
                }
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Verification failed!";
            }
        } else if ($_POST['mode'] == "delete" && $_POST['relation'] != "") {
            $this->delete();
        }
        $this->model->get_db()->clear_cache();
    }

    /* Private Methods ********************************************************/

    /**
     * Check if a JSON value is dynamically set with {{}} and if it is accept it
     * @return boolean
     */
    private function check_json_for_dynamic_content($value){
        preg_match('~{{.*?}}~s', $value, $matches, PREG_OFFSET_CAPTURE);
        return isset($matches[0]) && $matches[0][0];
    }

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
            return (json_last_error() === JSON_ERROR_NONE) || $this->check_json_for_dynamic_content($value);
        }
        if($type === "number")
            return is_numeric($value);
        if($type === "style-bootstrap")
            return in_array($value, array(
                "primary",
                "secondary",
                "success",
                "danger",
                "warning",
                "info",
                "light",
                "dark",
                "none",
            ));
        if($type === "type-input")
            return in_array($value, array(
                "text",
                "checkbox",
                "color",
                "date",
                "datetime-local",
                "datetime",
                "email",
                "month",
                "number",
                "password",
                "range",
                "search",
                "tel",
                "time",
                "url",
                "week",
            ));
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
        if (
            isset($_POST['add-section-link']) && $_POST['add-section-link'] != ""
        ) {
            $this->insert_section_link(intval($_POST['add-section-link']), $_POST['relation'], isset($_POST['position']) ? $_POST['position'] : null);
            header('Location: ' . $this->model->get_link_url("cmsUpdate", array(
                "type" => "prop",
                "mode" => "update",
                "pid" => $this->model->get_active_page_id(),
                "sid" => $_POST['add-section-link'],
                "ssid" => null
            )));
            return $_POST['add-section-link'];
        } else if (isset($_POST['section-name']) && isset($_POST['section-style'])) {
            $section_name = htmlspecialchars($_POST['section-name']);
            $section_style_id = intval($_POST['section-style']);
            $relation = $_POST['relation'];
            $id = $this->insert_new_section(
                $section_name,
                $section_style_id,
                $relation,
                isset($_POST['position']) ? $_POST['position'] : null
            );
            return $id;
        }
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
     * @param int position
     * The position where the section should be inserted. If not set we assign the last position
     */
    private function insert_section_link($id, $relation, $position = null)
    {
        if($this->model->insert_section_link($id, $relation, $position))
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
     * @param int position
     * The position where the section should be inserted. If not set we assign the last position
     * 
     */
    private function insert_new_section($name, $id_style, $relation, $position = null)
    {
        $new_id = $this->model->insert_new_section($name, $id_style, $relation, $position);
        if($new_id != null) {
            $style = new StyleComponent($this->model->get_services(), $new_id);
            $style->cms_post_create_callback($this->model, $name,
                $id_style, $relation, $new_id);
        }
        if($new_id)
        {
            $this->insert_success = true;
            $sid = $this->model->get_active_root_section_id();
            if($relation == RELATION_PAGE_NAV || $relation == RELATION_SECTION_NAV)
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
     * @retval boolean
     *  True if all updates were successful, false if the update did not take
     *  place.
     */
    private function update($fields)
    {
        $page_fields = array();
        $section_fields = array();
        foreach($fields as $name => $languages)
        {
            if(!is_array($languages))
            {
                if(DEBUG == 1)
                    echo "Error: A field must be an array in CmsController::update_fields()";
                continue;
            }            
            foreach($languages as $id_language => $genders)
            {
                if(!is_array($genders))
                {
                    if(DEBUG == 1)
                        echo "Error: A field must be an array in CmsController::update_fields()";
                    continue;
                }
                foreach($genders as $id_gender => $field)
                {
                    $type = $field['type'];
                    $id = intval($field['id']);
                    if($type == "checkbox")
                        $content = isset($field['content']) ? 1 : 0;
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
                    {
                        $content = $this->secure_field($type, $content);
                        if ($relation == RELATION_PAGE) {
                            $page_fields[$name] = $content;
                            $res = true;
                        }else if($relation == RELATION_SECTION){
                            $section_fields[$name] = $content;
                            $res = true;
                        } else {
                            $res = $this->model->update_db(
                                $id,
                                $id_language,
                                $id_gender,
                                $content,
                                $relation
                            );
                        }
                    }
                    else
                        $this->bad_fields[$name][$id_language] = $field;
                    // res can be null which means that nothing was changed
                    if($res === true)
                        $this->update_success_count++;
                    else if($res === false)
                        $this->update_fail_count++;
                }
            }
        }
        if (count($page_fields) > 0) {
            // update page fields in table pages
            $position = null;
            if(isset($_POST['set-position']))
            {
                $position = array();
                foreach(explode(',', $_POST['set-position']) as $item){
                    $position[] = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
                }
                if ($_POST['nav_position'] == "") {
                    // the page has no nav position apply one
                    $page_fields['nav_position'] = 999;
                } 
            } else {
                $page_fields['nav_position'] = null;
            }
            $res = $this->model->update_page($page_fields, $position);
            if ($res && $res >= 0) {
                $this->update_success_count = $this->update_success_count + $res;
            } else if ($res === false) {
                $this->update_fail_count++;
            }
        }
        if (count($section_fields) > 0) {
            // update section fields in table sections
            $res = $this->model->update_section($section_fields);
            if ($res && $res >= 0) {
                $this->update_success_count = $this->update_success_count + $res;
            } else if ($res === false) {
                // only on false count as failed
                $this->update_fail_count++;
            }
        }
        if($this->update_success_count >= 0 && $this->update_fail_count == 0) {
            $this->model->set_mode("select");
            return true;
        }
        return false;
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
        if(in_array($type, array("text", "textarea", "style-list",
            "style-bootstrap", "type-input", "checkbox")))
            return htmlspecialchars($content);
        if($type === "number")
            return intval($content);
        return $content;
    }

    /* Public Methods *********************************************************/

    /**
     * Returns a list of fields where the update process failed.
     *
     * @retval array
     *  Each field is stored in the form res[name][id_language] => field
     */
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
