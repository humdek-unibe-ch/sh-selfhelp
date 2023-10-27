<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the button style component.
 * This style components allows to represent a link as a button.
 */
class ButtonView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' (empty string).
     * The label on the button. If this field is not set, the button is not
     * rendered.
     */
    private $label;

    /**
     * DB field 'confirmation_title' (empty string).
     * If set a modal is shown. This will be the header of the confirmation modal.
     */
    private $confirmation_title;

    /**
     * DB field 'label_cancel' (empty string).
     */
    private $label_cancel;

    /**
     * DB field 'label_continue' (OK).
     */
    private $label_continue;

    /**
     * DB field 'label_message' ('Do you want to continue?').
     */
    private $label_message;

    /**
     * DB field 'url' (empty string).
     * The target url of the button. If this field is not set, the button is not
     * rendered.
     */
    private $url;

    /**
     * DB field 'type' ('primary').
     * The style of the button. E.g. 'warning', 'danger', etc.
     */
    private $type;

    /**
     * Id used for html element
     */
    private $id;

    /**
     * Data object used to pass some data in html if it is needed later in JS
     */
    private $data;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->label = $this->model->get_db_field("label");
        $this->url = $this->model->get_db_field("url");
        $this->type = $this->model->get_db_field("type", "primary");
        $this->id = $this->model->get_db_field("id", null);
        $this->data = $this->model->get_db_field("data", null);
        $this->confirmation_title = $this->model->get_db_field("confirmation_title");
        $this->label_cancel = $this->model->get_db_field("label_cancel");
        $this->label_continue = $this->model->get_db_field("label_continue");
        $this->label_message = $this->model->get_db_field("label_message");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $data_confirmation = array();
        if($this->confirmation_title){
            $data_confirmation['confirmation_title'] = $this->confirmation_title;
            $data_confirmation['label_cancel'] = $this->label_cancel;
            $data_confirmation['label_continue'] = $this->label_continue;
            $data_confirmation['label_message'] = $this->label_message;
        }
        if ($this->url == "" || $this->label == "") return;
        require __DIR__ . "/tpl_button.php";
    }

}
?>
