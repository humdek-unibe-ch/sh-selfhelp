<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the container style component.
 */
class NavigationContainerView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * If set to true the container spand to whole page. If set to false the
     * container only uses a part of the page.
     */
    private $title;

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
        $this->title = $this->model->get_db_field('title');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_container.php";
    }
}
?>
