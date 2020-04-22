<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class CmsPreferencesModel extends BaseModel
{

    /* Private Properties *****************************************************/

    private $cmsPreferences;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $this->pull_cmsPreferences();
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    public function get_cmsPreferences()
    {
        return $this->cmsPreferences;
    }

    public function pull_cmsPreferences(){
        $this->cmsPreferences = $this->db->fetch_cmsPreferences()[0];
    }

    /**
     * Checks whether the current user is allowed to create new language.
     *
     * @retval bool
     *  True if the current user can create new language, false otherwise.
     */
    public function can_create_new_language()
    {
        return $this->acl->has_access_insert(
            $_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("cmsInsert")
        );
    }

    /**
     * Get a list of languages and prepares the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':      The id of the language.
     *   'locale':   
     *   'language':   
     *   'csv_separator':
     */
    public function get_languages()
    {
        $res = array();
        foreach ($this->db->fetch_languages() as $language) {
            $id = intval($language["id"]);
            $res[] = array(
                "id" => $id,
                "title" => $language["language"],
                "url" => $this->get_link_url("language", array("lid" => $id))
            );
        }
        return $res;
    }

    public function update_cmsPreferences($arr)
    {
        return $this->db->update_by_ids(
            "cmsPreferences",
            $arr,
            array("id" => 1)
        );
    }

    /**
     * Enable or disable module
     *  
     * @param string $module_name
     * @param int $enabled 1 true, 0 false 
     *      
     */
    public function update_module_status($module_name, $enabled)
    {
        $sql = "UPDATE pages SET enabled = :enabled
            WHERE keyword = :module_name";
        return $this->db->execute_update_db($sql, array(
            ':enabled' => $enabled,
            ':module_name' => $module_name,
        ));
    }

    public function get_db(){
        return $this->db;
    }
}
