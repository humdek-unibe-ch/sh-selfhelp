<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the Title style component.
 */
class TitleView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $text = $this->model->get_db_field("text");
        $level = $this->model->get_db_field("level");
        if($level == "") $level = 1;
        require __DIR__ . "/tpl_title.php";
    }
}
?>
