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
     * DB field 'label_global' (empty string)
     * The label of the global room.
     */
    private $label_global;

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
        $this->label = $this->model->get_db_field("label_global");
        $this->experimenter = $this->model->get_db_field("experimenter");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the room list.
     */
    private function output_rooms()
    {
        foreach($this->model->get_rooms() as $room)
        {
            $id = intval($room['id']);
            $name = $room['name'];
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
            . $this->experimenter;
        require __DIR__ . "/tpl_chat_subject.php";
    }
}
?>
