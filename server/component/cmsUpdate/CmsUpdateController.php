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
     * Success flag for moving a section to another list.
     */
    private $move_success;

    /**
     * Fail flag for moving a section to another list.
     */
    private $move_fail;

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
        $this->move_success = false;
        $this->move_fail = false;
        $this->remove_success = false;
        $this->remove_fail= false;
        $this->bad_fields = array();
        if(!isset($_POST['mode'])) return;
        if($_POST['mode'] == "update" && isset($_POST["fields"]))
        {
            $style = null;
            $section_id = $this->model->get_active_section_id();
            if (!$section_id && isset($_POST['id_section'])) {
                $section_id = intval($_POST['id_section']);
            }
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
                if (in_array($style->get_model()->get_style_name(), ['formUserInputRecord', 'formUserInputLog', 'formUserInputLogList']) && isset($_POST["fields"]['name'])) {
                    // update the displayName for the data tables
                    $style->get_model()->set_dataTables_displayName($section_id, $_POST["fields"]['name'][1][1]['content']);
                }
                $style->cms_post_update_callback($model, $_POST["fields"]);
            }
        } else if ($_POST['mode'] == "insert" && isset($_POST['relation']) && $_POST['relation'] != "") {
            $this->insert();
            $this->delete();
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
     *  - Move a section to another list.
     */
    private function insert()
    {
        if (isset($_POST['move-section-link']) && $_POST['move-section-link'] != "") {
            $this->move_section_link(
                intval($_POST['move-section-link']),
                isset($_POST['from-relation']) ? $_POST['from-relation'] : "",
                isset($_POST['from-parent']) ? intval($_POST['from-parent']) : 0,
                $_POST['relation'],
                isset($_POST['position']) ? $_POST['position'] : null
            );
        } else if (
            isset($_POST['add-section-link']) && $_POST['add-section-link'] != ""
        ) {
            $this->insert_section_link(intval($_POST['add-section-link']), $_POST['relation'], isset($_POST['position']) ? $_POST['position'] : null);
            // The redirect tells the caller that the link was added. A refused link must not redirect.
            if ($this->insert_success) {
                $this->redirect_to_section(intval($_POST['add-section-link']), $_POST['relation']);
            }
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
     * Moves a section from one list of children to another list in one step.
     *
     * @param int $id
     *  The id of the section to move.
     * @param string $from_relation
     *  The relation of the old list.
     * @param int $from_parent
     *  The id of the section which has the old list (the page for a page list).
     * @param string $relation
     *  The relation of the new list.
     * @param int position
     *  The position where the section should be inserted. If not set we assign the last position
     */
    private function move_section_link($id, $from_relation, $from_parent, $relation, $position = null)
    {
        if($this->model->move_section_link($id, $from_relation, $from_parent, $relation, $position))
        {
            $this->move_success = true;
            $this->model->set_mode("select");
        }
        else
            $this->move_fail = true;
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
        if($new_id) {
            $style = new StyleComponent($this->model->get_services(), $new_id);
            $style->cms_post_create_callback($this->model, $name,
                $id_style, $relation, $new_id);
            $this->insert_success = true;
            $this->redirect_to_section(intval($new_id), $relation);
        }
        else
            $this->insert_fail = true;
    }

    /**
     * Redirect to the CMS editor of a section after it was added. The cache is
     * cleared before the redirect so the next request sees the new link
     * (otherwise CmsComponent::has_access can still use the old section tree
     * and show "Page not Found"). On a navigation page the root of the url
     * stays the navigation item and the new section is selected as ssid.
     *
     * @param int $section_id
     *  The id of the section to select.
     * @param string $relation
     *  The relation used for the insert (navigation items become the root).
     */
    private function redirect_to_section($section_id, $relation)
    {
        $this->model->get_db()->clear_cache();
        $sid = $this->model->get_active_root_section_id();
        $ssid = null;
        if ($relation == RELATION_PAGE_NAV || $relation == RELATION_SECTION_NAV) {
            $sid = $section_id;
        } else if ($sid == null) {
            $sid = $section_id;
        } else {
            $ssid = $section_id;
        }
        header('Location: ' . $this->model->get_link_url("cmsUpdate", array(
            "type" => "prop",
            "mode" => "update",
            "pid" => $this->model->get_active_page_id(),
            "sid" => $sid,
            "ssid" => $ssid,
        )));
        exit();
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
                        $content = isset($field['content']) ? $field['content'] : "";
                    $relation = $field['relation'];
                    if($type == "internal")
                    {
                        $info = $this->model->get_field_info($name);
                        $type = $info['type'];
                        $id = intval($info['id']);
                    }
                    $res = false;
                    if($this->model->check_field_content($type, $content))
                    {
                        $content = $this->model->secure_field_content($type, $content);
                        if(is_array($content)){
                            $content = implode(',', $content);
                        }
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
                                $relation,
                                (isset($field['meta']) ? $field['meta'] : null)
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
     * Gets the move success flag.
     *
     * @retval bool
     *  True if a section was moved to another list.
     */
    public function has_move_succeeded()
    {
        return $this->move_success;
    }

    /**
     * Gets the move fail flag.
     *
     * @retval bool
     *  True if moving a section to another list failed.
     */
    public function has_move_failed()
    {
        return $this->move_fail;
    }

    /**
     * Get the reason why adding, removing or moving a section link was
     * refused.
     *
     * @retval string
     *  The reason or an empty string if there is none.
     */
    public function get_link_error()
    {
        return $this->model->get_link_error();
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
