<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formUserInput/FormUserInputView.php";

/**
 * The view class of the messageBoard style component.
 */
class MessageBoardView extends FormUserInputView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The title of each message.
     */
    private $title;

    /**
     * DB field 'text' (empty string).
     * The title of each message.
     */
    private $message;

    /**
     * DB field 'max' (0).
     * The maximal number of messages to be shown.
     */
    private $limit;

    /**
     * DB field 'icons' (empty array).
     * A list of icons to be shown in the message footer.
     */
    private $icons;

    /**
     * DB field 'comments' (empty array).
     * A list of expressions to be shown in the message footer.
     */
    private $comments;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->title = $this->model->get_db_field("title");
        $this->message = $this->model->get_db_field("text_md");
        $this->limit = $this->model->get_db_field("max", 0);
        $this->icons = $this->model->get_db_field("icons", array());
        if(!is_array($this->icons)) {
            $this->icons = array();
        }
        $this->comments = $this->model->get_db_field("comments", array());
        if(!is_array($this->comments)) {
            $this->comments = array();
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Render the messages to the message board.
     */
    private function output_messages()
    {
        $messages = $this->model->get_scores(intval($this->limit));

        foreach($messages as $score_message)
        {
            $title = str_replace("@publisher", $score_message['user_name'],
                $this->title);
            $message = $this->message;
            $ts = $score_message['create_time'];
            $time = $this->model->convert_timestamp($ts);
            $score = $score_message['value'];
            $record_id = $score_message['record_id'];
            $replies = $this->model->get_replies($record_id, $this->icons);
            $reply_messages = $replies['reply_messages'];
            $icon_counter = $replies['icon_counter'];
            $user = $score_message['user_id'];
            $color = $_SESSION['id_user'] == $user ? "primary" : "success";
            require __DIR__ . "/tpl_message.php";
        }
    }

    private function output_message_footer($icon_counter, $user, $record_id)
    {
        require __DIR__ . "/tpl_message_footer.php";
    }

    private function output_message_footer_comments($user, $record_id)
    {
        if($_SESSION['id_user'] == $user)
            return;
        require __DIR__ . "/tpl_message_footer_comments.php";
    }

    private function output_message_footer_comment_options($record_id)
    {
        $url = $_SERVER['REQUEST_URI'] . '#message-' . $this->id_section . '-' . $record_id;
        $id_reply = $this->model->get_reply_input_section_id();
        $id_link = $this->model->get_link_input_section_id();
        $form_name = $this->model->get_form_name();
        foreach($this->comments as $comment)
        {
            require __DIR__ . "/tpl_comment.php";
        }
    }

    private function output_message_footer_icons($user, $icon_counter, $record_id)
    {
        $disabled_forced = $_SESSION['id_user'] == $user;
        $url = $_SERVER['REQUEST_URI'] . '#message-' . $this->id_section . '-' . $record_id;
        $id_reply = $this->model->get_reply_input_section_id();
        $id_link = $this->model->get_link_input_section_id();
        $form_name = $this->model->get_form_name();
        foreach($this->icons as $icon)
        {
            $count = 0;
            $disabled = false;
            if(isset($icon_counter[$icon])) {
                $count = $icon_counter[$icon]['count'];
                $disabled = $disabled_forced ||
                    in_array($_SESSION['id_user'], $icon_counter[$icon]['users']);
            }
            require __DIR__ . "/tpl_icon_form.php";
        }
    }

    private function output_message_replies($replies)
    {
        if(!$replies) {
            return;
        }
        foreach($replies as $reply)
        {
            $user = $reply['user_name'];
            $message = $reply['value'];
            $ts = $reply['create_time'];
            $time = $this->model->convert_timestamp($ts);
            require __DIR__ . "/tpl_reply.php";
        }
    }

    private function output_message_reply($message)
    {
        if(in_array($message, $this->icons)) {
            $this->output_icon($message);
        } else {
            echo $message;
        }
    }

    private function output_icon($icon)
    {
        require __DIR__ . "/tpl_icon.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_messageBoard.php";
    }
}
?>
