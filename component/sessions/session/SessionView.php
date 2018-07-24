<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../../style/BaseStyleComponent.php";

/**
 * The view class of the session component.
 */
class SessionView extends BaseView
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
     * Render the session navigation component.
     */
    private function output_nav()
    {
        $this->nav->output_content();
    }

    /**
     * Render a button if the url is not empty.
     *
     * @param string $label
     *  The label of the button.
     * @param string $url
     *  The url of the button.
     */
    private function output_button($label, $url)
    {
        if($url == "") return;
        $button = new BaseStyleComponent("button",
            array("label" => $label, "url" => $url));
        $button->output_content();
    }

    /**
     * Render the content of the session view. The content is composed of child
     * sections.
     */
    private function output_section_content()
    {
        $children = $this->model->get_db_field("content");
        foreach($children as $child)
            $child->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $url_next = "";
        $next_id = $this->nav->get_next_id();
        if($next_id != false)
            $url_next = $this->model->get_link_url("session",
                array("id" => $next_id));
        $label_next = $this->model->get_next_label();;
        $url_back = "";
        $prev_id = $this->nav->get_previous_id();
        if($prev_id != false)
            $url_back = $this->model->get_link_url("session",
                array("id" => $prev_id));
        $label_back = $this->model->get_back_label();
        $title = $this->model->get_title();
        require __DIR__ . "/tpl_session.php";
    }
}
?>
