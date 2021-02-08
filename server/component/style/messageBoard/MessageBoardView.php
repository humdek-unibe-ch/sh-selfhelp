<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the messageBoard style component.
 */
class MessageBoardView extends StyleView
{
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
    }

    /* Private Methods ********************************************************/

    /**
     * 
     */
    private function output_messages()
    {
        $messages = array(
            array(
                "user" => 3,
                "title" => "Simon's Highscore",
                "message" => "My new highscore",
                "time" => "2m",
                "score" => "70",
                "replies" => array(
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    )
                )
            ),
            array(
                "user" => 2,
                "title" => "Hanueles's Highscore",
                "message" => "My new highscore",
                "time" => "32m",
                "score" => "82",
                "replies" => array(
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Baser",
                        "messgae" => "Go for it my friend.",
                        "time" => "just now"
                    )
                )
            ),
            array(
                "user" => 2,
                "title" => "Basers's Highscore",
                "message" => "My new highscore",
                "time" => "1h",
                "score" => "94",
                "replies" => array(
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Baser",
                        "messgae" => "Go for it my friend.",
                        "time" => "just now"
                    )
                )
            ),
            array(
                "user" => 2,
                "title" => "Basers's Highscore",
                "message" => "My new highscore",
                "time" => "1h",
                "score" => "95",
                "replies" => array()
            ),
            array(
                "user" => 3,
                "title" => "Simon's Highscore",
                "message" => "My new highscore",
                "time" => "3d",
                "score" => "86",
                "replies" => array(
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Hanuele",
                        "messgae" => "Well Done!",
                        "time" => "4m"
                    ),
                    array(
                        "user" => "Baser",
                        "messgae" => "Go for it my friend.",
                        "time" => "just now"
                    )
                )
            )
        );

        foreach($messages as $score_message)
        {
            $title = $score_message['title'];
            $message = $score_message['message'];
            $time = $score_message['time'];
            $score = $score_message['score'];
            $replies = $score_message['replies'];
            $user = $score_message['user'];
            $color = $_SESSION['id_user'] == $user ? "primary" : "success";
            require __DIR__ . "/tpl_message.php";
        }
    }

    private function output_message_footer($user)
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

    private function output_message_footer_comments($comments)
    {
        foreach($comments as $comment)
        {
            require __DIR__ . "/tpl_comment.php";
        }
    }

    private function output_message_footer_icons($icons)
    {
        foreach($icons as $icon)
        {
            require __DIR__ . "/tpl_icon.php";
        }
    }

    private function output_message_replies($replies)
    {
        foreach($replies as $reply)
        {
            $user = $reply['user'];
            $message = $reply['messgae'];
            $time = $reply['time'];
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
