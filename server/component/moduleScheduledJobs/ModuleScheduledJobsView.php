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
class ModuleScheduledJobsView extends BaseView
{
    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/
    /**
     * mail queue entry,
     */
    private $job_entry;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $sjid = $this->model->get_sjid();
        if ($sjid > 0) {
            $this->job_entry = $this->model->get_services()->get_db()->query_db_first('SELECT * FROM view_scheduledJobs WHERE id = :sjid;', array(":sjid" => $sjid));
        }
    }

    /* Private Methods ********************************************************/

    private function output_mail_form_view()
    {
        $mail_entry = $this->model->get_services()->get_db()->query_db_first('SELECT * FROM view_mailQueue WHERE id = :sjid;', array(":sjid" => $this->model->get_sjid()));
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => 'Scheduled Job ID: ' . $mail_entry['id'],
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Status",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['status'],
                        "css" => $mail_entry['status_code'] === scheduledJobsStatus_deleted ? 'text-danger' : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Created",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['date_create']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date To Be Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['date_to_be_executed']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['date_executed']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Email",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['from_email']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['from_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Reply To",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['reply_to']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Recipient Emails",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['recipient_emails']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "CC Emails",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['cc_emails']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "BCC Emails",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['bcc_emails']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Subject",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['subject']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Body",
                    "locale" => "",
                    "id" => "body",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['body']
                    ))),
                )),
                new BaseStyleComponent("textarea", array(
                    "label" => "Message",
                    "type_input" => "text",
                    "name" => "body",
                    "css" => "d-none",
                    "value" => $mail_entry['body'],
                    "placeholder" => "@user_name can be used for showing the user",
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Is HTML",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['is_html'] == 1 ? 'True' : 'False'
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Config",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $mail_entry['config']
                    ))),
                )),
                new BaseStyleComponent("card", array(
                    "css" => "mb-3",
                    "title" => "Attachments",
                    "type" => "light",
                    "is_expanded" => true,
                    "is_collapsible" => true,
                    "children" => array(new BaseStyleComponent("sortableList", array(
                        "items" => $this->model->get_attachments($mail_entry['id_mailQueue']),
                        "is_editable" => true
                    )))
                ))
            )
        ));
        $form->output_content();
    }

    private function output_notification_form_view()
    {
        $entry = $this->model->get_services()->get_db()->query_db_first('SELECT * FROM view_notifications WHERE id = :sjid;', array(":sjid" => $this->model->get_sjid()));
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => 'Scheduled Job ID: ' . $entry['id'],
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Status",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['status'],
                        "css" => $entry['status_code'] === scheduledJobsStatus_deleted ? 'text-danger' : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Created",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['date_create']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date To Be Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['date_to_be_executed']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['date_executed']
                    ))),
                )),

                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Recipient",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['recipient']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "URL",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['url']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Subject",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['subject']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Body",
                    "locale" => "",
                    "id" => "body",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['body']
                    ))),
                )),
                new BaseStyleComponent("textarea", array(
                    "label" => "Message",
                    "type_input" => "text",
                    "name" => "body",
                    "css" => "d-none",
                    "value" => $entry['body'],
                    "placeholder" => "@user_name can be used for showing the user",
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Config",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['config']
                    ))),
                )),
            )
        ));
        $form->output_content();
    }

    private function output_task_form_view()
    {
        $entry = $this->model->get_services()->get_db()->query_db_first('SELECT * FROM view_tasks WHERE id = :sjid;', array(":sjid" => $this->model->get_sjid()));
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => 'Scheduled Job ID: ' . $entry['id'],
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Status",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['status'],
                        "css" => $entry['status_code'] === scheduledJobsStatus_deleted ? 'text-danger' : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Created",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['date_create']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date To Be Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['date_to_be_executed']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Date Sent",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['date_executed']
                    ))),
                )),

                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Recipient",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['recipient']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Description",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['description']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Config",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $entry['config']
                    ))),
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Get all the column names 
     * @param array $table
     * The table rows
     * @return array
     * Return all the columns names in an array
     */
    private function get_all_columns($table)
    {
        $column_names = array(); // Initialize an empty array

        foreach ($table as $item) {
            if (is_object($item)) {
                // If the item is an object, get its properties
                $objectProperties = get_object_vars($item);
                $column_names = array_merge($column_names, $objectProperties);
            } else if (is_array($item)) {
                // If the item is an array, merge its values recursively
                $column_names = array_merge($column_names, array_keys($item));
            }
        }

        // Keep only the unique column names
        return array_keys(array_flip($column_names));
    }

    /**
     * Output a table with the job transactions
     */
    private function output_job_transactions()
    {
        $transactions = $this->model->get_scheduledJobs_transactions($this->model->get_sjid());
        $column_names = $this->get_all_columns($transactions);
        $transaction_rows = array();
        foreach ($transactions as $key_transaction => $transaction) {
            $transaction_cells = array();
            foreach ($column_names as $key => $column_name) {
                $transaction_cells[] =  new BaseStyleComponent("tableCell", array(
                    "text_md" => isset($transaction[$column_name]) ? $transaction[$column_name] : "",
                ));
            }
            $transaction_rows[] = new BaseStyleComponent("tableRow", array(
                "children" => $transaction_cells
            ));
        }
        $card = new BaseStyleComponent(
            "card",
            array(
                "css" => "mb-3",
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => 'Transactions for Job ID: ' . $this->model->get_sjid(),
                "children" => array(
                    new BaseStyleComponent(
                        "table",
                        array(
                            "css" => "w-100",
                            "column_names" => implode(', ', $column_names),
                            "children" => $transaction_rows
                        )
                    )
                )
            )
        );
        $card->output_content();
    }


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if ($this->model->get_sjid() > 0) {
            require __DIR__ . "/tpl_scheduledJobs_entry.php";
        } else {
            require __DIR__ . "/tpl_scheduledJobs.php";
        }
    }

    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    public function output_mail_queue()
    {
        require __DIR__ . "/tpl_scheduledJobsQueue.php";
    }

    public function output_mail_queue_rows()
    {
        foreach ($this->model->get_scheduledJobs_queue() as $queue) {
            $url = $this->model->get_link_url("moduleScheduledJobs", array("sjid" => intval($queue['id'])));
            require __DIR__ . "/tpl_scheduledJobsQueue_row.php";
        }
    }

    public function output_mail_queue_transactions()
    {
        $mailQueue_transactions = (json_encode($this->model->get_scheduledJobs_queue_transactions(), JSON_OBJECT_AS_ARRAY));
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
            $local = array(
                __DIR__ . "/js/moduleScheduledJobs.js"
            );
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
        $local = array(
            __DIR__ . "/css/moduleScheduledJobs.css"
        );
        return parent::get_css_includes($local);
    }

    public function get_date_types()
    {
        $select_date_types = new BaseStyleComponent("select", array(
            "value" => $this->model->get_date_type(),
            "name" => "dateType",
            "items" => $this->get_lookups_with_code(scheduledJobsSearchDateTypes),
        ));
        $select_date_types->output_content();
    }

    /**
     * Render the entry form view
     */
    protected function output_entry_form_view()
    {
        if ($this->job_entry['type_code'] == jobTypes_email) {
            $this->output_mail_form_view();
        } else if ($this->job_entry['type_code'] == jobTypes_notification) {
            $this->output_notification_form_view();
        } else if ($this->job_entry['type_code'] == jobTypes_task) {
            $this->output_task_form_view();
        }
        $this->output_job_transactions();
    }


    /**
     * Render the sidebar buttons for scheduledJobs entry
     */
    public function output_side_buttons_scheduledJobs_entry()
    {
        // maoduel queue back button
        $scheduledJobsButton = new BaseStyleComponent("button", array(
            "label" => "Scheduled Jobs",
            "url" => $this->model->get_link_url("moduleScheduledJobs"),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $scheduledJobsButton->output_content();

        //execute/reexecute button
        $executeButton = new BaseStyleComponent("button", array(
            "label" => ($this->job_entry['status_code'] == scheduledJobsStatus_done ? 'Re-execute' : 'Execute') . " Job Entry",
            "id" => "execute",
            "url" => $this->model->get_link_url("moduleScheduledJobs", array("sjid" => intval($this->job_entry['id']))),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $executeButton->output_content();

        if (isset($this->job_entry['id_formActions']) && $this->job_entry['id_formActions'] > 0) {
            // View the action from where the job was generated if it is linked
            $actionButton = new BaseStyleComponent("button", array(
                "label" => "View Action",
                "id" => "view_action",
                "url" => $this->model->get_link_url("moduleFormsAction", array("mode" => SELECT, "aid" => $this->job_entry['id_formActions'])),
                "css" => "d-block mb-3"
            ));
            $actionButton->output_content();
        }

        if (!($this->job_entry['status_code'] === scheduledJobsStatus_deleted)) {
            // delete button visible only if not deleted
            $deleteButton = new BaseStyleComponent("button", array(
                "label" => "Delete Job Entry",
                "id" => "delete",
                "url" => $this->model->get_link_url("moduleScheduledJobs", array("sjid" => intval($this->job_entry['id']))),
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
            "url" => $this->model->get_link_url("moduleScheduledJobs"),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $mailQueueuRunCron->output_content();

        // compose email
        $composeEmail = new BaseStyleComponent("button", array(
            "label" => "Compose email",
            "id" => "compose_email",
            "url" => $this->model->get_link_url("moduleScheduledJobsCompose",  array("type" => jobTypes_email)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $composeEmail->output_content();

        // compose Push notification
        $composeNotification = new BaseStyleComponent("button", array(
            "label" => "Compose notification",
            "id" => "compose_notification",
            "url" => $this->model->get_link_url("moduleScheduledJobsCompose",  array("type" => jobTypes_notification)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $composeNotification->output_content();

        // view calendar
        $viewCalendar = new BaseStyleComponent("button", array(
            "label" => "View calendar",
            "id" => "job_schedule_calendar",
            "url" => $this->model->get_link_url("moduleScheduledJobsCalendar",  array()),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $viewCalendar->output_content();
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
