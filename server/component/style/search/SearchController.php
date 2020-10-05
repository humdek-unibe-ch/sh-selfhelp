<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";

/**
 * The controller class of Search style component.
 */
class SearchController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if (count($_POST) === 0) {
            return;
        }
        if (!isset($_POST['search_param'])) {
            return;
        }
        $keyword = $this->model->get_services()->get_router()->get_keyword_from_url();
        header('Location: ' . $this->model->get_link_url($keyword) . '/' . $this->get_search_param());
    }

    /* Private Methods *********************************************************/

    /**
     * Return the text that will be append to the url
     * @retval string
     * Return the text that will be append to the url
     */
    private function get_search_param(){
        return $this->model->get_db_field('prefix', '') . $_POST['search_param'] . $this->model->get_db_field('suffix', '');
    }
}
?>
