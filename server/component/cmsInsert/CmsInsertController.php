<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms insert component.
 */
class CmsInsertController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * The id of the new page.
     */
    private $pid;

    /**
     * The name of the new page.
     */
    private $name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms insert component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->success = false;
        $this->fail= false;
        $this->pid = null;
        $this->name = "";
        if(isset($_POST['keyword']) && isset($_POST['url'])
            && isset($_POST['protocol']) && isset($_POST['type']))
        {
            $this->name = filter_var($_POST["keyword"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $keyword_error = $this->model->get_keyword_error($this->name);
            if ($keyword_error) {
                $this->fail = true;
                $this->error_msgs[] = $keyword_error; // the name is already html encoded by the filter
                return;
            }
            foreach($_POST['protocol'] as $protocol)
                if(!in_array($protocol, CmsModel::PAGE_PROTOCOLS, true))
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to create new page: Invalid protocol settings.";
                    return;
                }
            $protocol = implode('|', $_POST['protocol']);
            $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
            $type = filter_var($_POST['type'], FILTER_SANITIZE_NUMBER_INT);
            if(!$url && $this->name && $type)
            {
                // The url field is filled by javascript while typing the
                // keyword. Derive it if it was left empty (e.g. autofill/paste).
                $url = '/' . $this->name . (intval($type) == 4 ? '/[i:nav]' : '');
            }
            if(!$this->name || !$url || !$type)
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to create new page: Insufficient parameters provided.";
                return;
            }
            $position = null;
            if(isset($_POST['set-position']))
            {
                $position = array();
                foreach(explode(',', $_POST['set-position']) as $item)
                    $position[] = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
            }
            $is_headless = false;
            if(isset($_POST['set-headless']))
                $is_headless = true;
            $is_open = false;
            if(isset($_POST['set-open']))
                $is_open = true;

            $this->pid = $model->create_new_page($this->name, $url, $protocol,
                intval($type), $position, $is_headless, $is_open,
                $this->model->get_active_page_id(), $_POST['id_pageAccessTypes']);
            if($this->pid)
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to create new page.";
            }
            $this->model->get_db()->clear_cache();
            if($this->success)
            {
                // Go straight to the editor of the new page, so sections can be
                // added right away instead of showing an extra success screen.
                header('Location: ' . $this->model->get_link_url("cmsUpdate", array(
                    "pid" => $this->pid,
                    "mode" => "update",
                    "type" => "prop",
                )));
                exit();
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the newly created page id.
     *
     * @return int
     *  The newly created page id.
     */
    public function get_new_pid()
    {
        return $this->pid;
    }

    /**
     * Return the newly created page name.
     *
     * @return int
     *  The newly created page name.
     */
    public function get_new_page_name()
    {
        return $this->name;
    }
}
?>
