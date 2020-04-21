<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the cms select component. This controller serves to
 * update CMS settings such as the cms language and gender settings.
 */
class CmsSelectController extends BaseController
{
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
        if(isset($_POST['cms_language']))
        {
            $is_data_ok = false;
            foreach($this->model->get_languages() as $language)
                if($_POST['cms_language'] === $language['locale'])
                {
                    $is_data_ok = true;
                    break;
                }
            if($is_data_ok || $_POST['cms_language'] === "all")
                $_SESSION['cms_language'] = $_POST['cms_language'];
        }
        if(isset($_POST['cms_gender']))
        {
            if(in_array($_POST['cms_gender'], array("both", "female", "male")))
                $_SESSION['cms_gender'] = $_POST['cms_gender'];
        }
    }
}
?>
