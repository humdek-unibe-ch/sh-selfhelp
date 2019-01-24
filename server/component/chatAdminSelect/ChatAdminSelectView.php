<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the chatAdmin select component.
 */
class ChatAdminSelectView extends BaseView
{
    /* Private Properties *****************************************************/

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

    private function output_room_manipulation()
    {
        $this->output_room_users();
        if($this->model->can_delete_room())
        {
            $this->output_room_delete();
        }
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
                    "items" => $this->model->get_active_room_users(),
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
