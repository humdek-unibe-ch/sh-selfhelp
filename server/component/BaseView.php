<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
/**
 * The class to define the basic functionality of a view.
 */
abstract class BaseView
{
    /* Private Properties *****************************************************/

    /**
     * The model instance of the component.
     */
    protected $model;

    /**
     * The controller instance of the component.
     */
    protected $controller;

    /**
     * The list of local components. These components where produced
     * programmatically (not loaded from the db)
     */
    private $local_components;

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
        $this->model = $model;
        $this->controller = $controller;
        $this->local_components = array();
    }

    /* Protected Methods ******************************************************/

    /**
     * Add a component to the local component list. Thes components were
     * instantiated inside this view.
     *
     * @param string $name
     *  The name of the component.
     * @param object $component
     *  A component object.
     */
    protected function add_local_component($name, $component)
    {
        $this->local_components[$name] = $component;
    }

    /**
     * Get a local component given a component name.
     *
     * @param string $name
     *  The name of the component.
     * @retval object
     *  A component object.
     */
    protected function output_local_component($name)
    {
        $component = $this->get_local_component($name);
        if ($component != null)
            $component->output_content();
    }

    /**
     * Get a local component given a component name.
     *
     * @param string $name
     *  The name of the component.
     * @retval object
     *  A component object.
     */
    protected function get_local_component($name)
    {
        if (array_key_exists($name, $this->local_components))
            return $this->local_components[$name];
        else
            return null;
    }

    /**
     * Render the fail alerts of the controller.
     * @param boolean $wrap_in_container 
     * Default value is false, if enabled the alert will be wrapped in div with class container
     */
    protected function output_controller_alerts_fail($wrap_in_container = false)
    {
        if ($this->controller === null) return;
        if (!$this->controller->has_failed()) return;
        $children_alerts = array();
        foreach ($this->controller->get_error_msgs() as $idx => $msg) {
            $alert = new BaseStyleComponent("alert", array(
                "id" => "controller-fail-" . $idx,
                "type" => "danger",
                "is_dismissable" => true,
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $msg,
                )))
            ));
            if ($wrap_in_container) {
                $children_alerts[] = $alert;
            } else {
                $alert->output_content();
            }
        }
        if ($wrap_in_container) {
            $wrapper = new BaseStyleComponent("div", array(
                "css" => 'container my-3',
                "children" => $children_alerts
            ));
            $wrapper->output_content();
        }
    }

    /**
     * Render the fail alerts of the controller.
     */
    protected function output_controller_alerts_fail_mobile()
    {
        if ($this->controller === null) return;
        if (!$this->controller->has_failed()) return;
        return $this->controller->get_error_msgs();
    }

    /**
     * Render the fail alerts of the controller.
     * @param boolean $wrap_in_container 
     * Default value is false, if enabled the alert will be wrapped in div with class container
     */
    protected function output_controller_alerts_success($wrap_in_container = false)
    {
        if ($this->controller === null) return;
        if (!$this->controller->has_succeeded()) return;
        $children_alerts = array();
        foreach ($this->controller->get_success_msgs() as $idx => $msg) {
            $alert = new BaseStyleComponent("alert", array(
                "id" => "controller-success-" . $idx,
                "type" => "success",
                "is_dismissable" => true,
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $msg,
                )))
            ));
            if ($wrap_in_container) {
                $children_alerts[] = $alert;
            } else {
                $alert->output_content();
            }
        }
        if ($wrap_in_container) {
            $wrapper = new BaseStyleComponent("div", array(
                "css" => 'container my-3',
                "children" => $children_alerts
            ));
            $wrapper->output_content();
        }
    }

    /**
     * Render the fail alerts of the controller.
     */
    protected function output_controller_alerts_success_mobile()
    {
        if ($this->controller === null) return;
        if (!$this->controller->has_succeeded()) return;
        return $this->controller->get_success_msgs();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    abstract public function output_content();

    /**
     * Render the component view for mobile.
     */
    abstract public function output_content_mobile();

    /**
     * Get css include files required for this view. By default the css files of
     * the children of a section are included.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of css include files the view requires. If no overridden, an
     *  empty array is returned.
     */
    public function get_css_includes($local = array())
    {
        return $local;
    }

    /**
     * Get js include files required for this view. By default the js files of
     * the children of a section are included.
     *
     * @param array $local
     *  An array of inlcude files that can be passed from a class implementing
     *  this base class.
     * @retval array
     *  An array of js include files the view requires. If no overridden, an
     *  empty array is returned.
     */
    public function get_js_includes($local = array())
    {
        return $local;
    }

    /**
     *  get lookups by types.
     *  @param string $type
     *  The type of the lookup
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_lookups($type)
    {
        $arr = array();
        foreach ($this->model->get_services()->get_db()->get_lookups($type) as $val) {
            array_push($arr, array("value" => intval($val['id']), "text" => $val['lookup_value']));
        }
        return $arr;
    }

    /**
     *  get lookups by types.
     *  @param string $type
     *  The type of the lookup
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_lookups_with_code($type)
    {
        $arr = array();
        foreach ($this->model->get_services()->get_db()->get_lookups($type) as $val) {
            array_push($arr, array("value" => $val['lookup_code'], "text" => $val['lookup_value']));
        }
        return $arr;
    }

    /**
     * render the app version
     */
    public function get_app_version()
    {
        echo $this->model->get_services()->get_db()->get_git_version(__DIR__);
    }

    /**
     * render the db version
     */
    public function get_db_version()
    {
        echo $this->model->get_services()->get_db()->query_db_first('SELECT version FROM version')['version'];
    }

    /**
     * Check and output an alert for the multiple users editing the same page
     * @param boolean $return_component
     * If true, the function will return the component instead of outputting it. Default value is false
     * @return object | void
     * If a value is returned, it returns the alert
     */
    public function output_check_multiple_users($return_component = false)
    {
        $users = $this->model->get_services()->get_router()->get_other_users_editing_this_page();
        if ($users) {
            $user_emails = array();
            foreach ($users as $key => $value) {
                $user_emails[] = "[" . $value['email'] . "]";
            }
            $alert = new BaseStyleComponent("alert", array(
                "type" => "danger",
                "id" => "multiple-users-warning-alert",
                "is_dismissable" => false,
                "children" => array(
                    new BaseStyleComponent(
                        "markdown",
                        array(
                            "text_md" => "<div class = 'd-flex justify-content-between'><div>Multiple people are editing this page and you might impact each other's changes!</div> <div><i class='fas fa-users'></i> " . implode(", ", $user_emails) . "</div></div>"
                        )
                    )
                )
            ));
            if ($return_component) {
                return $alert;
            }
            $alert->output_content();
        }
        if ($return_component) {
            // return empty div
            return new BaseStyleComponent("div", array("id" => "multiple-users-warning-div"));
        }
    }

    /**
     * Output missing entry
     */
    public function output_missing()
    {
        $sections = $this->model->get_services()->get_db()->fetch_page_sections('missing');
        foreach ($sections as $section) {
            $missing_styles =  new StyleComponent($this->model->get_services(), intval($section['id']));
            $missing_styles->output_content();
        }
    }
}
?>