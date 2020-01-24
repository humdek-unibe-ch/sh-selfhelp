<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid my-3">
    <div class="row">
        <div class="col-auto">
            <?php //$this->output_users(); 
            ?>
        </div>
        <div class="col">
            <div class="jumbotron">
                <h1>Data</h1>
                <h2>User <code><?php echo $this->get_selected_user(); ?></code></h2>
                <p>This page shows all input data for all users or for a selected user. If you want to search any text input within the data you can use the global filter
                    of the filter for the specific form. If you want to filter the data for s specific user, find the user in the user table and click on the row. Button 'Reset'
                    will reset all searching criterias.
                </p>
                <div class="card">
                    <div class="card-header text-white bg-primary">Search Panel</div>
                    <div class="card card-primary m-3">
                        <div class="card-header">Global filter</div>
                        <div class="card-body">
                            <input type="text" id="dataFilter" class="form-control" placeholder="type to filter all forms' data simultaneously">
                        </div>
                    </div>
                    <div class="card card-primary m-3">
                        <div class="card-header">Users</div>
                        <div class="card-body">
                            <?php $this->output_config_panel(); ?>
                        </div>
                    </div>
                    <div>
                        <button id="btnReset" type="button" class="btn btn-primary float-right mb-3 mr-3">Reset</button>
                    </div>
                </div>
            </div>
            <div>
                <?php $this->output_tables_data(); ?>
            </div>
        </div>
    </div>
</div>