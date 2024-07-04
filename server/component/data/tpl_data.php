<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid my-3">
    <?php $this->output_controller_alerts_success(); ?>
    <div class="row">
        <div class="col">
            <div class="jumbotron">
                <h1>Data</h1>
                <p>This page shows all input data for all users or for a selected user. If you want to search any text input within the data you can use the global filter
                    of the filter for the specific form. If you want to filter the data for s specific user, find the user in the user table and click on the row. Button 'Cancel'
                    will reset all searching criterias.
                </p>
                <?php $this->output_config_panel(); ?>
            </div>
            <div>
                <?php $this->output_tables_data(); ?>
            </div>
        </div>
    </div>
</div>