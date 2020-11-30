<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the template style component.
 * A template style supports the following fields:
 *  'path':     The path to the template to display.
 *  'items':    The fields used in the template.
 */
class TemplateView extends StyleView
{
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

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fields = $this->model->get_db_field("items");
        require $this->model->get_db_field("path");
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
