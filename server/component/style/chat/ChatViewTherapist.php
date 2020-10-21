<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/ChatView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the chat component if the current user is a therapist.
 * The chat component is not made available to the CMS and is only used
 * internally.
 */
class ChatViewTherapist extends ChatView
{
    /* Private Properties******************************************************/

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
        $this->subjects = $this->model->get_db_field("subjects");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the subject list.
     */
    private function output_subjects()
    {
        $subjects = array();
        if($this->model->is_user_in_group($_SESSION['id_user'])){
            $subjects = $this->model->get_GroupSubjects();
        }
        foreach($subjects as $subject)
        {
            $id = intval($subject['id']);
            if($id != $_SESSION['id_user'] && !$this->model->get_services()->get_acl()->has_access_select($id, $this->model->get_services()->get_db()->fetch_page_id_by_keyword("chatTherapist"))){
                // show all users except the logged in and the other therapists
                $group_id = intval($subject['id_groups']);
                $count = intval($subject['count']);
                $name = $subject['name'];            
                $subject_code = $subject['code'];
                $url = $this->model->get_subject_url($id);
                $active = "";
                if($this->model->is_subject_selected($id))
                    $active = "bg-info text-white";
                require __DIR__ . "/tpl_subject.php";
            }
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Render the chat messages. Place and color the messages dependeing on who
     * the author is.
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
    protected function output_msgs_spec($user, $msg, $uid, $datetime)
    {
        $css = "";
        if($uid == $_SESSION['id_user'])
            $css = "me ml-auto";
        else if($this->model->is_subject_selected($uid))
            $css .= " subject";
        require __DIR__ . "/tpl_chat_item.php";
    }

    /**
     * @param int $count - the number of the new messages
     * Render the new badge.
     */
    protected function output_new_badge_subject($count)
    {        
        if($count > 0)
            require __DIR__ . "/tpl_new_badge.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the chat view of the therapist role.
     */
    public function output_content_spec()
    {
        $title = $this->title_prefix . " "
            . $this->model->get_selected_user_name();
        $subjects = $this->subjects;
        require __DIR__ . "/tpl_chat_experimenter.php";
    }   

    /**
     * Render all groups that has access to chatTherapis and the therapis is in them as tabs.
     */
    public function output_group_tabs(){
        $groups = $this->model->get_groups();
        foreach ($groups as $key => $group) {
            $group_id = intval($group['id']);
            $tab_name = $group['name'];
            if($tab_name == 'subject'){
                $tab_name = $this->label_global;
            }
            $tab_css = $this->model->is_group_selected($group_id) ? 'active border-primary border-bottom-white' : '';
            $tab_url = $this->model->get_link_url('chatTherapist', array("gid" => $group_id));
            require __DIR__ . "/tpl_chat_tab.php";
        }        
    }
}
?>
