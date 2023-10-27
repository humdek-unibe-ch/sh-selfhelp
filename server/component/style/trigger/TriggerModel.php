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
class TriggerModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'plugin'
     * What plugin will be triggerd.
     */
    private $plugin;

    /**
     * Section id
     */
    protected $section_id;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
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
        $this->plugin = $this->get_db_field("plugin");
        $this->section_id = $id;
    }

    /** Private Methods */

    /* Public Methods *********************************************************/

    /**
     * Get the plugin that will be triggered
     * @retval string plugin code
     */
    public function get_plugin()
    {
        return $this->plugin;
    }

    public function checkTrigger()
    {
        if ($this->get_plugin() == plugins_calc_sleep_efficiency) {
            if (isset($_POST['__form_name'])) {
                require_once __DIR__ . "/../../../plugins/sleepEfficiency/php/SleepEfficiencyModule.php";
                $sleep_efficiency_model = new SleepEfficiencyModule($this->services, $this->section_id);
                $sleep_efficiency_model->calc_sleep_efficiency();
            }
        }
    }
}
