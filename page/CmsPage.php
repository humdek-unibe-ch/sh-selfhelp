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
     * @param int $id_section
     *  The id of the section that is currently edited (only relevant for
     *  navigation pages).
     */
    public function __construct($router, $db, $keyword, $id_page, $id_section=0)
    {
        $this->cms_page_id = $id_page;
        parent::__construct($router, $db, $keyword);
        $this->add_component("cms",
            new CmsComponent($this->services, $id_page, $id_section));
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
        $key = $this->keyword;
        if(($key == "cms_remove" && !$acl->has_access_delete($uid, $pid))
            || ($key == "cms_show" && !$acl->has_access_select($uid, $pid))
            || ($key == "cms_new" && !$acl->has_access_insert($uid, $pid))
            || ($key == "cms_edit" && !$acl->has_access_update($uid, $pid))
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
