<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_button(); ?>
            <?php $this->output_languages(); ?>
        </div>
        <div class="col">
            <div class="jumbotron">
                <h1>CMS Preferences</h1>
                <p>Manage all global setting for the CMS.</p>
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
    </div>
</div>