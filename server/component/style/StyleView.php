<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The class to define the basic functionality of a style view.
 */
abstract class StyleView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'css' (null)
     * This field can hold a list of comma seperated css classes. These css
     * classes will be assigned to style wrapper element.
     */
    protected $css;

    /**
     * DB field 'id' (null)
     * The id of the section.
     */
    protected $id_section;

    /**
     * The name of the style.
     */
    protected $style_name;

    /**
     * The list of child components. These components where loaded from the db.
     */
    protected $children;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance that is used to provide the view with data.
     * @param object $controller
     *  The controler instance that is used to handle user interaction.
     */
    public function __construct($model = null, $controller = null)
    {
        parent::__construct($model, $controller);
        $this->style_name = $model->get_style_name();
        $this->children = array();
        if($model != null)
        {
            $this->children = $model->get_children();
            if(method_exists($model, "get_db_field"))
            {
                $this->css = $model->get_db_field("css", null);
                $this->id_section = $model->get_db_field("id", null);
                if($this->id_section !== null)
                {
                    $this->css .= " style-section-" . $this->id_section;
                    if($this->id_section === $_SESSION['active_section_id'])
                        $this->css .= " highlight";
                }
            }
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Checks whether the children array is empty or not.
     *
     * @retval bool
     *  True if there is at least one child, false otherwise.
     */
    protected function has_children()
    {
        return (count($this->children) > 0);
    }

    /**
     * Render the content of all children of this view instance.
     */
    protected function output_children()
    {
        foreach($this->children as $child)
            if($child instanceof StyleComponent
                    || $child instanceof BaseStyleComponent)
                $child->output_content();
            else
                echo "invalid child element of type '" . gettype($child) . "'";
    }

    /**
     * Render the content of all children of this view instance.
     */
    protected function output_children_mobile()
    {
        $res = [];
        foreach ($this->children as $child) {
            if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                $res[] = $child->output_content_mobile();
            } else {
                echo "invalid child element of type '" . gettype($child) . "'";
            }
        }
        return $res;
    }

    public function output_content_mobile()
    {
        $style = $this->model->get_db_fields();
        $style['type'] = 'style';
        $style['style_name'] = $this->style_name;
        $style['css'] = $this->css;
        $style['children'] = $this->output_children_mobile();
        return $style;
    }
}
?>
