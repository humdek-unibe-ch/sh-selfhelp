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
        $entry_list = $this->model->get_entry_list();
        if(!$entry_list){
           return;
        }
        for ($i = 0; $i < count($this->model->get_entry_list()); $i++){
            $entry_data = $entry_list[$i];
            require __DIR__ . "/tpl_entryList_row.php";
        }        
    }

    /* Public Methods *********************************************************/

    /**
     * Render the template.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_entryList.php";
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $entry_list = $this->model->get_entry_list();
        $style['children'] = [];
        for ($i = 0; $i < count($entry_list); $i++){
            $entry_data = $entry_list[$i];            
            $style['children'] = array_merge($style['children'], $this->output_children_mobile_entry($entry_data));
        }
        return $style;
    }
}
?>
