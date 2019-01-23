<?php
require_once __DIR__ . "/ChatView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the chat component if the current user is a therapist.
 * The chat component is not made available to the CMS and is only used
 * internally.
 */
class ChatViewTherapist extends ChatView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'subjects' (empty string)
     * The text to be displayed when addressing subjects.
     */
    private $subjects;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user profile component.
     * @param object $controller
     *  The controller instance of the user profile component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->subjects = $this->model->get_db_field("subjects");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the subject list.
     */
    private function output_subjects()
    {
        foreach($this->model->get_subjects() as $subject)
        {
            $id = intval($subject['id']);
            $name = $subject['name'];
            $url = $this->model->get_link_url("contact", array("uid" => $id));
            $active = "";
            if($this->model->is_selected_id($id))
                $active = "bg-info text-white";
            require __DIR__ . "/tpl_subject.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the user view.
     */
    public function output_content_spec()
    {
        $title = $this->title_prefix . " "
            . $this->model->get_selected_user_name();
        require __DIR__ . "/tpl_chat_experimenter.php";
    }
}
?>
