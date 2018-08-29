<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the acl style component.
 */
class AclView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'is_editable' (false).
     * If set to true the checkboxes are editable.
     */
    private $is_editable;

    /**
     * DB field 'title' ("ACL").
     * The title of the column where each acl element is listed.
     */
    private $title;

    /**
     * DB field 'items' (empty array).
     * An array holding the list items
     * An item in the items list must have the following keys:
     *  'id':       The item id (required).
     *  'title':    The title of the item (required).
     *  'children': The children of this item.
     *  'url':      The target url.
     */
    private $items;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->title = $this->model->get_db_field("title", "ACL");
        $this->items = $this->model->get_db_field("items", array());
        $this->is_editable = $this->model->get_db_field("is_editable", false);
    }

    /* Private Methods ********************************************************/

    private function output_items()
    {
        $disabled = ($this->is_editable) ? "" : "disabled";
        foreach($this->items as $key => $acl)
        {
            $name = $acl["name"];
            require __DIR__ . "/tpl_acl_item.php";
        }
    }

    private function output_items_checkbox($key, $checkboxes, $disabled)
    {
        foreach($checkboxes as $level => $item)
        {
            $checked = $item ? "checked" : "";
            require __DIR__ . "/tpl_acl_item_checkbox.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_acl.php";
    }
}
?>
