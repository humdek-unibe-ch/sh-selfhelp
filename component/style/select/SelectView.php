<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the select form style component. This component renders a
 * select form field.
 */
class SelectView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the form selection. If this is not set, the component will
     * not be rendered.
     */
    private $name;

    /**
     * DB field 'items' (empty array).
     * A list of options. where each element has the following keys
     *  'value':    The id of the option item.
     *  'text':     The content of the option item.
     */
    private $items;

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
        $this->items = $this->model->get_db_field("items", array());
        $this->name = $this->model->get_db_field("name");
    }

    /* Private Methods ********************************************************/

    /**
     * Render a select option.
     */
    private function output_fields()
    {
        foreach($this->items as $field)
        {
            $value = $field['value'];
            $text = $field['text'];
            require __DIR__ . "/tpl_select_item.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name == "") return;
        require __DIR__ . "/tpl_select.php";
    }
}
?>
