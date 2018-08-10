<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the Title style component.
 * A title style supports the following fields:
 *  'text':
 *      The text of the title to be rendered.
 *  'level':
 *      The level of the title, i.e. a number in the interval [1, 6]
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
        $text = $this->model->get_db_field("title");
        $level = $this->model->get_db_field("level");
        if($level == "") $level = 1;
        if($level < 1) $level = 1;
        if($level > 6) $level = 6;
        require __DIR__ . "/tpl_title.php";
    }
}
?>
