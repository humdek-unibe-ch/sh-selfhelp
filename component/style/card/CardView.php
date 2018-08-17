<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the card style component.
 * A card style supports the following fields:
 *  'title' (no header):
 *      The title of the card, displayed in a card-header. If this is not set
 *      or set to the empty string the card header is omitted.
 *  'children' (empty array):
 *      A list of style components to be displayed in the card body.
 *  'is_expanded' (true):
 *      If set to true and the card is collapsible, it is expanded by
 *      default. If set to false the card is collapsed by default.
 *  'is_collapsible' (false):
 *      If set to true, the card is collapsible.
 *  'type' ('light'):
 *      The style of the card. E.g. 'warning', 'danger', etc., The default is
 *      'light'.
 */
class CardView extends BaseView
{
    /* Private Properties *****************************************************/

    private $fluid;
    private $is_collapsible;
    private $is_expanded;

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
        $this->is_expanded = $this->model->get_db_field("is_expanded", true);
        $this->is_collapsible = $this->model->get_db_field("is_collapsible",
            false);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the card header if a title is set. The card can be collapsible
     * or always open.
     */
    private function output_card_header()
    {
        $show = $this->is_expanded ? "" : "collapsed";
        $title = $this->model->get_db_field("title");
        if($title == "") return;
        $collapsible = $this->is_collapsible ? "collapsible" : "";
        require __DIR__ . "/tpl_card_header.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/card.css");
        return parent::get_css_includes($local);
    }

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
        $type = $this->model->get_db_field("type", "light");
        $fluid = ($this->fluid) ? "-fluid" : "";
        $show = $this->is_expanded ? "show" : "";
        $collapse = $this->is_collapsible ? "collapse" : "";
        require __DIR__ . "/tpl_card.php";
    }
}
?>
