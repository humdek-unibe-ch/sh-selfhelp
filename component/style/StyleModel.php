<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/StyleComponent.php";
/**
 * This class is used to prepare all data related to the style component such
 * that the data can easily be displayed in the view of the component.
 */
class StyleModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $section;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches a section item from the database and assignes
     * the fetched content to private class properties.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $children
     *  An array of StyleComponent() obejcts, representing the children of this
     *  style.
     */
    public function __construct($router, $db, $id, $children)
    {
        parent::__construct($router, $db);
        $this->section = $db->select_by_uid_join("sections", $id);

        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields);

        $this->db_fields["content"] = $children;
    }

    /* Private Methods ********************************************************/

    /**
     * Returns an url given a router keyword. The keyword :back will generate
     * the url of the last visited page or the home page if the last visited
     * page is the current page or unknown.
     *
     * @retval string
     *  The generated url.
     */
    private function get_url($url)
    {
        if($url == ":back")
        {
            if(isset($_SERVER['HTTP_REFERER'])
                    && ($_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']))
            {
                return htmlspecialchars($_SERVER['HTTP_REFERER']);
            }
            return $this->router->generate("home");
        }
        else
        {
            return $this->router->generate($url);
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Overrides the method BaseModel::set_db_fields($fields).
     * Set the db_fields attribute of the model. Each field is assigned as an
     * key => value element where the key is the field name and the value the
     * field content.
     *
     * If the field name is 'url', a specifig url is generated. See
     * StyleModel::get_url($url).
     *
     * @param array $fields
     *  An array of field items where one item is an associative array of the
     *  form:
     *   "name" => name of the db field
     *   "content" => the content of the db field
     */
    protected function set_db_fields($fields)
    {
        foreach($fields as $field)
        {
            if($field['name'] == "url")
                $field['content'] = $this->get_url($field['content']);
            $this->db_fields[$field['name']] = $field['content'];
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the db field array where each field item is stores as a key,
     * value pair. The key corresponds to the name of the field and the value to
     * the content of the field.
     *
     * @retval array
     *  The key, value pairs describing data fields.
     */
    public function get_db_fields()
    {
        return $this->db_fields;
    }

    /**
     * Returns the style name. This will be used to load the corresponding
     * template.
     *
     * @retval string
     *  The style name.
     */
    public function get_style_name()
    {
        return $this->section['name_styles'];
    }
}
?>
