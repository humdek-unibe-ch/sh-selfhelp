<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the asset select component.
 */
class EntryRecordDeleteView extends StyleView
{

    /* Private Properties *****************************************************/

    /**
     * DB field 'type'.
     * The bootstrap type
     */
    private $type;

    /**
     * DB field 'label_delete'.
     * The label for the delete button
     */
    private $label_delete;

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
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->label_delete = $this->model->get_db_field("label_delete", "");
        $this->type = $this->model->get_db_field("type", "danger");
        $this->confirmation_title = $this->model->get_db_field("confirmation_title", '');
        $this->confirmation_cancel = $this->model->get_db_field("confirmation_cancel", '');
        $this->confirmation_continue = $this->model->get_db_field("confirmation_continue", '');
        $this->confirmation_message = $this->model->get_db_field("confirmation_message");
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the view.
     */
    public function output_content()
    {        
        $delete_record_id = $this->model->get_delete_record_id();
        $delete_form = new BaseStyleComponent('form', array(
            "id" => $this->id_section,
            "type" => $this->type,
            'url' => $_SERVER['REQUEST_URI'],
            'label' => $this->label_delete,
            'disabled' => !$delete_record_id,
            'confirmation_title'=> $this->confirmation_title,
            'confirmation_cancel'=> $this->confirmation_cancel,
            'confirmation_continue'=> $this->confirmation_continue,
            'confirmation_message'=> $this->confirmation_message,
            'css' => "entry-record-delete ". $this->css,
            'children' => array(
                new BaseStyleComponent('input', array(
                    'name' => DELETE_RECORD_ID,                    
                    'value' => $delete_record_id,
                    'type_input' => "hidden",
                ))
            ),
        ));
        $delete_form->output_content();
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style[DELETE_RECORD_ID] = $this->model->get_delete_record_id();
        $redirect_link = str_replace("/", "", $this->model->get_db_field("redirect_at_end", ""));
        $redirect_link = $this->model->get_services()->get_router()->get_url($redirect_link);
        $style['redirect_at_end']['content'] = $redirect_link;
        return $style;
    }
}
?>
