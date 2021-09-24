<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the card style component.
 * 
 */
class BookView extends StyleView
{
    /* Private Properties *****************************************************/

    // Config field holidng infor for turn.js
    private $config;

    /**
     * DB field 'label_next' (empty string).
     * The label of the navigation button to go to the next item.
     */
    private $label_next;

    /**
     * DB field 'label_back' (empty string).
     * The label of the navigation button to go to the pervious item.
     */
    private $label_back;

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
        $this->title = $this->model->get_db_field("title");
        $this->config = $this->model->get_db_field("config");
        $this->label_next = $this->model->get_db_field("label_next");
        $this->label_back = $this->model->get_db_field("label_back");
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_book.php";
    }

}
?>
