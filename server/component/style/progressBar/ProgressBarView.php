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
    protected $count;

    /**
     * DB field 'count_max' (1).
     * Corresponds to 100% of the progress bar value.
     */
    protected $count_max;

    /**
     * DB field 'type' ('primary').
     * The style of the progress bar. E.g. 'warning', 'danger', etc.
     */
    private $type;

    /**
     * DB field 'is_striped' (true)
     * If set to true the progressbar appears striped.
     */
    private $is_striped;

    /**
     * DB field 'has_label' (true)
     * If set to true the progressbar indicates the current and the total value
     * as a label.
     */
    private $has_label;

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
        $this->is_striped = $this->model->get_db_field("is_striped", true);
        $this->has_label = $this->model->get_db_field("has_label", true);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the progressbar label.
     */
    private function output_progress_label()
    {
        if($this->count == 0 || !$this->has_label) return;
        require __DIR__ . "/tpl_progress_label.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $progress = round($this->count / $this->count_max * 100);
        $striped = "";
        if($this->is_striped)
            $striped = "progress-bar-striped";
        require __DIR__ . "/tpl_progress.php";
    }
}
?>
