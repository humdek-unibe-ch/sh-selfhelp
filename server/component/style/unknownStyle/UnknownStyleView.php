<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the unknown style component.
 */
class UnknownStyleView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'style_name' (empty string).
     * The name of the style that cannot be found.
     */
    private $style_name_na;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        $this->style_name_na = $model->get_db_field("style_name");
        parent::__construct($model);
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_unknown.php";
    }
}
?>
