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
     * @return string
     * Return markdown text for the plugins
     */
    public function get_plugins()
    {
        $folders = array_diff(scandir(__DIR__ . '/../../../plugins'), array('..', '.'));
        // Initialize an array to store only directories
        $plugins = [];

        // Iterate through each item
        foreach ($folders as $item) {
            $itemPath = __DIR__ . '/../../../plugins/' . $item;

            // Check if the item is a directory
            if (is_dir($itemPath)) {
                // Get all files ending with 'Hooks.php' in the directory
                $files = glob($itemPath . '/server/component/*Hooks.php');

                // Output the list of matching files
                foreach ($files as $file) {
                    $hookClass = basename($file, '.php');
                    if(class_exists($hookClass)){
                        $hook = new $hookClass($this->services);
                        if (method_exists($hook, 'get_plugin_db_version')){
                            $plugins[$item] = $hook->get_plugin_db_version();
                        }else{
                            $plugins[$item] = 'No data';
                        }
                    }
                }
            }
        }
        $plugins_md = '';
        foreach ($plugins as $key => $plugin) {
            $git_command = 'cd ' . PLUGIN_SERVER_PATH . '/' . $key . ' && git describe --tags';
            $res = $this->db->get_git_version(__DIR__, $git_command);
            $plugin_v = $res ? rtrim($res) : 'No data';
            $plugins_md = $plugins_md . "
            | " . $key . " | " . $plugin_v . " |" . $plugin . "   | | Plugin |";
        }
        return $plugins_md;
    }

    /**
     * Get the libraries data as text_md
     * @return string
     * Return markdown text for the libraries
     */
    public function get_libraries()
    {
        $libraries = $this->db->query_db('SELECT * FROM libraries ORDER BY `name`');
        $libraries_md = '';
        foreach ($libraries as $key => $library) {
            $libraries_md = $libraries_md . "
            | " . $library['name'] . " | " . $library['version'] . "   |" . $library['license'] . " | " . $library['comments'] . " |";
        }
        return $libraries_md;
    }
}
