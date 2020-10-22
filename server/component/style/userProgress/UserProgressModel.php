<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../../user/UserModel.php";

/**
 * This class is used to prepare all data related to the userProgress style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class UserProgressModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Public Methods *********************************************************/

    /**
     * Wrapper function to return the progress of the active user.
     *
     * @retval int
     *  The progress percentage of the current user.
     */
    public function get_user_progress()
    {
        $user = new UserModel($this->services);
        return round($user->get_user_progress($_SESSION['id_user'], $user->calc_pages_for_progress())*100);
    }

}
?>
