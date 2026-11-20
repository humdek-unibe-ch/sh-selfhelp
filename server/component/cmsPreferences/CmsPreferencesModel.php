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

    /**
     * Get the pages which can be chosen for a landing page setting: pages with
     * a route that needs no parameter, for `guest` the open pages and the login
     * page, otherwise the content pages (experiment or open).
     *
     * @param string $setting
     *  One of `id_pages_landing_user`, `id_pages_landing_guest` or
     *  `id_pages_after_login`.
     * @retval array
     *  An array of items with the keys 'value' (page id) and 'text' (keyword),
     *  the first item (value 0) is the default.
     */
    public function get_landing_page_options($setting)
    {
        $guest = $setting === 'id_pages_landing_guest';
        $default = $setting === 'id_pages_after_login' ? "Landing Page (logged in)" : SH_HOME;
        $res = array(array("value" => 0, "text" => "Default (" . $default . ")"));
        $sql = "SELECT id, keyword, url FROM pages
            WHERE (id_type IN (:experiment, :open) OR keyword = :login) AND url IS NOT NULL AND protocol IS NOT NULL
            ORDER BY keyword";
        $pages = $this->db->query_db($sql, array(
            ':experiment' => $guest ? OPEN_PAGE_ID : EXPERIMENT_PAGE_ID,
            ':open' => OPEN_PAGE_ID,
            ':login' => $guest ? SH_LOGIN : ''
        ));
        foreach ($pages as $page) {
            // a required route parameter (`[i:id]` without `?`) cannot be generated
            if (preg_match('/\[[^\]]*\](?!\?)/', $page['url'])) {
                continue;
            }
            $res[] = array("value" => intval($page['id']), "text" => $page['keyword']);
        }
        return $res;
    }

    /**
     * Get the id of a page if it can be chosen for a landing page setting.
     *
     * @param string $setting
     *  The setting, see get_landing_page_options().
     * @param mixed $id
     *  The posted page id.
     * @retval mixed
     *  The posted page id or null for the default (also for an id which is
     *  not in the list of the setting).
     */
    public function get_valid_landing_page_id($setting, $id)
    {
        $id = intval($id);
        foreach ($this->get_landing_page_options($setting) as $option) {
            if ($id > 0 && $option['value'] === $id) {
                return $id;
            }
        }
        return null;
    }

    public function update_cmsPreferences($arr)
    {
        return $this->db->update_by_ids(
            "cmsPreferences",
            $arr,
            array("id" => 1)
        );
    }

    public function get_db(){
        return $this->db;
    }
}
