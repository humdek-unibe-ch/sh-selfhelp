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
     * DB field 'edit' (false).
     * A boolean indicating whether the list can be edited.
     */
    private $edit;

    /**
     * DB field 'items' (empty array).
     * An array of items where each item array has the following keys:
     *  'id':       The id of the item.
     *  'title':    The name of the item.
     */
    private $items;

    /**
     * DB field 'insert_target' (empty string).
     * The target url of the insert button. If this is not set, the insert
     * button is not rendered.
     */
    private $insert_target;

    /**
     * DB field 'label' ("Add").
     * The label of the insert button.
     */
    private $insert_label;

    /**
     * DB field 'delete_target' (empty string).
     * The target url of the delete button. Note that the string ':did' is
     * replaced by the id of the element that is supposed to be removed.
     * If this field is not set, the delete buttons are not rendered.
     */
    private $delete_target;

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
        $this->edit = $this->model->get_db_field("edit", false);
        $this->items = $this->model->get_db_field("items", array());
        $this->insert_target = $this->model->get_db_field('insert_target');
        $this->insert_label = $this->model->get_db_field('label', "Add");
        $this->delete_target = $this->model->get_db_field('delete_target');
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
        if(!$this->edit || !$this->is_sortable) return;
        require __DIR__ . "/tpl_list_item_index.php";
    }

    /**
     * Render the delete button on each item.
     */
    private function output_list_item_rm_button($id)
    {
        $url = str_replace(":did", $id, $this->delete_target);
        if(!$this->edit || $url == "") return;
        require __DIR__ . "/tpl_list_item_rm_button.php";
    }

    /**
     * Render the insert button on top of the sortable list.
     */
    private function output_list_new_button()
    {
        $url = $this->insert_target;
        $label = $this->insert_label;
        if(!$this->edit || $url == "") return;
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
        $sortable = ($this->edit && $this->is_sortable) ? "sortable" : "";
        require __DIR__ . "/tpl_list.php";
    }
}
?>
