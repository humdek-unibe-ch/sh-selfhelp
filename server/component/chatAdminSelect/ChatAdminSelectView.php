<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the chatAdmin select component.
 */
class ChatAdminSelectView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * All users assigned to the active room.
     */
    private $users = null;

    /**
     * The number of mods in a group.
     */
    private $mod_count = 0;

    /**
     * The number of users in a group.
     */
    private $user_count = 0;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $users = $this->model->get_active_room_users();
        $this->users = array();
        foreach($users as $user)
        {
            if($user['is_mod'] === '1')
            {
                $this->mod_count++;
                $user['css'] = "font-italic";
            }
            $this->users[] = $user;
        }
        $this->user_count = count($this->users);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Render the button to create a new chat room.
     */
    private function output_button()
    {
        if($this->model->can_create_new_room())
        {
            $button = new BaseStyleComponent("button", array(
                "label" => "Create New Chat Room",
                "url" => $this->model->get_link_url("chatAdminInsert"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button->output_content();
        }
    }

    /**
     * Render the user description or the intro text.
     */
    private function output_main_content()
    {
        if($this->model->get_active_room() !== null)
        {
            $name = $this->model->get_active_room_name();
            $desc = $this->model->get_active_room_desc();
            require __DIR__ . "/tpl_room.php";
        }
        else
            require __DIR__ . "/tpl_rooms.php";
    }

    /**
     * Render the chat room delete card.
     */
    private function output_room_delete()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete Chat Room",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Careful, deleting a chat room will delete all conversation within this room. This action cannot be undone.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("button", array(
                    "label" => "Delete Chat Room",
                    "url" => $this->model->get_link_url("chatAdminDelete",
                        array("rid" => $this->model->get_active_room())),
                    "type" => "danger",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the cards to manipulate a room.
     */
    private function output_room_manipulation()
    {
        $this->output_room_users();
        if($this->model->can_delete_room())
        {
            $this->output_room_delete();
        }
    }

    /**
     * Render the room summary;
     */
    private function output_room_summary()
    {
        $subj_count = $this->user_count - $this->mod_count;
        require __DIR__ . '/tpl_summary.php';
    }

    /**
     * Render the card to manipulate chat room users.
     */
    private function output_room_users()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Users in Chat Room",
            "children" => array(
                new BaseStyleComponent("markdown", array(
                    "md-text" => "Only user that are assigned to a chat room are allowed to read and write messages within this room",
                )),
                new BaseStyleComponent("sortableList", array(
                    "is_editable" => $this->model->can_administrate_chat(),
                    "items" => $this->users,
                    "url_add" => $this->model->get_link_url(
                        "chatAdminUpdate",
                        array(
                            "rid" => $this->model->get_active_room(),
                            "mode" => "add_user",
                        )
                    ),
                    "url_delete" => $this->model->get_link_url(
                        "chatAdminUpdate",
                        array(
                            "rid" => $this->model->get_active_room(),
                            "mode" => "rm_user",
                            "did" => ":did",
                        )
                    ),
                    "label_add" => "Add User",
            )))
        ));
        $card->output_content();
    }

    /**
     * Render the list of rooms.
     */
    private function output_rooms()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Chat Rooms",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_rooms(),
                "id_prefix" => "rooms",
                "is_collapsible" => false,
                "id_active" => $this->model->get_active_room(),
            )))
        ));
        $card->output_content();
    }

    /**
     * Render warnings if sometrhing might be wrong about the chat room.
     */
    private function output_warnings()
    {
        $msg = null;
        if($this->user_count === 0)
            $msg = "This chat room is empty";
        else if($this->mod_count === 0)
            $msg = "This chat room has no `Therapist`. Nobody will be able to read messages sent by the `Subjects` in this room.";
        else if($this->mod_count === $this->user_count)
                $msg = "This chat room has no `Subjects`. No message will be sent to this room.";
        $alert = new BaseStyleComponent('alert', array(
            'type' => "warning",
            'children' => array(new BaseStyleComponent('markdownInline', array(
                'text_md_inline' => $msg,
            )))
        ));
        if($msg !== null)
            $alert->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_main.php";
    }
}
?>
