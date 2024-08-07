<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the json component.
 * This style component is one of the main styles to produce content. This
 * allows to display json encoded base styles.
 */
class JsonView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'json' (empty string).
     * The text to be rendered as nested Base Styles content.
     */
    private $json;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the json component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->json = $this->model->get_db_field('json');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the view.
     */
    public function output_content()
    {
        echo $this->model->json_style_parse($this->json);
    }

}
?>
