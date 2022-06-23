<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class VersionModel extends StyleModel
{

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $id
     *  The id of the section.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
    }

    /**
     * Get the dtabase version from the database
     * @retval string the version of the database
     */
    public function get_db_version()
    {
        return $this->db->query_db_first('SELECT version FROM version')['version'];
    }

    /**
     * Get the plugin data as text_md
     * @retval string
     * Return markdown text for the plugins
     */
    public function get_plugins()
    {
        $plugins = $this->db->query_db('SELECT * FROM plugins ORDER BY `name`');
        $plugins_md = '';
        foreach ($plugins as $key => $plugin) {
            $git_command = 'cd server/plugins/' . $plugin['name'] . ' && git describe --tags';
            $plugin_v = rtrim(shell_exec($git_command));
            $plugins_md = $plugins_md . "
            | " . $plugin['name'] . " | " . $plugin_v . "   | | Plugin |
            | " . $plugin['name'] . "_DB | " . $plugin['version'] . "   | | Plugin [DB Info] |";
        }
        return $plugins_md;
    }
}
