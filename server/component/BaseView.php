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
        if($component != null)
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
        if(array_key_exists($name, $this->local_components))
            return $this->local_components[$name];
        else
            return null;
    }

    /**
     * Render the fail alerts of the controller.
     */
    protected function output_controller_alerts_fail()
    {
        if($this->controller === null) return;
        if(!$this->controller->has_failed()) return;
        foreach($this->controller->get_error_msgs() as $msg)
        {
            $alert = new BaseStyleComponent("alert", array(
                "type" => "danger",
                "is_dismissable" => true,
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $msg,
                )))
            ));
            $alert->output_content();
        }
    }

    /**
     * Render the fail alerts of the controller.
     */
    protected function output_controller_alerts_success()
    {
        if($this->controller === null) return;
        if(!$this->controller->has_succeeded()) return;
        foreach($this->controller->get_success_msgs() as $idx => $msg)
        {
            $alert = new BaseStyleComponent("alert", array(
                "id" => "controller-success-" . $idx,
                "type" => "success",
                "is_dismissable" => true,
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $msg,
                )))
            ));
            $alert->output_content();
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    abstract public function output_content();

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
}
?>
