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
            $url = $this->model->get_subject_url($id);
            $active = "";
            if($this->model->is_subject_selected($id))
                $active = "bg-info text-white";
            require __DIR__ . "/tpl_subject.php";
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Render the chat messages. Place and color the messages dependeing on who
     * the author is.
     *
     * @param string $user
     *  The user name of the author.
     * @param string $msg
     *  The message.
     * @param int $uid
     *  The user id of the author.
     * @param string $datetime
     *  The date and time of the message.
     */
    protected function output_msgs_spec($user, $msg, $uid, $datetime)
    {
        $css = "";
        if($uid == $_SESSION['id_user'])
            $css = "me ml-auto";
        else if($this->model->is_subject_selected($uid))
            $css .= " subject";
        require __DIR__ . "/tpl_chat_item.php";
    }

    /**
     * Render the new badge.
     */
    protected function output_new_badge_subject($id)
    {
        $count = $this->model->get_subject_message_count($id);
        if($count > 0)
            require __DIR__ . "/tpl_new_badge.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the chat view of the therapist role.
     */
    public function output_content_spec()
    {
        $title = $this->title_prefix . " "
            . $this->model->get_selected_user_name();
        require __DIR__ . "/tpl_chat_experimenter.php";
    }
}
?>
