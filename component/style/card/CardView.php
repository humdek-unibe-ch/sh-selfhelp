<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the card style component.
 */
class CardView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (emty string).
     * The title of the card, displayed in a card-header. If this is not set or
     * set to the empty string the card header is omitted.
     */
    private $title;

    /**
     * DB field 'is_collapsible' (false).
     * If set to true, the card is collapsible.
     */
    private $is_collapsible;

    /**
     * DB field 'is_expanded' (true).
     * If set to true and the card is collapsible, it is expanded by default.
     * If set to false the card is collapsed by default.
     */
    private $is_expanded;

    /**
     * DB field 'url' (empty string).
     * The target url when clicking the edit button in the header. When this
     * field is set an edit button is rendered in the header. If this field
     * is not set, no edit button will be rendered.
     */
    private $url_edit;

    /**
     * DB field 'type' ('light').
     * The style of the card. E.g. 'warning', 'danger', etc.
     */
    private $type;

    /**
     * If set to true the card gets the bootstrap class "container-fluid",
     * otherwise the class "container" is used.
     */
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
        parent::__construct($model);
        $this->fluid = $fluid;
        $this->is_expanded = $this->model->get_db_field("is_expanded", true);
        $this->is_collapsible = $this->model->get_db_field("is_collapsible",
            false);
        $this->url_edit = $this->model->get_db_field("url");
        $this->title = $this->model->get_db_field("title");
        $this->type = $this->model->get_db_field("type", "light");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the card header if a title is set. The card can be collapsible
     * or always open.
     */
    private function output_card_header()
    {
        $show = $this->is_expanded ? "" : "collapsed";
        if($this->title == "") return;
        $collapsible = $this->is_collapsible ? "collapsible" : "";
        require __DIR__ . "/tpl_card_header.php";
    }

    private function output_edit_button()
    {
        if($this->url_edit != "")
            require __DIR__ . "/tpl_edit_button.php";
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
        $fluid = ($this->fluid) ? "-fluid" : "";
        $show = $this->is_expanded ? "show" : "";
        $collapse = $this->is_collapsible ? "collapse" : "";
        require __DIR__ . "/tpl_card.php";
    }
}
?>
