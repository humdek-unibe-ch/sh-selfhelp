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
        if ($entry_record) {
            $this->output_children_entry($entry_record);
        } else {
            // no data for that record
            $this->sections = $this->model->get_services()->get_db()->fetch_page_sections('missing');
            foreach ($this->sections as $section) {
                $missing_styles =  new StyleComponent($this->model->get_services(), intval($section['id']));
                $missing_styles->output_content();
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_entryRecord.php";
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $entry_record = $this->model->get_entry_record();
        $style['children'] = $this->output_children_mobile_entry($entry_record);
        return $style;
    }
}
?>
