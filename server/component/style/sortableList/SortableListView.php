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
     * DB field 'is_editable' (false).
     * A boolean indicating whether the list can be edited.
     */
    private $is_editable;

    /**
     * DB field 'items' (empty array).
     * An array of items where each item array has the following keys:
     *  'id':       The id of the item.
     *  'title':    The name of the item.
     *  'url':      The target url of the item (not required).
     *  'css':      The name of a css class (not required).
     */
    private $items;

    /**
     * DB field 'url_add' (empty string).
     * The target url of the insert button. If this is not set, the insert
     * button is not rendered.
     */
    private $insert_target;

    /**
     * DB field 'label_add' ("Add").
     * The label of the insert button.
     */
    private $insert_label;

    /**
     * DB field 'url_delete' (empty string).
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
        $this->is_editable = $this->model->get_db_field("is_editable", false);
        $this->items = $this->model->get_db_field("items", array());
        if(!is_array($this->items)) $this->items = array();
        $this->insert_target = $this->model->get_db_field('url_add');
        $this->insert_label = $this->model->get_db_field('label_add', "Add");
        $this->delete_target = $this->model->get_db_field('url_delete');
        $this->id_active = $this->model->get_db_field('id_active', null);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the label of an item.
     */
    private function output_label($name, $url)
    {
        if($url == "" || ($this->is_editable && $this->is_sortable))
            echo '<span class="label">' . $name . '</span>';
        else
            require __DIR__ . "/tpl_link.php";
    }

    /**
     * Render the items of the sortable list.
     */
    private function output_list_items()
    {
        foreach($this->items as $index => $item)
        {
            if(!isset($item['id']) || !isset($item['title'])) continue;
            $id = $item['id'];
            $name = $item['title'];
            $url = (isset($item['url'])) ? $item['url'] : "";
            $css = (isset($item['css'])) ? $item['css'] : "";
            require __DIR__ . "/tpl_list_item.php";
        }
    }

    /**
     * Render the index badge in front of each sortable item.
     */
    private function output_list_item_index($index)
    {
        if(!$this->is_editable || !$this->is_sortable) return;
        require __DIR__ . "/tpl_list_item_index.php";
    }

    /**
     * Render the delete button on each item.
     */
    private function output_list_item_rm_button($id)
    {
        $url = str_replace(":did", $id, $this->delete_target);
        if(!$this->is_editable || $url == "") return;
        require __DIR__ . "/tpl_list_item_rm_button.php";
    }

    /**
     * Render the insert button on top of the sortable list.
     */
    private function output_list_new_button()
    {
        $url = $this->insert_target;
        $label = $this->insert_label;
        if(!$this->is_editable || $url == "") return;
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
            __DIR__ . "/sortableList.js",
        );
        return parent::get_js_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $sortable = ($this->is_editable && $this->is_sortable) ? "sortable" : "";
        require __DIR__ . "/tpl_list.php";
    }
}
?>
