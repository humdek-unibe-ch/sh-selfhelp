<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the progress bar style component.
 */
class ProgressBarView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'count' (0).
     * The current state of the progress bar. $count : $count_max defines the
     * percentage at which the progress bar stands.
     */
    private $count;

    /**
     * DB field 'count_max' (1).
     * Corresponds to 100% of the progress bar value.
     */
    private $count_max;

    /**
     * DB field 'type' ('primary').
     * The style of the progress bar. E.g. 'warning', 'danger', etc.
     */
    private $type;

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
        $this->count = $this->model->get_db_field('count', 0);
        $this->count_max = $this->model->get_db_field('count_max', 1);
        $this->type = $this->model->get_db_field("type", "primary");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the progressbar label.
     */
    private function output_progress_label()
    {
        if($this->count == 0) return;
        require __DIR__ . "/tpl_progress_label.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $progress = round($this->count / $this->count_max * 100);
        require __DIR__ . "/tpl_progress.php";
    }
}
?>
