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
        $this->output_children();
        // require __DIR__ . "/tpl_entryList.php";
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
