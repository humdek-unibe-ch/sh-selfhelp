<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The base view class of a navigation component.
 * This class provides common fiunctionallity that is used by navigation styles.
 */
abstract class NavigationView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label_next' (empty string).
     * The label of the navigation button to go to the next item.
     */
    private $label_next;

    /**
     * DB field 'label_back' (empty string).
     * The label of the navigation button to go to the pervious item.
     */
    private $label_back;

    /**
     * DB style field 'has_navigation_buttons' (false).
     * If set to true the navigation buttons back and next are rendered. If set
     * to false the buttons are omitted.
     */
    private $has_navigation_buttons;

    /**
     * DB style field 'has_navigation_menu' (false).
     * If set to true the navigation menu on the left is rendered. If set
     * to false the menu is omitted.
     */
    private $has_navigation_menu;

    /**
     * DB field 'is_fluid' (true).
     * If set to true the container spand to whole page. If set to false the
     * container only uses a part of the page.
     */
    private $is_fluid;

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
        $this->label_next = $this->model->get_db_field("label_next");
        $this->label_back = $this->model->get_db_field("label_back");
        $this->is_fluid = $this->model->get_db_field('is_fluid', true);
        $this->has_navigation_buttons =
            $this->model->get_db_field("has_navigation_buttons", false);
        $this->has_navigation_menu =
            $this->model->get_db_field("has_navigation_menu", false);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    abstract protected function output_nav();

    /**
     * Render the navigation buttons.
     */
    private function output_buttons()
    {
        if(!$this->has_navigation_buttons) return;
        $back = new BaseStyleComponent("button", array(
            "css" => "nav-back",
            "label" => $this->label_back,
            "url" => $this->model->get_previous_nav_url()
        ));
        $next = new BaseStyleComponent("button", array(
            "css" => "nav-next",
            "label" => $this->label_next,
            "url" => $this->model->get_next_nav_url()
        ));
        $back->output_content();
        $next->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $fluid = ($this->is_fluid) ? "-fluid" : "";
        if($this->has_navigation_menu)
            require __DIR__ . "/tpl_nav.php";
        else
            require __DIR__ . "/tpl_nav_no_menu.php";
    }
}
?>
