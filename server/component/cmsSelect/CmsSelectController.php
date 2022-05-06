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
        if (isset($_POST['cms_language'])) {
            $cms_lang = implode(', ', $_POST['cms_language']);
            $_SESSION['cms_language'] = $cms_lang;
        }
        if (isset($_POST['cms_gender'])) {
            $cms_gender = implode(', ', $_POST['cms_gender']);
            $_SESSION['cms_gender'] = $cms_gender;
        }
        if (isset($_POST['gender'])) {            
            $_SESSION['gender'] = $_POST['gender'];
        }
        if (isset($_POST['language'])) {            
            $_SESSION['language'] = $_POST['language'];
        }
    }
}
?>
