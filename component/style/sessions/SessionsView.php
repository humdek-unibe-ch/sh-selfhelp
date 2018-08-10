<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the sessions component.
 */
class SessionsView extends BaseView
{
    /* Private Properties *****************************************************/

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
        $this->add_local_component("progress",
            new BaseStyleComponent("progress", array(
                "count" => 0,
                "count_max" => $this->model->get_count(),
                "type" => "primary"
            ))
        );
        $this->add_local_component("nav",
            new BaseStyleComponent("accordion_list", array(
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
        $title = $this->model->get_db_field('title');
        $text = $this->model->get_db_field('text_markdown');;
        $next_url = "#";
        $progress_label = $this->model->get_db_field('progress_label');
        $continue_label = $this->model->get_db_field('continue_label');
        require __DIR__ . "/tpl_sessions.php";
    }
}
?>
