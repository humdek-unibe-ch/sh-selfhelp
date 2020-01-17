<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * DB field 'label_submit' ("Send")
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

    /**
     * DB field 'title_prefix' ("New Messages")
     * A divider with this text indicating the new messages.
     */
    private $label_new;

    /**
     * The list of chat items (see ChatModel::get_chat_items).
     */
    protected $items = null;

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
        $this->label = $this->model->get_db_field("label_submit", "Send");
        $this->label_global = $this->model->get_db_field("label_global", "Lobby");
        $this->alert_fail = $this->model->get_db_field("alert_fail");
        $this->title_prefix = $this->model->get_db_field("title_prefix");
        $this->label_new = $this->model->get_db_field("label_new", "New Messages");
        $this->items = $this->model->get_chat_items();
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $this->alert_fail,
                )))
            ))
        );
    }

    /* Abstract Protecetd Methods *********************************************/

    /**
     * Render the chat messages. This depends on the role of the current user.
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
    abstract protected function output_msgs_spec($user, $msg, $uid, $datetime);

    /* Abstract Public Methods ************************************************/

    /**
     * Render the role-specific content.
     */
    abstract public function output_content_spec();

    /* Protecetd Methods ******************************************************/

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

    /** 
     * Render the list of available rooms and if therapist add groups
     */
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
     *
     * @param array $rooms
     *  A list of rooms with the following keys:
     *   - 'id':    the id of the chat room
     *   - 'name':  the name of the chat room
     */
    protected function output_rooms($rooms)
    {
        foreach($rooms as $room)
        {
            $id = intval($room['id']);
            $name = $room['name'];
            $url = $this->model->get_link_url("contact", array(
                "chrid" => $id
                ));
            $active = "";
            if($this->model->is_room_selected($id))
                $active = "active";
            require __DIR__ . "/tpl_room.php";
        }
    }

    /**
     * Render the room list.
     *
     * @param array $rooms
     *  A list of rooms with the following keys:
     *   - 'id':    the id of the chat room
     *   - 'name':  the name of the chat room
     */
    protected function output_groups($groups)
    {
        foreach($groups as $group)
        {
            $id = intval($group['id']);
            $name = $group['name'];
            $url = $this->model->get_link_url("contact", array(
                "gid" => $id,
                "chrid" => 0
                ));
            $active = "";
            if($this->model->is_group_selected($id))
                $active = "active";
            require __DIR__ . "/tpl_group.php";
        }
    }

    /**
     * Render the chat messages.
     */
    protected function output_msgs()
    {
        $first_new = true;
        foreach($this->items as $item)
        {
            $user = $item['name'];
            $msg = $item['msg'];
            $uid = intval($item['uid']);
            $datetime = $item['timestamp'];
            if($first_new && $item['is_new'] == '1' && $uid != $_SESSION['id_user'])
            {
                require __DIR__ . "/tpl_divider.php";
                $first_new = false;
            }
            $this->output_msgs_spec($user, $msg, $uid, $datetime);
        }
    }

    /**
     * Render the new badge that is displayed next to the room name.
     */
    protected function output_new_badge_room($id)
    {
        $count = $this->model->get_room_message_count($id);
        if($count > 0)
            require __DIR__ . "/tpl_new_badge.php";
    }

    /**
     * Render the new badge that is displayed next to the group name.
     */
    protected function output_new_badge_group($id)
    {
        $count = $this->model->get_group_message_count($id);
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
}
?>
