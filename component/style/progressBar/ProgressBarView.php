<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the alert style component.
 */
class ProgressBarView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param string $type
     *  The background type. This can be anything offered by bootstrap (e.g.
     *  success, warning, primary, etc.)
     */
    public function __construct($model, $type)
    {
        $this->type = $type;
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the progressbar label.
     *
     * @param int $count
     *  The number of reviewed sessions.
     * @param int $max_count
     *  The session count.
     */
    private function output_progress_label($count, $count_max)
    {
        if($count == 0) return;
        require __DIR__ . "/tpl_progress_label.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $count = 0;
        $count = $this->model->get_db_field('count');
        $count_max = $this->model->get_db_field('count_max');
        $progress = round($count / $count_max * 100);
        $type = "bg-" . $this->type;
        require __DIR__ . "/tpl_progress.php";
    }
}
?>
