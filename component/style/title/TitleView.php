<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the Title style component.
 */
class TitleView extends BaseView
{
    /* Private Properties******************************************************/

    private $level;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param int $level
     *  The level of the title
     */
    public function __construct($model, $level)
    {
        parent::__construct($model);
        $this->level = $level;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $level = $this->level;
        $text = $this->model->get_db_field("text");
        require __DIR__ . "/tpl_title.php";
    }
}
?>
