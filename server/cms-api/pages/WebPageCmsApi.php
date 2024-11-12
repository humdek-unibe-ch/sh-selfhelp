<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/../../service/ext/swaggest_json_schema_0.12.39.0_require/vendor/autoload.php";

/**
 * 
 * class is used for AJAX calls.
 */
class WebPageCmsApi extends BaseApiRequest
{
    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services, $keyword)
    {
        parent::__construct($services, $keyword);
    }

    /* Public Methods *********************************************************/

    public function GET_page($keyword)
    {
        return [
            "keyword" => $keyword
        ];
    }
}
?>
