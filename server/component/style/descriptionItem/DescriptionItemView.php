<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the description item style component.
 * This style component is currently only used for internal purposes and is not
 * made available vie the CMS.
 */
class DescriptionItemView extends StyleView
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
     * DB field 'gender' (empty string).
     * The target gendre for the content of the field.
     */
    private $gender;

    /**
     * DB field 'alt' (empty string).
     * The text that is displayed if no children are defined.
     */
    private $alt;

    /**
     * DB field 'type_input' (empty string).
     * The field type.
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
        $this->title = $this->model->get_db_field("title");
        $this->locale = $this->model->get_db_field("locale", "all");
        $this->gender = $this->model->get_db_field("gender");
        $this->alt = $this->model->get_db_field("alt");
        $this->type = $this->model->get_db_field("type_input");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the content of a field.
     */
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

    /**
     * Render the field type.
     */
    private function output_type()
    {
        if($this->type == "") return;
        require __DIR__ . "/tpl_type.php";
    }

    /**
     * Render the locale string and the gender string.
     */
    private function output_small_text()
    {
        if($this->locale == "" && $this->gender == "") return;
        if($this->gender == "")
            require __DIR__ . "/tpl_locale.php";
        else
            require __DIR__ . "/tpl_locale_gender.php";
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
