<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The class to define the basic functionality of a style view.
 */
abstract class StyleView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'css' (null)
     * This field can hold a list of comma seperated css classes. These css
     * classes will be assigned to style wrapper element.
     */
    protected $css;

    /**
     * DB field 'css_mobile' (null)
     * This field can hold a list of comma seperated css classes. These css
     * classes will be assigned to style wrapper element.
     */
    protected $css_mobile;

    /**
     * DB field 'id' (null)
     * The id of the section.
     */
    protected $id_section;

    /**
     * The name of the style.
     */
    protected $style_name;

    /**
     * The list of child components. These components where loaded from the db.
     */
    protected $children;

    /**
     * The list of all fields for the style, with their id, value and default value
     */
    protected $fields;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance that is used to provide the view with data.
     * @param object $controller
     *  The controller instance that is used to handle user interaction.
     */
    public function __construct($model = null, $controller = null)
    {
        parent::__construct($model, $controller);
        $this->style_name = $model->get_style_name();
        $this->children = array();
        if($model != null)
        {
            $this->children = $model->get_children();            
            if(method_exists($model, "get_db_field"))
            {
                $this->css = $model->get_db_field("css", null);
                $this->css_mobile = $model->get_db_field("css_mobile", null);
                $this->fields = $model->get_db_fields();
                $this->id_section = $model->get_db_field("id", null);
                $this->css = $this->css . " selfHelp-locale-" . $_SESSION['user_language_locale'];
                if($this->id_section !== null)
                {
                    $this->css .= " style-section-" . $this->id_section;
                    if($this->id_section === $_SESSION['active_section_id'])
                        $this->css .= " highlight";
                }
            }
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Checks whether the children array is empty or not.
     *
     * @retval bool
     *  True if there is at least one child, false otherwise.
     */
    protected function has_children()
    {
        return (count($this->children) > 0);
    }

    /**
     * Render the content of all children of this view instance.
     */
    protected function output_children()
    {
        if (method_exists($this->model, "is_cms_page") && $this->model->is_cms_page() &&
            method_exists($this->model, "is_cms_page_editing") && $this->model->is_cms_page_editing() &&
            $this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            $params = $this->model->get_params();
            if (isset($params['missing']) && $params['missing']) {
                $this->output_children_content();
            } else {
                require __DIR__ . "/tpl_style_children_holder_ui_cms.php";
            }             
        } else {
            $this->output_children_content();
        }        
    }

     /**
     * Render the content of all children of this view instance.
     */
    protected function output_children_content()
    {
        foreach($this->children as $child)
            if($child instanceof StyleComponent
                    || $child instanceof BaseStyleComponent)
                $child->output_content();
            else
                echo "invalid child element of type '" . gettype($child) . "'";
    }


    /**
     * Render the content of all children of this view instance.
     */
    protected function output_children_mobile()
    {
        $res = [];
        foreach ($this->children as $child) {
            if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                $res[] = $child->output_content_mobile();
            } else {
                echo "invalid child element of type '" . gettype($child) . "'";
            }
        }
        return $res;
    }


    /**
     * Render the debug information
     */
    public function output_debug()
    {
        $debug = $this->model->get_db_field('debug', false);
        if ($debug) {
            $res = $this->model->get_condition_result();
            $debug_data = $this->model->get_debug_data();
            echo '<pre class="alert alert-warning data-debug" data-debug="' . htmlspecialchars(json_encode($debug_data), ENT_QUOTES, 'UTF-8'). '">';
            var_dump($res);
            echo "</pre>";
        }
    }

    /**
     * Render the component view for mobile.
     */
    public function output_content_mobile()
    {
        $style = $this->model->get_db_fields();
        $style['style_name'] = $this->style_name;
        $style['css'] = $this->css;
        $style['children'] = $this->output_children_mobile();
        $success_msgs = $this->output_controller_alerts_success_mobile();
        if($success_msgs){
            $style['success_msgs'] = $success_msgs;
        }
        $fail_msgs = $this->output_controller_alerts_fail_mobile();
        if($fail_msgs){
            $style['fail_msgs']  = $fail_msgs;
        }
        return $style;
    }

    /**
     * Render the style in holder for the CMS UI
     * and prepare the needed data as data-style attribute in order to be used in javascript
     */
    public function output_style_for_cms()
    {
        $params = $this->model->get_services()->get_router()->route['params'];
        $style_from_page_url = $this->model->get_link_url("cmsUpdate", array(
            "pid" => isset($params['pid']) ? $params['pid'] : -1,
            "mode" => "update",
            "type" => "prop",
            "did" => null
        ));
        $style_from_style_url = $this->model->get_link_url("cmsUpdate", array(
            "pid" => isset($params['pid']) ? $params['pid'] : -1,
            "mode" => "update",
            "type" => "prop",
            "did" => null,
            "sid" => $this->model->get_parent_id()            
        ));      
        $go_to_section_url_params = array();
        foreach ($params as $key => $value) {
            if($value){
                $go_to_section_url_params[$key] = $value;
            }
        }
        if ($_SESSION['active_section_id'] != $this->id_section) {
            // add extra params for navigation container children
            $go_to_section_url_params['ssid'] = $this->id_section;
        }
        $go_to_section_url = $this->model->get_link_url("cmsUpdate", $go_to_section_url_params);
        $insert_sibling_section = $this->model->get_link_url("cmsUpdate", array(
            "pid" => isset($params['pid']) ? $params['pid'] : -1,
            "mode" => "insert",
            "type" => RELATION_SECTION_CHILDREN,
            "did" => null,
            "sid" => $this->model->get_parent_id()
        ));
        $insert_section_in_page = $this->model->get_link_url("cmsUpdate", array(
            "pid" => isset($params['pid']) ? $params['pid'] : -1,
            "mode" => "insert",
            "type" => RELATION_PAGE_CHILDREN,
            "did" => null
        ));
        $data_section = array(
            'can_have_children' => $this->model->can_have_children(),
            'id_sections' => $this->id_section,
            'id_pages' => intval(isset($params['pid']) ? $params['pid'] : -1),
            'update_page_url' => $style_from_page_url,
            'update_section_url' => $style_from_style_url,  
            'update_url' => $this->model->get_relation() == RELATION_PAGE_CHILDREN ? $style_from_page_url : $style_from_style_url,
            'section_name' => $this->model->get_section_name(),
            'insert_sibling_section_url' => $insert_sibling_section,
            'insert_section_in_page' => $insert_section_in_page,
            'go_to_section_url' => $go_to_section_url,
            'parent_id' => $this->model->get_parent_id(),
            'relation' => $this->model->get_relation(),
            'params' => $params,
            'children' => count($this->children)
        );
        if ($data_section['can_have_children']) {
            $insert_section_in_section = $this->model->get_link_url("cmsUpdate", array(
                "pid" => isset($params['pid']) ? $params['pid'] : -1,
                "mode" => "insert",
                "type" => RELATION_SECTION_CHILDREN,
                "did" => null,
                "sid" => $this->id_section
            ));
            $data_section['insert_section_url'] = $insert_section_in_section;
        }
        require __DIR__ . "/tpl_style_holder_ui_cms.php";
    }

    /**
     * get the view children
     * @return array
     * Return an array with the children
     */
    public function get_children(){
        return $this->children;
    }

    /**
     * Set the view children
     * @param array $children
     * the children array
     */
    public function set_children($children){
        $this->children = $children;
    }

    /**
     * Get the field value if there is no field value, return the default one
     * @param string $field_name
     * The name of the field
     * @return string
     * Return the value of the field if there is no value, return the default value which is configured
     */
    public function get_field_value($field_name){
        return $this->fields[$field_name]['content'] ? $this->fields[$field_name]['content'] : $this->fields[$field_name]['default'];
    }

    /**
     * Updates the view children based on user input changes.
     *
     * This function checks if there is a user input change through the model's user input. 
     * If a change is detected, it reloads the children of the view by loading the entry record 
     * and setting the updated children.
     */
    public function update_children()
    {
        if ($this->model->get_user_input()->is_there_user_input_change()) {
            $this->model->loadChildren($this->model->get_entry_record());
            $this->set_children($this->model->get_children());
        }
    }

}
?>
