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
class DataContainerView extends StyleView
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


    /* Public Methods *********************************************************/

    /**
     * Render the view.
     */
    public function output_content()
    {
        if ($this->model->get_user_input()->is_there_user_input_change()) {
            $this->model->loadChildren($this->model->get_entry_record());
            $this->set_children($this->model->get_children());
        }
        require __DIR__ . "/tpl_dataContainer.php";
    }

    /**
     * Return the mobile json structure for rendering
     */
    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style['style_name'] = 'div'; // this style could be handled by the div style
        return $style;
    }

}
?>
