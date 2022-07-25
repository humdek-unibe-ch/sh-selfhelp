<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
spl_autoload_register(function ($class_name) {
    $folder = str_replace("Component", "", $class_name);
    $folder = lcfirst(str_replace("View", "", $folder));
    $folder = lcfirst(str_replace("Init", "", $folder));
    $file = "/" . $folder . "/" . $class_name . ".php";
    if (file_exists(__DIR__ . "/style" . $file))
        require_once __DIR__ . "/style" . $file;
    else if (file_exists(__DIR__ . $file))
        require_once __DIR__ . $file;
    else {
        // check plugins
        if($handle = opendir(PLUGIN_SERVER_PATH)) {
            while(false !== ($dir = readdir($handle))){                
                if(filetype(PLUGIN_SERVER_PATH . '/' . $dir) == "dir"){                    
                    $plugin_path = __DIR__ . '/../plugins/' . $dir . '/server/component';
                    if (file_exists($plugin_path . "/style" . $file)) {
                        require_once $plugin_path . "/style" . $file;
                        break;
                    } else if (file_exists($plugin_path . $file)) {
                        require_once $plugin_path . $file;
                        break;
                    } else if (file_exists($plugin_path . '/' . $class_name . '.php')) {
                        require_once $plugin_path . '/' . $class_name . '.php';
                        break;
                    }
                }
            }
        }
    }
});
/**
 * The class to define the basic functionality of a component.
 */
abstract class BaseComponent
{
    /* Private Properties *****************************************************/

    /**
     * The view instance of the component.
     */
    protected $view;

    /**
     * The controller instance of the component.
     */
    protected $controller;

    /**
     * The model instance of the component.
     */
    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor requires the view instance of a component to iprovide a
     * function to render the output of the component. It further requires the
     * view and the controller instance to include the necessary css and js
     * files.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $view
     *  The view instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $view, $controller=null)
    {
        $this->view = $view;
        $this->controller = $controller;
        $this->model = $model;
    }

    /* Protected Methods ******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    public function output_content()
    {
        if($this->view){
            if (method_exists($this->view, 'output_debug')) {
                $this->view->output_debug();
            }
            if (
                method_exists($this->model, 'get_condition_result') &&
                !$this->model->get_condition_result()['result']
                && $this->model->get_style_name() != "conditionalContainer"
                && !(method_exists($this->model, 'is_cms_page_editing') && $this->model->is_cms_page_editing())
            ) {
                //condition not meat, do not load unless it is conditional container. Conditional container could have a child conditionFailed
                // load in CMS edit mode but not if it is in cms view mode
                return;
            }
            if (method_exists($this->model, 'is_cms_page') && $this->model->is_cms_page() && 
            method_exists($this->model, 'is_cms_page_editing') && $this->model->is_cms_page_editing() && 
                method_exists($this->view, 'output_style_for_cms') && $this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
                // load the page in the CMS 
                // wrap each style in UI CMS Holder that keep the information for the style
                $params = $this->model->get_params();
                if (isset($params['missing']) && $params['missing']) {
                    $this->view->output_content();
                } else {
                    $this->view->output_style_for_cms();
                }                                
            } else {
                $this->view->output_content();
            }
        }
    }

    /**
     * Render the component view for mobile
     */
    public function output_content_mobile()
    {
        if ($this->view) {
            if (
                method_exists($this->model, 'get_condition_result') &&
                !$this->model->get_condition_result()['result']
                && $this->model->get_style_name() != "conditionalContainer"
            ) {
                //condition not meat, do not load unless it is conditional container. Conditional container could have a child conditionFailed
                return;
            }
            return $this->view->output_content_mobile();
        }
    }

    /**
     * Get css include files required for this component. Extensions of this
     * class ought to override this method. By default, a component includes no
     * css files.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of css include files the component requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_css_includes($local = array())
    {
        if($this->view == null) return $local;
        return array_merge($local, $this->view->get_css_includes());
    }

    /**
     * Get js include files required for this component. Extensions of this
     * class ought to override this method. By default, a component includes no
     * js files.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of js include files the component requires. If no overridden,
     *  an empty array is returned.
     */
    public function get_js_includes($local = array())
    {
        if($this->view == null) return $local;
        return array_merge($local, $this->view->get_js_includes());
    }

    /**
     * Gets all the child components of this component.
     *
     * @return array
     *  A key => value array of components.
     */
    public function get_children()
    {
        if($this->model == null) return array();
        return $this->model->get_children();
    }

    /**
     * Returns the view instance of this component.
     *
     * @retval object
     *  The view instance of this component.
     */
    public function get_view()
    {
        return $this->view;
    }
	
	/**
     * Returns the model instance of this component.
     *
     * @retval object
     *  The model instance of this component.
     */
    public function get_model()
    {
        return $this->model;
    }
	
	/**
     * Returns the controller instance of this component.
     *
     * @retval object
     *  The controller instance of this component.
     */
    public function get_controller()
    {
        return $this->controller;
    }

    /**
     * Always returns true. A component extending the base component should
     * overwrite this method if invalid url parameters are passed.
     *
     * @retval bool
     *  True
     */
    public function has_access()
    {
        return true;
    }
}
?>
