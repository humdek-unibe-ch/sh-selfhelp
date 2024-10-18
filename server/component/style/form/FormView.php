<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the form style component. This component renders a html
 * form.
 */
class FormView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'url' (empty string).
     * The achtion url of the form. If this is not set, the form will not be
     * rendered.
     */
    private $url;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    private $type;

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    private $label;

    /**
     * DB field 'label_cancel' ('Cancel').
     * The label of the cancel button.
     */
    private $label_cancel;

    /**
     * DB field 'url_cancel' (empty string).
     * The target url when the cancel button is clicked.  If left empty, the
     * cancel button will not be rendered
     */
    private $cancel_url;

    /**
     * Entry data if the style is used in entry visualization
     */
    protected $entry_data;

    /**
     * DB field 'confirmation_title' (empty string).
     * If set a modal is shown. This will be the header of the confirmation modal.
     */
    private $confirmation_title;

    /**
     * DB field 'confirmation_cancel' (empty string).
     */
    private $confirmation_cancel;

    /**
     * DB field 'confirmation_continue' (OK).
     */
    private $confirmation_continue;

    /**
     * DB field 'confirmation_message' ('Do you want to continue?').
     */
    private $confirmation_message;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->url = $this->model->get_db_field("url");        
        $this->type = $this->model->get_db_field("type", "primary");
        $this->label = $this->model->get_db_field("label", "Submit");
        $this->label_cancel = $this->model->get_db_field("label_cancel", "Cancel");
        $this->cancel_url = $this->model->get_db_field("url_cancel");
        $this->confirmation_title = $this->model->get_db_field("confirmation_title", '');
        $this->confirmation_cancel = $this->model->get_db_field("confirmation_cancel", '');
        $this->confirmation_continue = $this->model->get_db_field("confirmation_continue", '');
        $this->confirmation_message = $this->model->get_db_field("confirmation_message");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the cancel button.
     */
    private function output_cancel()
    {
        $button = new BaseStyleComponent("button", array(
            "label" => $this->label_cancel,
            "type" => "secondary",
            "url" => $this->cancel_url,
            "css" => "float-end form-cancel-btn",
        ));
        $button->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if ($this->url == "") return;
        $data_confirmation = array();
        if ($this->confirmation_title) {
            $data_confirmation['confirmation_title'] = $this->confirmation_title;
            $data_confirmation['confirmation_cancel'] = $this->confirmation_cancel;
            $data_confirmation['confirmation_continue'] = $this->confirmation_continue;
            $data_confirmation['confirmation_message'] = $this->confirmation_message;
        }
        require __DIR__ . "/tpl_form.php";
    }

    /**
     * Render the submit button.
     */
    public function output_submit_button()
    {
        if ($this->label) {                        
            require __DIR__ . "/tpl_submit_btn.php";
        }
    }

    /**
     * Output form children. If it is in cms mode then output them wrapped in a div with `section-children-ui-cms` class
     */
    public function output_form_children()
    {
        // if ($this->model->is_cms_page()) {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'admin/cms_update') !== false) { // ugly hack, it should be replaced
            require __DIR__ . "/tpl_cms_children_holder.php";
        } else {
            $this->output_children();
        }
    }

    /**
     * Check if the form sumption is disabled or not
     * @return string 'disabled' | ''
     */
    public function is_disabled(){
        $disabled = $this->model->get_db_field("disabled", false);
        echo $disabled ? 'disabled': '';
    }
}
?>
