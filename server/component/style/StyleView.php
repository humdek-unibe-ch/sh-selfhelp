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
    private $children;

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
            }
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Add include files to the list of includes.
     *
     * @param string $path
     *  The server path to the folder holding include files.
     * @param string $path_prefix
     *  The relative host path to reach the include files.
     * @param reference &$includes
     *  A reference to the array where the include paths will be attached.
     */
    private function get_include_files($path)
    {
        $files = array();
        if($handle = opendir($path)) {
            while(false !== ($file = readdir($handle)))
            {
                if(filetype($path . '/' . $file) === "dir") continue;
                $files[] = $file;
            }
            closedir($handle);
        }
        natcasesort($files);
        return $files;
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
            $child->output_content();
    }

    /* Public Methods *********************************************************/
}
?>
