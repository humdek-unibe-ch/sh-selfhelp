<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the sessions component.
 */
class SessionsView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The title of the style.
     */
    private $title;

    /**
     * DB field 'text' (empty string).
     * The text that is rendered in th ejumbotron below the title.
     */
    private $text;

    /**
     * DB field 'progress_label' (empty string).
     * The label to the left of the progress bar.
     */
    private $progress_label;

    /**
     * DB field 'continue_label' (empty string).
     * The label of the continue button.
     */
    private $continue_label;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->title = $this->model->get_db_field('title');
        $this->text = $this->model->get_db_field('text_md');;
        $this->progress_label = $this->model->get_db_field('progress_label');
        $this->continue_label = $this->model->get_db_field('continue_label');

        $this->add_local_component("progress",
            new BaseStyleComponent("progressBar", array(
                "count" => 0,
                "count_max" => $this->model->get_count(),
                "type" => "primary"
            ))
        );
        $this->add_local_component("nav",
            new BaseStyleComponent("accordionList", array(
                "items" => $this->model->get_navigation_items(),
                "title_prefix" => $this->model->get_item_prefix(),
                "id_active" => 0,
                "is_expanded" => false,
                "root_name" => "Intro"
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    private function output_nav()
    {
        $this->output_local_component("nav");
    }

    /**
     * Render the progressbar label.
     *
     * @param int $count
     *  The number of reviewed sessions.
     * @param int $max_count
     *  The session count.
     */
    private function output_progress_bar()
    {
        $this->output_local_component("progress");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $next_url = "#";
        require __DIR__ . "/tpl_sessions.php";
    }
}
?>
