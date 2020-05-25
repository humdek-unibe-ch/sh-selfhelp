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

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
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
}
?>
