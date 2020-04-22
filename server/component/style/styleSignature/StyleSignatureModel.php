<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the styleSignature style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class StyleSignatureModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the style for which to show the signature.
     */
    private $name;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->name = $this->get_db_field("name");
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Get the basic style information
     *
     * @retval array
     *  The array as returned by the DB with the following keys:
     *   - `id` the id of the selected style
     *   - `name` the name of the style
     *   - `description` the description of the syle
     *   - `style_group` the name of the group the style belongs to
     *   - `type` the name of the type of the style
     */
    public function get_style_info()
    {
        $sql = "SELECT s.id, s.name, s.description, g.name AS style_group, t.name AS type
            FROM styles AS s
            LEFT JOIN styleGroup AS g ON g.id = s.id_group
            LEFT JOIN styleType AS t ON t.id = s.id_type
            WHERE s.name = :name";
        $res = $this->db->query_db_first($sql, array(
            "name" => $this->name
        ));
        if($res) {
            $res['description'] = $this->parsedown->text($res['description']);
        }
        return $res;
    }

    /**
     * Get the list of fields associated to the style.
     *
     * @param number $id
     *  The id of the style for which to fetch the fields.
     * @retval array
     *  A list of data arrays where each data array has the following keys:
     *   - `name` the name of the field
     *   - `descrtiption` a description of the function of the with respect to
     *     the style in question
     *   - `type` the name of the type of the field
     */
    public function get_style_fields($id)
    {
        $sql = "SELECT f.name, sf.help AS description, ft.name AS type
            FROM styles_fields AS sf
            LEFT JOIN fields AS f ON sf.id_fields = f.id
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            WHERE sf.id_styles = :id";
        return $this->db->query_db($sql, array(
            "id" => $id
        ));
    }

    /**
     * A wrapper to compute the HTML from markdown
     *
     * @param string $text
     *  The markdown text to be parsed
     * @retval string
     *  The HTML string
     */
    public function text_md($text) {
        return $this->parsedown->text($text);
    }
}
?>
