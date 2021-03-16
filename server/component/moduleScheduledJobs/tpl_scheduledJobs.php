<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_side_buttons(); ?>
        </div>
        <div class="col">
            <?php $this->output_alert(); ?>     
            <div class="jumbotron">
                <h1>Scheduled Jobs</h1>
                <p>
                    The table below lists all queued scheduled jobs for the given period.
                    Clicking on a row will select an entry. Clicking on + sing will show the transactions related to this entry.
                </p>
            </div>
            <div class="card">
                <div class="card-header">Search Panel</div>
                <form class="input-group form-group m-3" action="<?php echo $this->model->get_link_url("moduleMail"); ?>" method="POST">
                    <div class="searchPanel">
                        <div class="input-group mr-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Date Type</span>
                            </div>
                            <?php echo $this->get_date_types(); ?>
                        </div>
                        <div class="input-group ">
                            <div class="input-group-prepend">
                                <span class="input-group-text">From</span>
                            </div>
                            <input type="text" class="form-control" id="dateFrom" name="dateFrom" value="<?php echo $this->model->get_date_from() ?>"><span class="add-on"></span>
                            <div class="input-group-append mr-3">
                                <div class="btn btn-primary" id="btnFrom"><i class="far fa-calendar-alt"></i></div>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">To</span>
                            </div>
                            <input type="text" class="form-control" id="dateTo" name="dateTo" value="<?php echo $this->model->get_date_to() ?>"><span class="add-on"></span>
                            <div class="input-group-append mr-3">
                                <div class="btn btn-primary" id="btnTo"><i class="far fa-calendar-alt"></i></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
            <div class="card mt-3">
                <div class="card-header">Mail Queue</div>
                <div class="card-body">
                    <?php $this->output_mail_queue(); ?>
                </div>
            </div>
        </div>
    </div>
</div>