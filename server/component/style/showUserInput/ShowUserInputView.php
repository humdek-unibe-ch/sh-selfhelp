<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the showUserInput style component. This component renders
 * user data from the database. See ShowUserInputComponent for more details.
 */
class ShowUserInputView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'source' (empty string).
     * The name of the form from which the data will be fetched for the current
     * user. If this field is left empty, the userData style will not be
     * rendered.
     */
    private $source;

    /**
     * DB field 'is_log' (false).
     * If set to true the data will be listed as time-stamped elements per each
     * field.
     */
    private $is_log;

    /**
     * DB field 'label_date_time' ("Date").
     * The labe to be used for the timestamp column for log form data.
     */
    private $label;

    /**
     * DB field 'label_delete' (false).
     * If set a subject is allowed to mark entries as deleted.
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

    /**
     * Internal attribute to track wether a subject can mark fields as deleted.
     */
    private $can_delete = false;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->source = $this->model->get_db_field("source");
        $this->is_log = $this->model->get_db_field("is_log", false);
        $this->label = $this->model->get_db_field("label_date_time", "Date");
        $this->label_delete = $this->model->get_db_field("label_delete", "Remove");
        $this->delete_content = $this->model->get_db_field("delete_content", "Do you want to remove the entry?");
        $this->delete_title = $this->model->get_db_field("delete_title", "Remove Entry");
        $this->can_delete = $this->label_delete != "";
    }

    /* Private Methods ********************************************************/

    /**
     * Render the body items of a log form.
     *
     * @param array $rows
     *  The rows of fields to be rendered.
     */
    private function output_body_items($rows)
    {
        foreach($rows as $cols)
            require __DIR__ . "/tpl_table_row.php";
    }

    /**
     * Render all colomns of a row.
     *
     * @param array $cols
     *  An array of values top be rendered in one row.
     */
    private function output_cols($cols)
    {
        foreach($cols as $id => $value)
        {
            if($id !== 0)
                $id = 'id="user-input-field-' . $id . '"';
            else
                $id = "";
            $this->output_field($id, $value);
        }
        if($this->can_delete)
        {
            $target = "modal-" . $this->source;
            require __DIR__ . "/tpl_delete.php";
        }
    }

    /**
     * Render a table field.
     *
     * @param array $id
     *  The field id to be referenced in HTML.
     * @param array $value
     *  The value to be displayed in the table field.
     */
    private function output_field($id, $value)
    {
        require __DIR__ . "/tpl_field.php";
    }

    /**
     * Render the form fields.
     *
     * @param array $fields
     *  An array of form fields.
     */
    private function output_fields($fields)
    {
        if($this->is_log)
            $this->output_fields_log($fields);
        else
            $this->output_fields_doc($fields);
    }

    /**
     * Render the form fields of a non-log form.
     *
     * @param array $fields
     *  An array of form fields.
     */
    private function output_fields_doc($fields)
    {
        foreach($fields as $field)
        {
            $label = $field['field_label'];
            $value = $field['value'];
            $id = intval($field['id']);
            require __DIR__ . "/tpl_doc_field.php";
        }
    }

    /**
     * Render the form fields of a log form.
     *
     * @param array $fields
     *  An array of form fields.
     */
    private function output_fields_log($fields)
    {
        $rows = array();
        $header = array($this->label);
        foreach($fields as $field)
        {
            $header[] = $field['field_label'];
            if(!isset($rows[$field['timestamp']]))
                $rows[$field['timestamp']] = array($field['timestamp']);
            $rows[$field['timestamp']][intval($field['id'])] = $field['value'];
        }
        $header = array_unique($header);
        if($this->can_delete)
            $header[] = "";

        require __DIR__ . "/tpl_table_header.php";
        require __DIR__ . "/tpl_table_body.php";
    }

    /**
     * Render the header items of a log form.
     *
     * @param array $header
     *  The header items.
     */
    private function output_header_items($header)
    {
        foreach($header as $title)
            require __DIR__ . "/tpl_header_item.php";
    }

    /**
     * Render to modal dialog
     */
    private function output_modal()
    {
        $modal = new BaseStyleComponent('modal', array(
            'id' => "modal-" . $this->source,
            'title' => $this->delete_title,
            'children' => array(
                new BaseStyleComponent('markdown', array(
                    "text_md" => $this->delete_content,
                )),
                new BaseStyleComponent('form', array(
                    "type" =>'danger',
                    'url' => $_SERVER['REQUEST_URI'],
                    'label' => $this->label_delete,
                    'children' => array(
                        new BaseStyleComponent('input', array(
                            'name' => 'user_input_remove_id',
                            'type_input' => "hidden",
                        )),
                    ),
                )),
            ),
        ));
        $modal->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->source === "") return;
        $fields = $this->model->get_user_data($this->source);
        if(count($fields) === 0) return;
        require __DIR__ . "/tpl_user_data.php";
    }
}
?>
