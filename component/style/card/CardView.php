<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the card style component.
 */
class CardView extends BaseView
{
    private $fluid;
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     * @param bool $fluid
     *  If set to true the card gets the bootstrap class "container-fluid",
     *  otherwise the class "container" is used.
     */
    public function __construct($model, $fluid)
    {
        $this->fluid = $fluid;
        parent::__construct($model);
    }

    /**
     * Render the card header if a title is set. The card can be collapsible
     * or always open.
     */
    private function output_card_header()
    {
        $title = $this->model->get_db_field("title");
        if($title == "") return;
        $collapsible = $this->model->get_db_field("is_collapsible") ? "collapsible" : "";
        require __DIR__ . "/tpl_card_header.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/card.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->fluid) ? "-fluid" : "";
        $show = $this->model->get_db_field("is_expanded") ? "show" : "";
        $collapse = $this->model->get_db_field("is_collapsible") ? "collapse" : "";
        require __DIR__ . "/tpl_card.php";
    }
}
?>
