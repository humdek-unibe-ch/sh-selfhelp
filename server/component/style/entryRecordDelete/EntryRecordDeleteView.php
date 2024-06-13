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
     * DB field 'delete_title' (empty string).
     * The title of the modal form.
     */
    private $delete_title;

    /**
     * DB field 'delete_content' (empty string).
     * The content of the modal form.
     */
    private $delete_content;

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
        $this->delete_content = $this->model->get_db_field("delete_content", "Do you want to remove the entry?");
        $this->delete_title = $this->model->get_db_field("delete_title", "Remove Entry");
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
}
?>
