<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . '/../../service/ext/clockwork/vendor/autoload.php';
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ClockworkModel extends BaseModel
{
    /* Private Properties *****************************************************/
    /**
     * The clockwork API key
     */
    private $clockwork_enabled = 0;

    private $clockwork;

    /**
     * The constructor
     *
     * @param object $services
     *  An associative array holding the different available services. See the
     *  class definition base page for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     * @param array $params
     *  The list of get parameters to propagate.     
     */
    public function __construct($services, $id, $params)
    {
        parent::__construct($services, $id, $params);
        $cmsPreferences = $this->db->fetch_cmsPreferences()[0];
        if (isset($cmsPreferences['clockwork']) && $cmsPreferences['clockwork']) {
            $this->clockwork_enabled = $cmsPreferences['clockwork'];
        }
        if ($this->is_clockwork_enabled()) {
            $this->clockwork = Clockwork\Support\Vanilla\Clockwork::init([
                'storage_files_path' => __DIR__ . '/../../../data/clockwork',
                'register_helpers' => true,
                'enable' => $this->is_clockwork_enabled(),
                // 'web' => [
                //     'enable' => true,
                //     'path' => __DIR__ . '/server/service/ext/clockwork/vendor/itsgoingd/clockwork/Clockwork/Web/public',
                //     'uri' => 'server/service/ext/clockwork/vendor/itsgoingd/clockwork/Clockwork/Web/public'
                // ]
            ]);
        }
    }

    /* Private Methods *********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Is clockwork enabled?
     * 
     * @return bool
     */
    public function is_clockwork_enabled(): bool
    {
        return $this->clockwork_enabled == 1;
    }

    public function handleMetadata()
    {
        header('Content-Type: application/json');
        $this->clockwork->handleMetadata();
        exit(0);   
    }
}
