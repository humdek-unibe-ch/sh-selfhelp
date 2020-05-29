<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleMailView extends BaseView
{
    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/
    /**
     * mail queue entry,
     */
    private $mail_queue_entry;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $mqid = $this->model->get_mqid();
        if ($mqid > 0) {
            $this->mail_queue_entry = $this->model->get_services()->get_db()->query_db_first('SELECT * FROM view_mailQueue WHERE id = :mqid;', array(":mqid" => $mqid));
        }
    }

    /* Private Methods ********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if ($this->model->get_mqid() > 0) {
            require __DIR__ . "/tpl_mailQueue_entry.php";
        } else {
            require __DIR__ . "/tpl_moduleMail.php";
        }
    }

    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    public function output_mail_queue()
    {
        require __DIR__ . "/tpl_mailQueue.php";
    }

    public function output_mail_queue_rows()
    {
        foreach ($this->model->get_mail_queue() as $queue) {
            $url = $this->model->get_link_url("moduleMail", array("mqid" => intval($queue['id'])));
            require __DIR__ . "/tpl_mailQueue_row.php";
        }
    }

    public function output_mail_queue_transactions()
    {
        $mailQueue_transactions = (json_encode($this->model->get_mail_queue_transactions(), JSON_OBJECT_AS_ARRAY));
        return $mailQueue_transactions;
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
        if (empty($local)) {
            $local = array(__DIR__ . "/js/moduleMail.js");
        }
        return parent::get_js_includes($local);
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/css/moduleMail.css");
        return parent::get_css_includes($local);
    }

    public function get_date_types()
    {
        $select_date_types = new BaseStyleComponent("select", array(
            "value" => $this->model->get_date_type(),
            "name" => "dateType",
            "items" => $this->get_lookups_with_code("mailQueueSearchDateTypes"),
        ));
        $select_date_types->output_content();
    }

    /**
     * Render the entry form view
     */
    protected function output_entry_form_view()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => 'Mail Queue ID: ' . $this->mail_queue_entry['id'],
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Status",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['status'],
                        "css" => $this->mail_queue_entry['status'] === 'deleted' ? 'text-danger' : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Created",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['date_create']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date To Be Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['date_to_be_sent']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['date_sent']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Email",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['from_email']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['from_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Reply To",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['reply_to']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Recipient Emails",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['recipient_emails']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "CC Emails",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['cc_emails']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "BCC Emails",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['bcc_emails']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Subject",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['subject']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Body",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['body']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Is HTML",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->mail_queue_entry['is_html'] === 1 ? 'True' : 'False'
                    ))),
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the sidebar buttons for mailQueue entry
     */
    public function output_side_buttons_mailQueue_entry()
    {
        // maoduel queue back button
        $mailQueueuButton = new BaseStyleComponent("button", array(
            "label" => "Mail Queueu",
            "url" => $this->model->get_link_url("moduleMail"),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $mailQueueuButton->output_content();

        //send/resend button
        $sendButton = new BaseStyleComponent("button", array(
            "label" => ($this->mail_queue_entry['status'] == 'sent' ? 'Resend' : 'Send') . " Queue Entry",
            "id" => "send",
            "url" => $this->model->get_link_url("moduleMail", array("mqid" => intval($this->mail_queue_entry['id']))),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $sendButton->output_content();

        if (!($this->mail_queue_entry['status'] === 'deleted')) {
            // delete button visible only if not deleted
            $deleteButton = new BaseStyleComponent("button", array(
                "label" => "Delete Queue Entry",
                "id" => "delete",
                "url" => $this->model->get_link_url("moduleMail", array("mqid" => intval($this->mail_queue_entry['id']))),
                "type" => "danger",
                "css" => "d-block mb-3",
            ));
            $deleteButton->output_content();
        }
    }

    /**
     * Render the sidebar buttons for mailQueue
     */
    public function output_side_buttons()
    {
        // run cron job manually
        $mailQueueuRunCron = new BaseStyleComponent("button", array(
            "id" => "run_cron",
            "label" => "Run now",
            "url" => $this->model->get_link_url("moduleMail"),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $mailQueueuRunCron->output_content();

        // compose email
        $composeEmail = new BaseStyleComponent("button", array(
            "label" => "Compose email",
            "id" => "compose_email",
            "url" => $this->model->get_link_url("moduleMailComposeEmail"),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $composeEmail->output_content();
    }
}
?>
