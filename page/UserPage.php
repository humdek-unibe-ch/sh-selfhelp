<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../component/user/UserComponent.php";

/**
 * This class is a wrapper for the CmsComponent for the case where a page id
 * is passed. This is a special case and cannot be treated like a Navigation
 * Page. hence a new calss.
 */
class UserPage extends BasePage
{
    /* Private Properties *****************************************************/

    private $mode;
    private $id_user;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and collects all sections that are allocated to the current page.
     * For each section, a StyleComponent is created and added to the component
     * list of the page.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $keyword
     *  The identification name of the page.
     * @param int $uid
     *  The id of the user that is selected.
     * @param string $mode
     *  The mode of the page: 'select', 'update', 'insert', or 'delete'
     */
    public function __construct($router, $db, $keyword, $mode, $uid = null)
    {
        $this->mode = $mode;
        $this->id_user = $uid;
        parent::__construct($router, $db, $keyword);
        $this->add_component("user",
            new UserComponent($this->services, $uid, $mode));
    }

    /* Private Methods ********************************************************/

    private function does_user_exist($uid)
    {
        if($uid == null)
            return true;
        return true;
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        if($this->does_user_exist($this->id_user))
            $this->output_component("user");
        else
            $this->output_component("denied");
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
