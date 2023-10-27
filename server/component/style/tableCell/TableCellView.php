<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the Table Cell style component.
 * A table style is a container that allows to wrap content into table.
 */
class TableCellView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text_md' (empty string).
     */
    protected $text_md;

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
        $this->text_md = $this->model->get_db_field("text_md");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_tableCell.php";
    }

}
?>
