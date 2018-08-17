<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the sortable list style component.
 */
class SortableListView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'is_sortable' (false).
     * A boolean indicating whether the list is sortable or not.
     */
    private $is_sortable;

    /**
     * DB field 'items' (empty array).
     * An array of items where each item array has the following keys:
     *  'id':       The id of the item.
     *  'title':    The name of the item.
     */
    private $items;

    /**
     * DB field 'id_target_insert' (empty string).
     * A unique aid that will allow to handle click events on the insert button.
     * If this is not set, the insert button is not rendered.
     */
    private $id_insert;

    /**
     * DB field 'id_target_insert' (empty string).
     * The label of the insert button. If this is not set, the insert button is
     * not rendered.
     */
    private $label_insert;

    /**
     * DB field 'id_target_rm' (empty string).
     * A unique id that will allow to handle click events on delete buttons.
     * If this is not set, the delete buttons are not rendered.
     */
    private $id_delete;

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
        $this->is_sortable = $this->model->get_db_field("is_sortable", false);
        $this->items = $this->model->get_db_field("items", array());
        $this->id_insert = $this->model->get_db_field('id_target_insert');
        $this->label_insert = $this->model->get_db_field('label');
        $this->id_delete = $this->model->get_db_field('id_target_rm');
    }

    /* Private Methods ********************************************************/

    /**
     * Render the items of the sortable list.
     */
    private function output_list_items()
    {
        foreach($this->items as $index => $item)
        {
            $id = $item['id'];
            $name = $item['title'];
            require __DIR__ . "/tpl_list_item.php";
        }
    }

    /**
     * Render the index badge in front of each sortable item.
     */
    private function output_list_item_index($index)
    {
        if(!$this->is_sortable) return;
        require __DIR__ . "/tpl_list_item_index.php";
    }

    /**
     * Render the delete button on each item.
     */
    private function output_list_item_rm_button()
    {
        $id = $this->id_delete;
        if(!$this->is_sortable || $id == "") return;
        require __DIR__ . "/tpl_list_item_rm_button.php";
    }

    /**
     * Render the insert button on top of the sortable list.
     */
    private function output_list_new_button()
    {
        $id = $this->id_insert;
        $label = $this->label_insert;
        if(!$this->is_sortable || $id == "" || $label == "") return;
        require __DIR__ . "/tpl_list_new_button.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/sortableList.css");
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(
            __DIR__ . "/sortable.min.js",
            __DIR__ . "/sortable.jquery.binding.js",
        );
        return parent::get_js_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $sortable = ($this->is_sortable) ? "sortable" : "";
        require __DIR__ . "/tpl_list.php";
    }
}
?>
