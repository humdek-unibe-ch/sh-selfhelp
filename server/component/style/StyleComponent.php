<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleComponent.php";
require_once __DIR__ . "/SimpleStyleComponent.php";
require_once __DIR__ . "/StyleModel.php";

/**
 * The class to define the style component. A style component serves to render
 * section content that is stored in the database with variable views.
 * The views are specified by the style.
 *
 * Styles are registered in the database. A style is loaded by name matching.
 * The name of the style must be matchable to the path and the name of the
 * class that will be instantiated.  A style can either be a simple view or
 * a fully fledget component. Depending on this tha class to be instantiated is
 * postfixe by 'View' or 'Component', respectively.  E.g. when using the view
 * style 'myVStyle' the following class will be loaded and instantiated:
 * 'server/style/myVStyle/MyVStyleView.php' (Note the capital first letter of
 * the class name).
 */
class StyleComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    /**
     * The ID of the section.
     */
    private $id_section;

    /**
     * The component instance of the style.
     */
    private $style = null;

    /**
     * A flag indicating whther the style is known or whether the style name is
     * invalid.
     */
    private $is_style_known;

    /**
     * The parent id if it exists
     */
    private $parent_id;

    /**
     * The relation if the component. Does it belong ot a page or a section, etc
     */
    private $relation;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the StyleModel class and passes
     * the view instance of the style to render to the constructor of the
     * parent class.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  An array of parameter that will be passed to the style component.
     * @param int $id_page
     *  The id of the parent page
     * @param array $entry_record 
     *  An array that contains the entry record information.
     * @param array $manual_style
     *  If the style is set manually and not loaded from the DB, if used internally. We pass the style info. It needs name, type and then the fields
     */
    public function __construct($services, $id, $params=array(), $id_page=-1, $entry_record = array(), $manual_style = array())
    {
        $class = get_class($this);
        $services->get_clockwork()->startEvent("[$class][__construct][$id]");
        $this->id_section = $id;
        if(isset($params['parent_id'])){
            $this->parent_id = $params['parent_id'];
        }
        if(isset($params['relation'])){
            $this->relation = $params['relation'];
        }
        $model = null;
        $this->is_style_known = true;
        // get style name and type
        $db = $services->get_db();
        $style = $db->get_style_component_info($id);
        if (!$style) {
            $style = $manual_style;
        }
        if(!$style) {
            $this->is_style_known = false;
            return;
        }

        if($style['type'] == "view")
        {
            $model = new StyleModel($services, $id, $params, $id_page, $entry_record);
            $this->style = new SimpleStyleComponent($model);
        }
        else if($style['type'] == "component" || $style['type'] == "navigation")
        {
            $className = ucfirst($style['name']) . "Component";
           if (class_exists($className)) {
                $this->style = new $className($services, $id, $params, $id_page, $entry_record, $manual_style);
            }
            if ($this->style === null) {
                $model = new StyleModel($services, $id, $params, $id_page, $entry_record);
                $this->style = new BaseStyleComponent(
                    "unknownStyle",
                    array("style_name" => $style['name'])
                );
            } else if (!$this->style->has_access()) {
                // print access denied or something
                $this->style = new BaseStyleComponent("alert", array(
                    "type" => "danger",
                    "children" => array(new BaseStyleComponent("plaintext", array(
                        "text" => 'No Access'
                    )))
                ));
            } else {
                $model = $this->style->get_model();
            }
        }
        else
        {
            $this->is_style_known = false;
            return;
        }
        $view = $this->style->get_view();
        parent::__construct($model, $view);
        $services->get_clockwork()->endEvent("[$class][__construct][$id]");
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid styles.
     *
     * @retval bool
     *  True if the style is known, false otherwise
     */
    public function has_access()
    {
        return parent::has_access() && $this->is_style_known
            && $this->style->has_access();
    }

    /**
     * Get the ID of the section.
     */
    public function get_id_section()
    {
        return $this->id_section;
    }

    /**
     * Returns the reference to the instance of a style class.
     *
     * @retval reference
     *  Refernce to the style instance class.
     */
    public function &get_style_instance()
    {
        return $this->style;
    }

    /**
     * Search for a child section of a specific name.
     *
     * @param string $name
     *  The name of the section to be seacrhed
     * @retval reference
     *  Reference to the section instance.
     */
    public function &get_child_section_by_name($name)
    {
        return $this->model->get_child_section_by_name($name);
    }

    /**
     * A wrapper function to call the model cms create callback after the
     * creation takes place.
     *
     * @param object $cms_model
     *  The CMS model instance. This is handy to perform operations on db
     *  fields and such.
     * @param string $section_name
     *  The name of the new section.
     * @param int $section_style_id
     *  The style ID of the new section.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * @param int $id
     *  The ID of the new section.
     */
    public function cms_post_create_callback($cms_model, $section_name,
        $section_style_id, $relation, $id)
    {
        $this->model->cms_post_create_callback($cms_model, $section_name,
        $section_style_id, $relation, $id);
    }

    /**
     * A wrapper function to call the model cms update callback after the
     * update takes place.
     *
     * @param object $cms_model
     *  The CMS model instance. This is handy to perform operations on db
     *  fields and such.
     * @param array $data
     *  The submitted data fields to be updated
     */
    public function cms_post_update_callback($cms_model, $data)
    {
        $this->model->cms_post_update_callback($cms_model, $data);
    }

    /**
     * A wrapper function to call the model cms update callback before the
     * update takes place.
     *
     * @param object $cms_model
     *  The CMS model instance. This is handy to perform operations on db
     *  fields and such.
     * @param array $data
     *  The submitted data fields to be updated
     */
    public function cms_pre_update_callback($cms_model, $data)
    {
        $this->model->cms_pre_update_callback($cms_model, $data);
    }
}
?>
