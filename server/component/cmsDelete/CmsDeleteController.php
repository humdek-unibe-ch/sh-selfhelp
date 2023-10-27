<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms component.
 */
class CmsDeleteController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * The name of the deleted page.
     */
    private $name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->name = "";
        if(isset($_POST["name"]))
        {
            if($this->model->get_active_section_id() == null)
            {
                $info = $this->model->get_page_info();
                if($_POST["name"] == $info["keyword"])
                {
                    if($this->model->delete_page($this->model->get_active_page_id()))
                    {
                        $this->success = true;
                        $this->name = $_POST['name'];
                    }
                    else
                    {
                        $this->fail = true;
                        $this->error_msgs[] = "Failed to delete the page.";
                    }
                }
                else
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to delete the page: The verification text does not match with the page keyword.";
                }
            }
            else
            {
                $info = $this->model->get_section_info();
                if($_POST["name"] == $info["name"])
                {
                    if($this->model->delete_section($this->model->get_active_section_id()))
                    {
                        $this->success = true;
                        $this->name = $_POST['name'];
                    }
                    else
                    {
                        $this->fail = true;
                        $this->error_msgs[] = "Failed to delete the section.";
                    }
                }
                else
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to delete the section: The verification text does not match with the section name.";
                }
            }
            $this->model->get_db()->clear_cache();
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the name of the deleted element.
     *
     * @return string
     *  The name of the deleted element.
     */
    public function get_deleted_name()
    {
        return $this->name;
    }
}
?>
