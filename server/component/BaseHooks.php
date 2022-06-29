<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * The class to define the hooks.
 */
class BaseHooks
{
    /* Constructors ***********************************************************/

    /* Protected Properties *****************************************************/
    /**
     * Various params
     */
    protected $params;

    /**
     * The constructor creates an instance of the hooks.
     *
     * @param object $params
     *  Various params
     */
    public function __construct($params = array())
    {
        $this->params = $params;
    }
}
?>
