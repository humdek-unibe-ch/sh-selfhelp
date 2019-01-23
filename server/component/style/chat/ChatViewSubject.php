<?php
require_once __DIR__ . "/ChatView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the chat component.
 * The chat component is not made available to the CMS in is only used
 * internally.
 */
class ChatViewSubject extends ChatView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'label_rooms' (empty string)
     * The label of the chat room header.
     */
    private $label_rooms;

    /**
     * DB field 'experimenter' (empty string)
     * The text to be displayed when addressing experimenter.
     */
    private $experimenter;

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
        $this->experimenter = $this->model->get_db_field("experimenter");
    }

    /* Private Methods ********************************************************/

    protected function output_msgs_spec($user, $msg, $uid, $datetime)
    {
        $css = "";
        if($uid == $_SESSION['id_user'])
            $css = "me ml-auto";
        else
            $css .= " experimenter";
        require __DIR__ . "/tpl_chat_item.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the user view.
     */
    public function output_content_spec()
    {
        $title = $this->title_prefix . " "
            . $this->experimenter;
        require __DIR__ . "/tpl_chat_subject.php";
    }
}
?>
