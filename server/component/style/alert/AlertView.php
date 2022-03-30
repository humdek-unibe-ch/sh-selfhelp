<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the alert style component.
 * This style component is a visual container that allows to represent alert
 * boxes.
 */
class AlertView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'is_dismissable' (false).
     * If set to true, the alert can be dismissed by clicking on a close button.
     */
    private $is_dismissable;

    /**
     * DB field 'type' ('primary').
     * The style of the alert. E.g. 'warning', 'danger', etc.
     */
    private $type;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->is_dismissable = $this->model->get_db_field("is_dismissable",
            false);
        $this->type = $this->model->get_db_field("type", "primary");
    }

    /* Private  Methods *******************************************************/

    /**
     * Render the close button.
     */
    private function output_close_button()
    {
        if(!$this->is_dismissable) return;
        require __DIR__ . "/tpl_close_alert.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $type = "alert-" . $this->type;
        require __DIR__ . "/tpl_alert.php";
    }

}
?>
