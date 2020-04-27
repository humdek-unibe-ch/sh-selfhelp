<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-3">
    <table id="qualtrics-surveys" class="table table-sm table-hover">
        <thead>
            <tr>
                <th scope="col">Survey ID</th>
                <th scope="col">Survey Name</th>
                <th scope="col">Qualtrics Survey ID</th>
                <th scope="col">Survey Description</th>
                <th scope="col">Subject Variable</th>
            </tr>
        </thead>
        <tbody>
            <?php $this->output_surveys_rows(); ?>
        </tbody>
    </table>
</div>
