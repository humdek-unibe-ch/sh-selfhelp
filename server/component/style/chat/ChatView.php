<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the chat component.
 * The chat component is not made available to the CMS in is only used
 * internally.
 */
abstract class ChatView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'alt' (empty string)
     * The text to be displayed if no subject is selected.
     */
    protected $alt;

    /**
     * DB field 'label' ("Send")
     * The label of the send button.
     */
    protected $label;

    /**
     * DB field 'label_global' ("Lobby")
     * The label of the global room.
     */
    protected $label_global;

    /**
     * DB field 'alert_fail' (empty string)
     * The alert message on failure.
     */
    protected $alert_fail;

    /**
     * DB field 'title_prefix' (empty string)
     * The first part of the title in the chat header.
     */
    protected $title_prefix;

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
        $this->alt = $this->model->get_db_field("alt");
        $this->label = $this->model->get_db_field("label", "Send");
        $this->label_global = $this->model->get_db_field("label_global", "Lobby");
        $this->alert_fail = $this->model->get_db_field("alert_fail");
        $this->title_prefix = $this->model->get_db_field("title_prefix");
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $this->alert_fail,
                )))
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alert.
     */
    protected function output_alert()
    {
        if($this->controller == null || $this->controller->has_failed())
            $this->output_local_component("alert-fail");
    }

    /**
     * Render the chat window.
     */
    protected function output_chat($title)
    {
        if($this->model->is_chat_ready())
        {
            $url = $_SERVER['REQUEST_URI'];
            require __DIR__ . "/tpl_chat.php";
        }
        else
            require __DIR__ . "/tpl_no_partner.php";

    }

    protected function output_room_list()
    {
        $rooms = $this->model->get_rooms();
        array_unshift($rooms, array("id" => GLOBAL_CHAT_ROOM_ID,
            "name" => $this->label_global));
        if(count($rooms) === 1)
            return;
        require __DIR__ . "/tpl_room_list.php";
    }

    /**
     * Render the room list.
     */
    protected function output_rooms($rooms)
    {
        foreach($rooms as $room)
        {
            $id = intval($room['id']);
            $name = $room['name'];
            $url = $this->model->get_link_url("contact", array("gid" => $id));
            $active = "";
            if($this->model->is_room_selected($id))
                $active = "active";
            require __DIR__ . "/tpl_room.php";
        }
    }

    /**
     * Render the chat messages.
     */
    protected function output_msgs()
    {
        foreach($this->model->get_chat_items() as $item)
        {
            $user = $item['name'];
            $msg = $item['msg'];
            $uid = intval($item['uid']);
            $datetime = $item['timestamp'];
            $this->output_msgs_spec($user, $msg, $uid, $datetime);
        }
    }
    abstract protected function output_msgs_spec($user, $msg, $uid, $datetime);

    /**
     * Render the new badge.
     */
    protected function output_new_badge()
    {
        $count = 0;
        if($count > 0)
            require __DIR__ . "/tpl_new_badge.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the user view.
     */
    public function output_content()
    {
        $this->output_content_spec();
    }

    abstract public function output_content_spec();
}
?>
