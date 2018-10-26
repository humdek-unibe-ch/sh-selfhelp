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

    private function output_fields()
    {
        $fields = $this->model->get_user_data($this->source);
        if($this->is_log)
            $this->output_fields_log($fields);
        else
            $this->output_fields_doc($fields);
    }

    private function output_fields_doc($fields)
    {
        foreach($fields as $field)
        {
            $label = $field['field_name'];
            $value = $field['value'];
            require __DIR__ . "/tpl_doc_field.php";
        }
    }

    private function output_fields_log($fields)
    {
        $rows = array();
        $header = array($this->label);
        foreach($fields as $field)
        {
            $header[] = $field['field_name'];
            if(!isset($rows[$field['timestamp']]))
                $rows[$field['timestamp']] = array($field['value']);
            else
                $rows[$field['timestamp']][] = $field['value'];
        }
        $header = array_unique($header);

        require __DIR__ . "/tpl_table_header.php";
        require __DIR__ . "/tpl_table_body.php";
    }

    private function output_header_items($header)
    {
        foreach($header as $title)
            require __DIR__ . "/tpl_header_item.php";
    }

    private function output_body_items($rows)
    {
        foreach($rows as $timestamp => $row)
        {
            echo "<tr><td>".$timestamp."</td>";
            foreach($row as $value)
                echo "<td>".$value."</td>";
            echo "</tr>";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->source === "") return;
        require __DIR__ . "/tpl_user_data.php";
    }
}
?>
