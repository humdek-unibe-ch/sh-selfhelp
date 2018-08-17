<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the description item style component.
 */
class DescriptionItemView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The title of the field. If it is left empty, the filed is not rendered.
     */
    private $title;

    /**
     * DB field 'locale' ('all').
     * The language abbreviation of the language the content of the field.
     */
    private $locale;

    /**
     * DB style field 'alt' (empty string).
     * The text that is displayed if no children are defined.
     */
    private $alt;

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
        $this->title = $this->model->get_db_field("title");
        $this->locale = $this->model->get_db_field("locale", "all");
        $this->alt = $this->model->get_db_field("alt");
    }

    /* Private Methods ********************************************************/

    private function output_field_content()
    {
        if($this->has_children())
            $this->output_children();
        else
        {
            $na = $this->alt;
            require __DIR__ . "/tpl_item_na.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->title == "") return;
        $border = "border-top";
        require __DIR__ . "/tpl_item.php";
    }
}
?>
