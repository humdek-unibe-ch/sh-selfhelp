<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The insert view class of the cms component.
 */
class CmsInsertView extends BaseView
{

    /**
     * The array of initial position values of the existing pages. This value
     * will be used when changing the order of pages with javascript.
     */
    private $position_value;

    /**
     * The list of pages at the current level.
     */
    private $pages;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->position_value = "";
        $this->pages = $this->model->get_pages_header(
            $this->model->get_active_page_id());
        foreach($this->pages as $idx => $page)
        {
            $this->position_value .= (string)($idx * 10) . ",";
            $this->pages[$idx]["css"] = "fixed text-muted";
        }

        $this->pages[] = array("id" => "new", "title" => "New Page");
        $this->position_value .= (string)(count($this->pages) * 10);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
    }

    /**
     * Render the sortable list to position the page in the header.
     */
    private function output_page_order()
    {
        $list = new BaseStyleComponent("sortableList", array(
            "is_sortable" => true,
            "is_editable" => true,
            "items" => $this->pages,
        ));
        $list->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/new_page.css");
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/new_page.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $name = $this->controller->get_new_page_name();
            $url = $this->model->get_link_url("cmsUpdate",
                array(
                    "pid" => $this->controller->get_new_pid(),
                    "mode" => "update",
                    "type" => "prop",
                ));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("cmsInsert",
                array("pid" => $this->model->get_active_page_id()));
            $cancel_url = $this->model->get_link_url("cmsSelect",
                array("pid" => $this->model->get_active_page_id()));
            require __DIR__ . "/tpl_cms_insert.php";
        }
    }
}
?>
