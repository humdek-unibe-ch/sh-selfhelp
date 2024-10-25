<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Delete <?php echo ucfirst($this->target); ?></h1>
        <p>This will delete the <?php echo $this->target; ?> <code><?php echo $name; ?></code> and all the data associated to this <?php echo $this->target; ?>.</p>
        <p>Children elements are not affected.</p>
    </div>
    <?php $this->output_form(); ?>
</div>
