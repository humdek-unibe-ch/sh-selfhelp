<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the navigation accordion component.
 */
class NavigationAccordionView extends BaseView
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
     * DB style field 'root_name' ("Intro").
     * The label of the root navigation item.
     */
    private $root_name;

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
        $this->root_name = $this->model->get_db_field("root_name", "Intro");
        $this->has_navigation_buttons =
            $this->model->get_db_field("has_navigation_buttons", false);

        if($this->has_navigation_buttons)
        {
            $this->add_button_component("button_next", $this->label_next,
                $this->get_button_url($model->get_next_nav_id()));
            $this->add_button_component("button_back", $this->label_back,
                $this->get_button_url($model->get_previous_nav_id()));
        }
        $this->add_local_component("nav",
            new BaseStyleComponent("accordionList", array(
                "items" => $this->model->get_navigation_items(),
                "title_prefix" => $this->model->get_item_prefix(),
                "id_active" => $this->model->get_current_id(),
                "is_expanded" => false,
                "root_name" => $this->root_name,
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    private function output_nav()
    {
        $this->output_local_component("nav");
    }

    /**
     * Create and return the url for a session, given the session id.
     *
     * @param int $id
     *  The session id.
     * @retval string
     *  The generated url.
     */
    private function get_button_url($id)
    {
        if($id == false) return "";
        else return $this->model->get_link_url("session", array("id" => $id));
    }

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
