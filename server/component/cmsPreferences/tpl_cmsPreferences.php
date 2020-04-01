<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="jumbotron">
        <h1>Manage Assets and CSS Files</h1>
        <p>Manage asset and CSS files. Asset files can be referenced in the CMS and CSS files can be used to customize the look and feel of the webpage.</p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            Assets on the Server
        </div>
        <div class="card-body">
            <?php $this->output("asset"); ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            User-defined CSS Files
        </div>
        <div class="card-body">
            <?php $this->output("css"); ?>
        </div>
    </div>
</div>
