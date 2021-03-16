<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the chat admin delete component.
 */
class ChatAdminDeleteView extends BaseView
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user delete component.
     * @param object $controller
     *  The controller instance of the user delete component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
    }

    /**
     * Rednet the delete form.
     */
    private function output_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete Chat Room",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the chat room.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Delete Chat Room",
                    "url" => $this->model->get_link_url("chatAdminDelete",
                        array("rid" => $this->model->get_active_room())),
                    "type" => "danger",
                    "url_cancel" => $this->model->get_link_url("chatAdminSelect",
                        array("rid" => $this->model->get_active_room())),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "name",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter Chat Room Name",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $url = $this->model->get_link_url("chatAdminSelect");
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $name = $this->model->get_active_room_name();
            require __DIR__ . "/tpl_delete_room.php";
        }
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
