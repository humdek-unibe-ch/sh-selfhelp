<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseCallback.php";

/**
 * A small class that handles callbak and set the group number for validation code
 * calls.
 */
class CallbackSetGroup extends BaseCallback
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

    /**
     * The setgroup function that can be called by the callback
     *
     * @param $data
     *  The POST data of the callback call:
     */
    public function set_group($data)
    {
       $result = [];
       $result['selfhelpCallback'] = 'selfelhp result';
        echo json_encode($result);
    }
}
?>
