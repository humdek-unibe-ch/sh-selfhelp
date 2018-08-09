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

    private $section_name;
    private $style_name;
    private $style_type;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches a section item from the database and assignes
     * the fetched content to private class properties.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param int $id_active
     *  The id of the currently active section (this is used for the cms)
     */
    public function __construct($services, $id, $id_active=null)
    {
        parent::__construct($services);
        $this->db_fields['id'] = $id;

        $sql = "SELECT s.id, sec.name, s.name AS style, t.name AS type
            FROM styles AS s
            LEFT JOIN styleType AS t ON t.id = s.id_type
            LEFT JOIN sections AS sec ON sec.id_styles = s.id
            WHERE sec.id = :id";
        $style = $this->db->query_db_first($sql, array(":id" => $id));
        if(!$style) return;
        $this->style_name = $style['style'];
        $this->style_type = $style['type'];
        $this->section_name = $style['name'];
        $this->db_fields['is_active'] = ($id === $id_active);

        $fields = $this->db->fetch_page_fields($this->get_style_name());
        $this->set_db_fields($fields, $id, $id_active);

        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields, $id, $id_active);

        $fields = $this->db->fetch_style_fields($style['id']);
        $this->set_db_fields($fields, $id, $id_active);

        $this->db_fields["content"] = array();
        $db_children = $this->db->fetch_section_children($id);
        foreach($db_children as $child)
            $this->db_fields["content"][] = new StyleComponent(
                $services, intval($child['id']), $id_active);
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
    protected function get_url($url)
    {
        if($url == "#back")
        {
            if(isset($_SERVER['HTTP_REFERER'])
                    && ($_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']))
            {
                return htmlspecialchars($_SERVER['HTTP_REFERER']);
            }
            return $this->router->generate("home");
        }
        else if($url[0] == "#")
        {
            return $this->router->generate(substr($url, 1));
        }
        else
        {
            return $url;
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
    protected function set_db_fields($fields, $id=null, $id_active=null)
    {
        foreach($fields as $field)
        {
            if($field['name'] == "url")
                $field['content'] = $this->get_url($field['content']);
            if($field['type'] == "markdown")
                $field['content'] = $this->parsedown->text($field['content']);
            if($field['type'] == "markdown-inline")
                $field['content'] = $this->parsedown->line($field['content']);
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
        return $this->style_name;
    }

    /**
     * Returns the style type.
     *
     * @retval string
     *  The style type.
     */
    public function get_style_type()
    {
        return $this->style_type;
    }

    /**
     * Returns the section name.
     *
     * @retval string
     *  The section name.
     */
    public function get_section_name()
    {
        return $this->section_name;
    }
}
?>
