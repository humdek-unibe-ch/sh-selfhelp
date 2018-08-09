<?php
require_once __DIR__ . "/../style/StyleModel.php";
/**
 * This class is used to prpare markdown content.
 */
class MarkdownModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services, $id, $id_active=null)
    {
        parent::__construct($services, $id, $id_active);
    }

    /**
     * Parses the markdown text and returns a html string.
     *
     * @retval string
     *  An HTML string parsed from markdown syntax.
     */
    public function get_markdown_text()
    {
        if($this->get_db_field('is_inline'))
            return $this->parsedown->line($this->get_db_field('text'));
        else
            return $this->parsedown->text($this->get_db_field('text'));
    }
}
?>
