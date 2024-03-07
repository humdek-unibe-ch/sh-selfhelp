<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the div style component.
 * A div style is a container that allows to wrap content into a div tag.
 */
class TagView extends StyleView
{
    /* Constructors ***********************************************************/

    /**
     * Id used for html element
     */
    private $id;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->id = $this->model->get_db_field("id", $this->id_section);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_tag.php";
    }
	
}
?>
