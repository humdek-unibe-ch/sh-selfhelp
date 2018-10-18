<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the chat component.
 * The chat component is not made available to the CMS in is only used
 * internally.
 */
class ChatView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'label' ("Send")
     * The label of the send button.
     */
    private $label;

    /**
     * DB field 'alert_fail' (empty string)
     * The alert message on failure.
     */
    private $alert_fail;

    /**
     * DB field 'alt' (empty string)
     * The text to be displayed if no subject is selected.
     */
    private $alt;

    /**
     * DB field 'title_prefix' (empty string)
     * The first part of the title in the chat header.
     */
    private $title_prefix;

    /**
     * DB field 'experimenter' (empty string)
     * The text to be displayed when addressing experimenter.
     */
    private $experimenter;

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
        $this->label = $this->model->get_db_field("label", "Send");
        $this->alert_fail = $this->model->get_db_field("alert_fail");
        $this->alt = $this->model->get_db_field("alt");
        $this->title_prefix = $this->model->get_db_field("title_prefix");
        $this->experimenter = $this->model->get_db_field("experimenter");
        $this->subjects = $this->model->get_db_field("subjects");
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
    private function output_alert()
    {
        if($this->controller == null || $this->controller->has_failed())
            $this->output_local_component("alert-fail");
    }

    /**
     * Render the chat window.
     */
    private function output_chat($title)
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
     * Render the chat messages.
     */
    private function output_msgs()
    {
        foreach($this->model->get_chat_items() as $item)
        {
            $user = $item['name'];
            $msg = $item['msg'];
            $uid = intval($item['uid']);
            $datetime = $item['timestamp'];
            $css = "";
            if($uid == $_SESSION['id_user'])
                $css = "me ml-auto";
            else if($this->model->is_selected_user($uid))
                $css .= " subject";
            else if($this->model->is_current_user_experimenter())
                $css .= " experimenter ml-auto";
            require __DIR__ . "/tpl_chat_item.php";
        }
    }

    /**
     * Render the new badge.
     */
    private function output_new_badge()
    {
        $count = 0;
        if($count > 0)
            require __DIR__ . "/tpl_new_badge.php";
    }

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
            if($this->model->is_selected_user($id))
                $active = "bg-info text-white";
            require __DIR__ . "/tpl_subject.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the user view.
     */
    public function output_content()
    {
        if($this->model->is_current_user_experimenter())
        {
            $title = $this->title_prefix . " "
                . $this->model->get_selected_user_name();
            require __DIR__ . "/tpl_chat_experimenter.php";
        }
        else
        {
            $title = $this->title_prefix . " "
                . $this->experimenter;
            require __DIR__ . "/tpl_chat_subject.php";
        }
    }
}
?>
