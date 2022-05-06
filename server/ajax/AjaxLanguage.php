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
     * This method set the language in the session and in the user DB
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'locale':   selected language
     * @retval boolean
     * return true if the language is set properly
     */
    public function ajax_set_user_language($data)
    {
        if ($data && $data['id_languages']) {
            $_SESSION['user_language'] = $data['id_languages'];
            $_SESSION['language'] = $data['id_languages'];            
            $this->db->update_by_ids('users', array("id_languages" => $data['id_languages']), array('id' => $_SESSION['id_user'])); // set the language in the user table
            return true;
        } else {
            return true;
        }
    }


    /**
     * Checks wheter the current user is authorised to perform AJAX requests.
     * This function overwrites the default access check and ignores the general
     * ACL settings for AJAX requests
     *
     * @retval boolean
     *  True if authorisation is granted, false otherwise.
     */
    public function has_access($class = "", $method = "")
    {
        return true;
    }
}
?>
