<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_controller_alerts_fail(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Remove All User <?php echo $title; ?></h1>
        <?php $this->output_text(); ?>
    </div>
    <?php $this->output_form(); ?>
</div>
