<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/../../../service/ext/swaggest_json_schema_0.12.39.0_require/vendor/autoload.php";

class AdminCmsApi extends BaseApiRequest
{

    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->client_type = pageAccessTypes_web;
    }

    /* Public Methods *********************************************************/
}
?>
