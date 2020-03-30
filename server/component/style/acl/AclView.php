<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the acl style component.
 * This style component allows to display ACL tables. It is not made available
 * to the CMS in is only used internally.
 */
class AclView extends StyleView
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
     * A list of key value pairs where the key is the page keyword and the
     * value an array of booleans, indicating the access rights select,
     * insert, update, and delete (in this order).
     */
    private $items;

    /**
     * DB field 'items_granted' (empty array).
     * A list of key value pairs where the key is the page keyword and the
     * value an array of booleans, indicating the access rights select,
     * insert, update, and delete (in this order).
     */
    private $items_granted;

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
        $this->items_granted = $this->model->get_db_field("items_granted",
            array());
        $this->is_editable = $this->model->get_db_field("is_editable", false);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the ACL items.
     */
    private function output_items()
    {
        $is_disabled = !$this->is_editable;
        foreach($this->items as $key => $acl)
        {
            $name = $acl["name"];
            require __DIR__ . "/tpl_acl_item.php";
        }
    }

    /**
     * Render all checkboxes of an ACL item.
     *
     * @param string $key
     *  The keyword of the page this item is associated to.
     * @param array $checkboxes
     *  The array of access rights with the four keys 'select', 'insert',
     *  'update', and 'delete'.
     * @param boolean $is_disabled
     *  Holds either an empty string if the checkbox is enabled or the html
     *  disabled attribute if the checkbox is disabled.
     */
    private function output_items_checkbox($key, $checkboxes, $is_disabled)
    {                       
        foreach($checkboxes as $level => $item)
        {           
            if($is_disabled || isset($this->items_granted[$key]["acl"][$level])
                    && !$this->items_granted[$key]["acl"][$level])
            {
                $disabled = "disabled";                
            }else{
                $disabled = "";                
            }      
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
