<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the Table style component.
 * A table style is a container that allows to wrap content into table.
 */
class TableView extends StyleView
{

    /* Private Properties *****************************************************/

    /**
     * DB field 'column_names' (empty string).
     * Comma separated list with the column names.
     */
    protected $column_names;

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
        $this->column_names = $this->model->get_db_field("column_names", '');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if (method_exists($this->model, 'is_cms_page_editing') && $this->model->is_cms_page_editing() && $this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            require __DIR__ . "/tpl_cms_table.php";
        } else {
            require __DIR__ . "/tpl_table.php";
        }
    }

    /**
     * Render the column names.
     *
     */
    private function output_column_names()
    {
        foreach (explode(',', $this->column_names) as $column) {
            require __DIR__ . "/tpl_column_title.php";
        }
    }
}
?>
