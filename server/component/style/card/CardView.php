<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the card style component.
 * Cards are special visual containers which with an optional heading and useful
 * configuartion settings such as making the card collpsible.
 */
class CardView extends StyleView
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
     * DB field 'url_edit' (empty string).
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

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->is_expanded = $this->model->get_db_field("is_expanded", true);
        $this->is_collapsible = $this->model->get_db_field("is_collapsible",
            false);
        $this->url_edit = $this->model->get_db_field("url_edit");
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
        $show = ($this->is_expanded || !$this->is_collapsible) ? "" : "collapsed";
        if($this->title == "") return;
        $collapsible = $this->is_collapsible ? "collapsible" : "";
        require __DIR__ . "/tpl_card_header.php";
    }

    /**
     * Render the edit button.
     */
    private function output_edit_button()
    {
        if($this->url_edit != "")
            require __DIR__ . "/tpl_edit_button.php";
    }

    /**
     * Render the expand icon.
     */
    private function output_expand_icon()
    {
        if($this->is_collapsible)
        {
            $direction = $this->is_expanded ? "up" : "down";
            require __DIR__ . "/tpl_expand_icon.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $show = $this->is_expanded ? "show" : "";
        $collapse = $this->is_collapsible ? "collapse" : "";
        require __DIR__ . "/tpl_card.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
