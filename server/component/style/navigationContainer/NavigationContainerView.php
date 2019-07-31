<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the container style component.
 * This style wraps the content that is shown on a navigation page.
 */
class NavigationContainerView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The title of the navigation section. This field is special because it
     * also serves as title for the navigation list item representing this
     * navigation section.
     */
    private $title;

    /**
     * DB field 'text_md' ("<h1>@title</h1>").
     * A markdown text that is placed at the beginning of the container. Use
     * the string '\@title' to print the field 'title'.
     */
    private $text_md;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->title = $this->model->get_db_field('title');
        $this->text_md = $this->model->get_db_field('text_md', "<h1>@title</h1>");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $text = str_replace("@title", $this->title, $this->text_md);
        require __DIR__ . "/tpl_container.php";
    }
}
?>
