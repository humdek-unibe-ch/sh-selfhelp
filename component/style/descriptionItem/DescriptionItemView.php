<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the description item style component.
 * The following fields are required
 * 'title': The title of the field.
 * 'locale': The language abbreviation of the language the content of the field.
 * 'children': The components to be rendered as content of this field.
 */
class DescriptionItemView extends BaseView
{
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
    }

    /* Private Methods ********************************************************/

    private function output_field_content()
    {
        if($this->has_children())
            $this->output_children();
        else
        {
            $na = $this->model->get_db_field("alt");
            require __DIR__ . "/tpl_item_na.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $border = "border-top";
        $name = $this->model->get_db_field("title");
        $locale = $this->model->get_db_field("locale");
        require __DIR__ . "/tpl_item.php";
    }
}
?>
