<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the sortable list style component.
 * The following fields are required.
 *  'is_sortable': A boolean indicating whether the list is sortable or not.
 *  'fields': An array of items where each item array has the following keys:
 *      'id': The id of the item.
 *      'title': The name of the item.
 *  'url': Target url for the new item button.
 *  'label': The label of the new item buton.
 */
class SortableListView extends BaseView
{
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
    }

    /* Private Methods ********************************************************/

    private function output_list_item_index($index, $is_sortable)
    {
        if(!$is_sortable) return;
        require __DIR__ . "/tpl_list_item_index.php";
    }

    private function output_list_item_rm_button($is_sortable)
    {
        $id = $this->model->get_db_field('id_target_rm');
        if(!$is_sortable || $id == "") return;
        require __DIR__ . "/tpl_list_item_rm_button.php";
    }

    private function output_list_items($items, $is_sortable)
    {
        foreach($items as $index => $item)
        {
            $id = $item['id'];
            $name = $item['title'];
            require __DIR__ . "/tpl_list_item.php";
        }
    }

    private function output_list_new_button($is_sortable)
    {
        $id = $this->model->get_db_field('id_target_insert');
        $label = $this->model->get_db_field('label');
        if(!$is_sortable || $id == "" || $label == "") return;
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
        $is_sortable = $this->model->get_db_field("is_sortable");
        if($is_sortable == "") $is_sortable = false;
        $sortable = ($is_sortable) ? "sortable" : "";
        $items = $this->model->get_db_field("items");
        require __DIR__ . "/tpl_list.php";
    }
}
?>
