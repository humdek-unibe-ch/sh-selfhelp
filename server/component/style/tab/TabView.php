<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the tab style component.
 */
class TabView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' (empty string).
     * The label of the tab button. If this field id not set, the tab is not
     * rendered.
     */
    private $label;

    /**
     * DB field 'type' ('light').
     * The style of the button.
     */
    private $type;

    /**
     * DB field 'id' (0).
     * A unique identifier for a tab.
     */
    private $id;

    /**
     * DB field 'is_expanded' (false).
     * If set to true the tab is expanded by default.
     */
    private $is_expanded;

    /**
     * DB field 'icon' (emty string).
     * If it is set to a font awsome icon, it will be loaded
     */
    private $icon;

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
        $this->label = $this->model->get_db_field("label");
        $this->icon = $this->model->get_db_field("icon", '');
        $this->type = $this->model->get_db_field("type", "light");
        $this->id = $this->model->get_db_field("id", 0);
        $this->is_expanded = $this->model->get_db_field("is_expanded", false);
    }

    /* Private Methods ********************************************************/

    /**
     * Return icon value for web if it exists
     * @param string $icon
     * icon value form cms
     * @retval string or false
     * Return the icon value or false if none set 
     */
    private function get_icon($icon)
    {
        $icons = explode(' ', $icon);
        if (count($icons) > 0) {
            foreach ($icons as $key => $iconValue) {
                if (strpos($iconValue, 'mobile-') === 0) {
                    // not needed for web
                } else {
                    return $iconValue;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    private function output_icon($icon){
        if($icon){
            require __DIR__ .'/tpl_icon.php';
        }
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->label == "" && $this->icon == "") return;
        $active = $this->is_expanded ? "active" : "";    
        $icon = $this->get_icon($this->icon);    
        require __DIR__ . "/tpl_tab.php";
    }    
}
?>

