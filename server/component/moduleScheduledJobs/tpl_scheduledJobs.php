<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="d-flex w-100">
        <div class="scheduled-jobs-side-buttons me-3">
            <?php $this->output_side_buttons(); ?>
        </div>
        <div class="flex-grow-1 scheduled-jobs-holder">
            <?php $this->output_alert(); ?>
            <div class="card card-header mb-4 rounded-2 py-5 px-3">
                <h1>Scheduled Jobs</h1>
                <p>
                    The table below lists all queued scheduled jobs for the given period.
                    Clicking on a row will select an entry. Clicking on + sing will show the transactions related to this entry.
                </p>
            </div>
            <div class="card">
                <div class="card-header">Search Panel</div>
                <form class="input-group mb-3 m-3" action="<?php echo $this->model->get_link_url("moduleMail"); ?>" method="POST">
                    <div class="searchPanel">
                        <div class="input-group me-3">
                            <span class="input-group-text">Date Type</span>
                            <?php echo $this->get_date_types(); ?>
                        </div>
                        <div class="input-group ">
                            <span class="input-group-text">From</span>
                            <input type="text" class="form-control" id="dateFrom" name="dateFrom" value="<?php echo $this->model->get_date_from() ?>"><span class="add-on"></span>
                            <div class="me-3">
                                <div class="btn btn-primary" id="btnFrom"><i class="far fa-calendar-alt"></i></div>
                            </div>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">To</span>
                            <input type="text" class="form-control" id="dateTo" name="dateTo" value="<?php echo $this->model->get_date_to() ?>"><span class="add-on"></span>
                            <div class=" me-3">
                                <div class="btn btn-primary" id="btnTo"><i class="far fa-calendar-alt"></i></div>
                            </div>
                        </div>
                        <button id="btn-search-scheduled-jobs" type="submit" class="btn btn-primary">Search</button>
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