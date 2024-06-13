<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the asset select component.
 */
class EntryListView extends StyleView
{

    /* Private Properties *****************************************************/

    /**
     * If enabled, the children are loaded inside a table.
     */
    private $load_as_table;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->load_as_table = $this->model->get_db_field("load_as_table", false);        
    }

    /* Private Methods ********************************************************/

    /**
     * Render the list.
     */
    private function output_list_row()
    {
        foreach ($this->model->get_children() as $key => $child) {
            $entry_record = $child;
            require __DIR__ . "/tpl_entryList_row.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the template.
     */
    public function output_content()
    {
        if ($this->model->get_user_input()->is_there_user_input_change()) {
            $this->model->loadChildren($this->model->get_entry_record());
            $this->set_children($this->model->get_children());
        }
        if ($this->load_as_table) {
            require __DIR__ . "/tpl_entryList_table.php";            
        } else {
            require __DIR__ . "/tpl_entryList_div.php";
        }
    }

    /**
     * Render the content of all children of this view instance as entries
     * @param array $entry_value
     * the data for the entry value
     */
    protected function output_children_entry($entry_record)
    {
        $entry_record->output_content();
    }
}
?>
