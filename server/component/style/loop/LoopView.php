<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The base view class of form field style components.
 * This class provides common functionality that is used for all for field style
 * components.
 */
class LoopView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'formula' (empty array).
     * The formula definition
     */
    private $formula;

    /**
     * DB field 'loop' (empty array).
     * The loop array
     */
    private $loop;

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
        $this->formula = $this->model->get_db_field("formula", array());
        $this->loop = $this->model->get_db_field("loop", array());
    }

    /* Private Methods ********************************************************/


    /* Public Methods ********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $this->output_children();
    }
}
?>
