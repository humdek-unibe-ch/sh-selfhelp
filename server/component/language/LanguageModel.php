<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../cmsPreferences/CmsPreferencesModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class LanguageModel extends CmsPreferencesModel
{

    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/

    /**
     * An array of language properties (see UserModel::fetch_language).
     */
    private $selected_language;

    /**
     * The id of the current selected language.
     */
    private $lid;

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $lid)
    {
        parent::__construct($services);
        $this->lid = $lid;
        $this->selected_language = null;
        if($lid != null) $this->selected_language = $this->db->fetch_language($lid);
    }

    /* Private Methods ********************************************************/    

    /* Public Methods ********************************************************/

    /**
     * Delete a language from the database.
     *
     * @param int $lid
     *  The id of the language to be deleted.
     * @retval bool
     *  True on success, false on failure.
     */
    public function delete_language($lid)
    {
        return $this->db->remove_by_fk("languages", "id", $lid);
    }

    public function get_language_id(){
        return $this->lid;
    }

    public function get_selected_language(){
        return $this->selected_language;
    }

    /**
     * Insert a new language to the DB.
     *
     * @param string $locale
     *  locale
     * @param string $language
     *  language
     * * @param string $csv_separator
     *  csv_separator
     * @retval int
     *  The id of the new language or false if the process failed.
     */
    public function insert_new_language($locale, $language, $csv_separator)
    {
        return $this->db->insert("languages", array(
            "locale" => $locale,
            "language" => $language,
            "csv_separator" => $csv_separator
        ));
    }

    /**
     * Update language to the DB.
     * @param string $lid
     * language id
     * @param string $locale
     *  locale
     * @param string $language
     *  language
     * * @param string $csv_separator
     *  csv_separator
     * @retval bool
     *  True or false if the process failed.
     */
    public function update_language($lid, $locale, $language, $csv_separator)
    {
        return $this->db->update_by_ids(
            "languages",
            array(
                "locale" => $locale,
                "language" => $language,
                "csv_separator" => $csv_separator
            ),
            array('id' => $lid)
        );
    }

}