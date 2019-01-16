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
        $this->source = $this->model->get_db_field("source");
        $this->is_log = $this->model->get_db_field("is_log", false);
        $this->label = $this->model->get_db_field("label_date_time", "Date");
    }

    /* Private Methods ********************************************************/

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
            require __DIR__ . "/tpl_doc_field.php";
        }
    }

    /**
     * Render all colomns of a row.
     *
     * @param array $cols
     *  An array of values top be rendered in one row.
     */
    private function output_cols($cols)
    {
        foreach($cols as $value)
            $this->output_field($value);
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
            $rows[$field['timestamp']][] = $field['value'];
        }
        $header = array_unique($header);

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
