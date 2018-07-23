<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the sessions component.
 */
class SessionsView extends BaseView
{
    /* Private Properties *****************************************************/

    private $nav;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     * @param object $nav
     *  The session navigation component.
     */
    public function __construct($model, $nav)
    {
        parent::__construct($model);
        $this->nav = $nav;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the introduction text.
     */
    private function output_intro()
    {
        $paragraphs = preg_split("/\R\R+/", $this->model->get_db_field('text'));
        foreach($paragraphs as $text)
            require __DIR__ . "/tpl_paragraph.php";
    }

    /**
     * Render the session navigation component.
     */
    private function output_nav()
    {
        $this->nav->output_content();
    }

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
     * Render the login view.
     */
    public function output_content()
    {
        $count = 0;
        $count_max = $this->nav->get_count();
        $progress = round($count / $count_max * 100);
        $next_url = "#";
        $title = $this->model->get_db_field('title');
        $progress_label = $this->model->get_db_field('progress_label');
        $continue_label = $this->model->get_db_field('continue_label');
        require __DIR__ . "/tpl_sessions.php";
    }
}
?>
