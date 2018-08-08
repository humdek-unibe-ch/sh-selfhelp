<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../component/cms/CmsComponent.php";

/**
 * This class is a wrapper for the CmsComponent for the case where a page id
 * is passed. This is a special case and cannot be treated like a Navigation
 * Page. hence a new calss.
 */
class CmsPage extends BasePage
{
    /* Private Properties *****************************************************/

    private $cms_page_id;
    private $mode;

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
     * @param int $id_page
     *  The id of the page that is currently edited.
     * @param string $mode
     *  The mode of the page: 'select', 'update', 'insert', or 'delete'
     * @param int $id_root_section
     *  The root id of a page or the section that is currently selected.
     * @param int $id_section
     *  The id of the section that is currently selected (only relevant for
     *  navigation pages).
     */
    public function __construct($router, $db, $keyword, $id_page, $mode,
        $id_root_section=null, $id_section=null)
    {
        $this->mode = $mode;
        $this->cms_page_id = $id_page;
        parent::__construct($router, $db, $keyword);
        $this->add_component("cms",
            new CmsComponent($this->services, $id_page, $mode, $id_root_section,
                $id_section));
    }

    /* Private Methods ********************************************************/

    private function does_page_exist($page_id)
    {
        $pages = $this->services['db']->fetch_accessible_pages();
        foreach($pages as $page)
            if($page_id == intval($page['id']))
                return true;
        return false;
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        $acl = $this->services['acl'];
        $uid = $_SESSION['id_user'];
        $pid = $this->cms_page_id;
        if(($this->mode == "delete" && !$acl->has_access_delete($uid, $pid))
            || ($this->mode == "select" && !$acl->has_access_select($uid, $pid))
            || ($this->mode == "insert" && !$acl->has_access_insert($uid, $pid))
            || ($this->mode == "update" && !$acl->has_access_update($uid, $pid))
            || !$this->does_page_exist($pid))
                $this->output_component("denied");
        else
            $this->output_component("cms");
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
