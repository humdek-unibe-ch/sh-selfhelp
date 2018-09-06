<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The base view class of a navigation component.
 */
abstract class NavigationView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB style field 'next'.
     * The label of the navigation button to go to the next item.
     */
    private $label_next;

    /**
     * DB style field 'back'.
     * The label of the navigation button to go to the pervious item.
     */
    private $label_back;

    /**
     * DB style field 'has_navigation_buttons' (false).
     * If set to true the navigation buttons back and next are rendered. If set
     * to false the buttons are omitted.
     */
    private $has_navigation_buttons;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->label_next = $this->model->get_db_field("next");
        $this->label_back = $this->model->get_db_field("back");
        if($this->css == null)
            $this->css = "my-3";
        $this->has_navigation_buttons =
            $this->model->get_db_field("has_navigation_buttons", false);

        if($this->has_navigation_buttons)
        {
            $this->add_button_component("button_next", $this->label_next,
                $this->model->get_next_nav_url());
            $this->add_button_component("button_back", $this->label_back,
                $this->model->get_previous_nav_url());
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    abstract protected function output_nav();

    /**
     * Add a button component to the local component list.
     *
     * @param string $name
     *  The button style type.
     * @param string $label
     *  The label of the button.
     * @param string $url
     *  The url of the button.
     */
    private function add_button_component($name, $label, $url)
    {
        $this->add_local_component($name,
            new BaseStyleComponent("button",
                array("label" => $label, "url" => $url)));
    }

    /**
     * Render a button if the url is not empty.
     *
     * @param string $label
     *  The label of the button.
     * @param string $url
     *  The url of the button.
     */
    private function output_button($name)
    {
        $this->output_local_component($name);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $button_next = "button_next";
        $button_back = "button_back";
        require __DIR__ . "/tpl_nav.php";
    }
}
?>
