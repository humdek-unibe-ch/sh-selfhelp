<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the group insert component.
 */
class LanguageController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * The id of the new languege.
     */
    private $lid;

    /**
     * The name of the new languege.
     */
    private $language;

    /**
     * The mode: insert or update
     */
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $lid)
    {
        parent::__construct($model);
        $this->lid = $lid;
        $this->mode = $this->lid > 1 ? "updated" : "created";
        if (isset($_POST['deleteLocale'])) {
            //delete mode
            if ($this->model->get_selected_language()['locale'] == $_POST['deleteLocale']) {
                $res = $this->model->delete_language($this->model->get_selected_language()['lid']);
                if ($res) {
                    $this->mode = "deleted";
                    $this->success = true;
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to delete the language.";
                }
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the language: The verification text does not match with the language locale.";
            }
        } else {
            if (isset($_POST['locale']) && isset($_POST['language']) && isset($_POST['csv_separator'])) {
                if ($this->lid > 1) {
                    // update mode
                    if ($this->model->update_language($this->lid, $_POST['locale'], $_POST['language'], $_POST['csv_separator'])) {
                        $this->language = $_POST['language'];
                        $this->success = true;
                    } else {
                        $this->fail = true;
                        $this->error_msgs[] = "Failed to create a new language.";
                    }
                } else {
                    //insert mode
                    $this->lid = $this->model->insert_new_language($_POST['locale'], $_POST['language'], $_POST['csv_separator']);
                    if ($this->lid) {
                        $this->language = $_POST['language'];
                        $this->success = true;
                    } else {
                        $this->fail = true;
                        $this->error_msgs[] = "Failed to create a new language.";
                    }
                }
            }
        }
    }

    /* Public Methods *********************************************************/

    public function get_new_language()
    {
        return $this->language;
    }

    public function get_mode()
    {
        return $this->mode;
    }
}
?>
