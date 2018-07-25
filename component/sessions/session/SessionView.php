<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../../style/BaseStyleComponent.php";

/**
 * The view class of the session component.
 */
class SessionView extends BaseView
{
    /* Private Properties *****************************************************/

    private $nav;
    private $button;
    private $css_includes;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     * @param object $nav
     *  The session navigation component.
     */
    public function __construct($model, $nav)
    {
        parent::__construct($model);
        $this->nav = $nav;
        $this->add_button_component("button_next",
            $this->model->get_next_label(),
            $this->get_button_url($this->nav->get_next_id())
        );
        $this->add_button_component("button_back",
            $this->model->get_back_label(),
            $this->get_button_url($this->nav->get_previous_id())
        );

    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    private function output_nav()
    {
        $this->nav->output_content();
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
            new BaseStyleComponent("button"),
            array("label" => $label, "url" => $url));
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
        $title = $this->model->get_title();
        require __DIR__ . "/tpl_session.php";
    }
}
?>
