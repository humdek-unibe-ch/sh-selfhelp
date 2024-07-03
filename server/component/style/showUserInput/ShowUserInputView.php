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
     * DB field 'anchor' (empty string).
     * The id of a anchor section to jump to on delete submit.
     */
    protected $anchor;

    /**
     * DB field 'source' (empty string).
     * The name of the form from which the data will be fetched for the current
     * user. If this field is left empty, the userData style will not be
     * rendered.
     */
    private $data_table;

    /**
     * The source string transformed into a alphanumeric string.
     */
    private $source_an;

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

    /**
     * If enabled the `showUserInput` will load only the records entered by the user.
     */
    private $own_entries_only = 1;

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
        $this->data_table = $this->model->get_db_field("data_table");
        $this->anchor = $this->model->get_db_field("anchor");
        $this->source_an = $this->model->get_user_input()->convert_to_valid_html_id($this->data_table);
        $this->is_log = $this->model->get_db_field("is_log", false);
        $this->label = $this->model->get_db_field("label_date_time", "Entry Date");
        $this->label_delete = $this->model->get_db_field("label_delete", "");
        $this->delete_content = $this->model->get_db_field("delete_content", "Do you want to remove the entry?");
        $this->delete_title = $this->model->get_db_field("delete_title", "Remove Entry");
        $this->can_delete = $this->label_delete != "";        
        $this->own_entries_only = $this->model->get_db_field("own_entries_only", 1);
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
        if($this->can_delete)
        {
            $target = "modal-" . $this->source_an;
            $record_id = $cols[0];
            require __DIR__ . "/tpl_delete.php";
        }
        foreach($cols as $id => $value)
        {
            if($id !== 0)
                $id = 'id="user-input-field-' . $id . '"';
            else
                $id = "";
            $this->output_field($value);
        }        
    }

    /**
     * Render a table field.
     *     
     * @param array $value
     *  The value to be displayed in the table field.
     */
    private function output_field($value)
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
        foreach($fields[0] as $label => $value)
        {
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
        foreach($fields as $row_id => $row) {
            foreach ($row as $column => $value) {
                $header[] = $column;
                $rows[$row[ENTRY_RECORD_ID]][] = $value;
            }                    
        }        
        $header = array_unique($header);
        if($this->can_delete)
            array_unshift($header, ""); // add it on top

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
        $anchor = $this->anchor ? "#section-" . $this->anchor : "";
        $modal = new BaseStyleComponent('modal', array(
            'id' => "modal-" . $this->source_an,
            'title' => $this->delete_title,
            'children' => array(
                new BaseStyleComponent('markdown', array(
                    "text_md" => $this->delete_content,
                )),
                new BaseStyleComponent('form', array(
                    "id" => $this->id_section,
                    "type" =>'danger',
                    'url' => $_SERVER['REQUEST_URI'] . $anchor,
                    'label' => $this->label_delete,
                    'children' => array(
                        new BaseStyleComponent('input', array(
                            'name' => DELETE_RECORD_ID,
                            'type_input' => "hidden",
                        ))
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
        if($this->data_table === "") return;
        $fields = $this->model->get_user_data($this->data_table, $this->own_entries_only);
        if(count($fields) === 0) return;
        require __DIR__ . "/tpl_user_data.php";
    }

    public function output_content_mobile()
    {        
        $style = parent::output_content_mobile();
        $style['fields'] = $this->model->get_user_data($this->data_table, $this->own_entries_only);
        $style['can_delete'] = $this->can_delete;
        return $style;
    }
}
?>
