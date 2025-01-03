<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/pages/AdminPagesApi.php";

class AdminCmsApi extends BaseApiRequest
{

    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->client_type = pageAccessTypes_web;
    }

    /* Public Methods *********************************************************/

    public function GET_pages(): void
    {
        $pages = new AdminPagesApi(services: $this->services, keyword: $this->keyword);
        $this->response->set_data($pages->GET_pages());
    }
}
?>
