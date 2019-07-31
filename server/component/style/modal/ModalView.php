<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the modal style component.
 * A modal style allows to render a message box on top of everything.
 */
class ModalView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The title of the modal dialog.
     */
    private $title;

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
        $this->title = $this->model->get_db_field("title");
    }

    /* Private Methods ********************************************************/

    /**
     * Render to title of the modal dialog.
     */
    private function output_title()
    {
        if($this->title === "")
            return;
        require __DIR__ . "/tpl_title.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_modal.php";
    }
}
?>
