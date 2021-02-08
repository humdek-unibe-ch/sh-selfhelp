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
    }

    /* Private Methods ********************************************************/

    /**
     * 
     */
    private function output_messages()
    {
        $messages = $this->model->get_scores();

        foreach($messages as $score_message)
        {
            $title = str_replace("@publisher", $score_message['user_name'],
                $this->title);
            $message = $this->message;
            $time = $score_message['create_time'];
            $score = $score_message['value'];
            $record_id = $score_message['record_id'];
            $replies = $this->model->get_replies($record_id);
            $user = $score_message['user_id'];
            $color = $_SESSION['id_user'] == $user ? "primary" : "success";
            require __DIR__ . "/tpl_message.php";
        }
    }

    private function output_message_footer($user, $record_id)
    {
        if($_SESSION['id_user'] == $user)
            return;
        $icons = [
            "thumbs-up",
            "laugh",
            "heart"
        ];
        $comments = [
            "Well Done!",
            "Go for it my friend."
        ];
        require __DIR__ . "/tpl_message_footer.php";
    }

    private function output_message_footer_comments($comments, $record_id)
    {
        $url = $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section;
        $id_reply = $this->model->get_reply_input_section_id();
        $id_link = $this->model->get_link_input_section_id();
        $form_name = $this->model->get_form_name();
        foreach($comments as $comment)
        {
            require __DIR__ . "/tpl_comment.php";
        }
    }

    private function output_message_footer_icons($icons, $record_id)
    {
        $url = $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section;
        $id_reply = $this->model->get_reply_input_section_id();
        $id_link = $this->model->get_link_input_section_id();
        $form_name = $this->model->get_form_name();
        foreach($icons as $icon)
        {
            $count = $this->model->get_icon_count($icon, $record_id);
            require __DIR__ . "/tpl_icon.php";
        }
    }

    private function output_message_replies($replies)
    {
        foreach($replies as $reply)
        {
            $user = $reply['user_name'];
            $message = $reply['value'];
            $time = $reply['create_time'];
            require __DIR__ . "/tpl_reply.php";
        }
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
