<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card mb-3 card-secondary">
    <div class="card-header collapsible">
        <div class="d-flex align-items-center">
            Stages 
            <div class="ml-auto">
                <i class="card-icon-collapse ml-3 fas fa-angle-double-up"></i>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="qualtrics-project-stages" class="table table-sm table-hover">
            <thead>
                <tr>
                    <th scope="col">Stage ID</th>
                    <th scope="col">Stage Name</th>
                    <th scope="col">Stage Type</th>
                    <th scope="col">When survey</th>
                    <th scope="col">Is (trigger type)</th>
                    <th scope="col">For groups</th>
                    <th scope="col">Functions</th>
                </tr>
            </thead>
            <tbody>
                <?php $this->output_project_stages_rows(); ?>
            </tbody>
        </table>
    </div>
</div>