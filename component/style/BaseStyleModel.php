<?php
/**
 * This class is used to prepare all data related to the base style component
 * such that the data can easily be displayed in the view of the component.
 */
class BaseStyleModel
{
    /* Private Properties *****************************************************/

    private $fields;

    /* Public Methods *********************************************************/

    /**
     * Returns the data filed given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The content of the filed specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key)
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
     *  required are dependent of the style. The array must contain key, value
     *  pairs where the key is the name of the field and the value the content
     *  of the field.
     */
    public function set_fields($fields)
    {
        $this->fields = $fields;
    }
}
?>
