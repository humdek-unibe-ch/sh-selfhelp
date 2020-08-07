<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * A small class to allow to fetch static or dynamic datat from the DB. This
 * class is used for AJAX calls.
 */
class AjaxLanguage extends BaseAjax
{
    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /* Public Methods *********************************************************/

    /**
     * The search function which can be called by an AJAx call.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'language_id':   selected language_id
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The email of the user.
     *   - 'id':        The id of the user.
     */
    public function set_user_language($data)
    {
        if($data && $data['locale']){
            $_SESSION['user_language'] = $data['locale'];
            $_SESSION['language'] = $data['locale'];
        } 
        return false;
    }


    /**
     * Checks wheter the current user is authorised to perform AJAX requests.
     * This function overwrites the default access check and ignores the general
     * ACL settings for AJAX requests
     *
     * @retval boolean
     *  True if authorisation is granted, false otherwise.
     */
    public function has_access($class="", $method="")
    {
        return true;
    }
}
?>
