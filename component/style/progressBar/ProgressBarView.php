<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the progress bar style component.
 * A progress bar style supports the following fields:
 *  'count' (0):
 *      The current state of the progress bar. $count : $count_max defines the
 *      percentage at which the progress bar stands.
 *  'count_max' (1):
 *      Corresponds to 100% of the progress bar value.
 *  'type' ('primary'):
 *      The style of the progress bar. E.g. 'warning', 'danger', etc.,
 *      The default is 'primary'.
 */
class ProgressBarView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
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
        $count = $this->model->get_db_field('count', 0);
        $count_max = $this->model->get_db_field('count_max', 1);
        $progress = round($count / $count_max * 100);
        $type = "bg-" . $this->model->get_db_field("type", "primary");
        require __DIR__ . "/tpl_progress.php";
    }
}
?>
