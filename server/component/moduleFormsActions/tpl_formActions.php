<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-3">
    <table id="formActions" class="table table-sm table-hover">
        <thead>
            <tr>
                <th scope="col">Action ID</th>
                <th scope="col">Action Name</th>
                <th scope="col">Form Name</th>
                <th scope="col">Trigger Type</th>
                <th scope="col">Action Type</th>
                <th scope="col">For Groups</th>
            </tr>
        </thead>
        <tbody>
            <?php $this->output_actions_rows(); ?>
        </tbody>
    </table>
</div>
