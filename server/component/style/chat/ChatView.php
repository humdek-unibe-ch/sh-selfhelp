<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the user profile component.
 */
class ChatView extends BaseView
{
    /* Private Properties******************************************************/

    private $label;
    private $alert_fail;

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
        $this->no_partner = $this->model->get_db_field("no_partner");
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
     * Render the fail alerts.
     */
    private function output_alert()
    {
        if($this->controller == null || $this->controller->has_failed())
            $this->output_local_component("alert-fail");
    }

    private function output_chat()
    {
        if($this->model->is_chat_ready())
        {
            $url = $_SERVER['REQUEST_URI'];
            require __DIR__ . "/tpl_chat.php";
        }
        else
            require __DIR__ . "/tpl_no_partner.php";

    }

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
            else if($this->model->is_experimenter)
                $css .= " experimenter ml-auto";
            require __DIR__ . "/tpl_chat_item.php";
        }
    }

    private function output_new_badge()
    {
        $count = 0;
        if($count > 0)
            require __DIR__ . "/tpl_new_badge.php";
    }

    private function output_subjects()
    {
        foreach($this->model->get_subjects() as $subject)
        {
            $id = intval($subject['id']);
            $url = $this->model->get_link_url("contact", array("uid" => $id));
            $active = "";
            if($this->model->is_selected_user($id))
                $active = "bg-info text-white";
            $name = $subject['name'];
            require __DIR__ . "/tpl_subject.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/chat.css");
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/chat.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the user view.
     */
    public function output_content()
    {
        if($this->model->is_experimenter)
            require __DIR__ . "/tpl_chat_experimenter.php";
        else
            require __DIR__ . "/tpl_chat_subject.php";
    }
}
?>
