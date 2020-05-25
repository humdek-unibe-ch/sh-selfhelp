<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="jumbotron">
        <h1>Mail Queue</h1>
        <p>
            The table below lists all queued mails for the given period.
            Selecting one will allow delete or send the message manually.
        </p>
    </div>
    <div class="card">
        <div class="card-header text-white bg-primary">Search Panel</div>
        <div class="card card-primary m-3">
            <div class="card-header">Global filter</div>
            <div class="card-body">
                <input type="text" id="dataFilter" class="form-control" placeholder="type to filter all forms' data simultaneously">
            </div>
        </div>
        <div>
            <button id="btnReset" type="button" class="btn btn-primary float-right mb-3 mr-3">Reset</button>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">Mail Queue</div>
        <div class="card-body">
            <?php $this->output_mail_queue(); ?>
        </div>
    </div>    
</div>