<?php
/**
 * This class is used to prepare all data related to the style component such
 * that the data can easily be displayed in the view of the component.
 */
class StyleModel
{
    /* Private Properties *****************************************************/

    private $title;
    private $content;
    private $url;
    private $link_label;


    /* Constructors ***********************************************************/

    /**
     * The constructor fetches a section itom from the database and assignes
     * the fetched content to private class properties.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The id of the database section item to be rendered.
     */
    public function __construct($router, $db, $id)
    {
        $this->title = "Title";
        $this->content = "Content";
        $sql = "SELECT st.title, st.content, st.link
            FROM sections_translation AS st
            LEFT JOIN languages AS l ON l.id = st.id_languages
            WHERE st.id = :id AND l.locale = :locale";
        $section = $db->query_db_first($sql,
            array(":id" => $id, ":locale" => $_SESSION['language']));
        if($section)
        {
            $this->title = $section['title'];
            $this->content = $section['content'];
            $link = explode('#', $section['link']);
            $this->url = "";
            $this->link_label = "";
            if(count($link) > 1)
            {
                if($link[1] == ":back")
                {
                    if(isset($_SERVER['HTTP_REFERER']))
                        $this->url = htmlspecialchars($_SERVER['HTTP_REFERER']);
                }
                else
                    $this->url = $router->generate($link[1]);
                $this->link_label = $link[0];
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Gets the section title.
     *
     * @retval string
     *  The section title.
     */
    public function get_title() { return $this->title; }

    /**
     * Gets the section content.
     *
     * @retval string
     *  The section content.
     */
    public function get_content() { return $this->content; }

    /**
     * Gets the url of the section link.
     *
     * @retval string
     *  The section link url.
     */
    public function get_url() { return $this->url; }

    /**
     * Gets the label of the section link.
     *
     * @retval string
     *  The section link label.
     */
    public function get_link_label() { return $this->link_label; }

    /**
     * Determines whether the section has a link or not.
     *
     * @retval bool
     *  true if the section has a link, false otherwise.
     */
    public function has_link() { return ($this->url != ""); }
}
?>
