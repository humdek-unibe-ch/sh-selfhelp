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

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance that is used to provide the view with data.
     * @param object $controller
     *  The controler instance that is used to handle user interaction.
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
                $this->id_section = $model->get_db_field("id", null);
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
            require __DIR__ . "/tpl_style_children_holder_ui_cms.php";
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
            echo '<pre class="alert alert-warning">';
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
        // prepare the remove id if the style is in page --> this will be checked in javascript
        $style_from_page_url = $this->model->get_link_url("cmsUpdate", array(
            "pid" => isset($params['pid']) ? $params['pid'] : -1,
            "mode" => "update",
            "type" => "prop",
            "did" => null
        ));
        // prepare the remove id if the style is in another style --> this will be checked in javascript
        $style_from_style_url = $this->model->get_link_url("cmsUpdate", array(
            "pid" => isset($params['pid']) ? $params['pid'] : -1,
            "mode" => "update",
            "type" => "prop",
            "did" => null,
            "sid" => ":parent_id"
        ));
        $data_section = array(
            'can_have_children' => $this->model->can_have_children(),
            'id_sections' => $this->id_section,
            'id_pages' => isset($params['pid']) ? $params['pid'] : -1,
            'section_from_page_url' => $style_from_page_url,
            'section_from_style_url' => $style_from_style_url,
            'section_name' => $this->model->get_section_name()
        );
        require __DIR__ . "/tpl_style_holder_ui_cms.php";
    }

}
?>
