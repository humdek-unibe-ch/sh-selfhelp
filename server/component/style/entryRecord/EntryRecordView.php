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
class EntryRecordView extends StyleView
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
     * Render the record.
     */
    private function output_entry_record()
    {
        $entry_record = $this->model->get_entry_record();
        if (!$entry_record) {
            // no data for that record
            // get the missing page and load it
            $cms_edit = method_exists($this->model, "is_cms_page_editing") && $this->model->is_cms_page_editing();
            if (!$cms_edit) {
                //show missing only if not in CMS mode
                $this->output_missing();
            }
        }
        $this->output_children();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        $this->update_children();
        require __DIR__ . "/tpl_entryRecord.php";
    }

    public function output_content_mobile()
    {        
        $this->update_children();
        return parent::output_content_mobile();
    }
}
?>
