<?php
require_once __DIR__ . "/IStyleModel.php";

/**
 * This class is used to prepare all data related to the base style component
 * such that the data can easily be displayed in the view of the component.
 */
class BaseStyleModel implements IStyleModel
{
    /* Private Properties *****************************************************/

    private $fields;
    private $children;

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $fields
     *  An array containing fields for the view to render to content. The
     *  required are dependent of the style. The array must contain key, value
     *  pairs where the key is the name of the field and the value the content
     *  of the field.
     */
    public function __construct($fields)
    {
        $this->children = array();
        $index = 0;
        foreach($fields as $key => $content)
        {
            if($key == "children")
            {
                $this->children += $content;
                continue;
            }
            $this->fields[$key] = array(
                "content" => $content,
                "type" => "internal",
                "id" => $index
            );
            $index++;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Gets the children components.
     *
     * @return array
     *  An array of children components.
     */
    public function get_children()
    {
        return $this->children;
    }

    /**
     * Returns the content of a data field given a specific key. If the key does
     * not exist an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The content of the field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key)
    {
        $field = $this->get_db_field_full($key);
        if($field == "") return "";
        return $field['content'];
    }

    /**
     * Returns the data field given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field_full($key)
    {
        if(array_key_exists($key, $this->fields))
            return $this->fields[$key];
        else
            return "";
    }

    /**
     * Set the fields that are required by the component model.
     *
     * @param array $fields
     *  An array containing fields for the view to render to content. The
     *  required fields are dependent of the style. The array must contain key,
     *  value pairs where the key is the name of the field and the value an
     *  array with the following keys:
     *   'content': hodling the content of the field that will be rendered.
     *   'type':    the type of the field indication how to render the field.
     *   'id':      a unique numerical value, describing this field.
     */
    public function set_fields_full($fields)
    {
        foreach($fields as $key => $content)
            $this->fields[$key] = $content;
    }
}
?>
