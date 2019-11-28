<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><?php echo $title; ?></h5>
    </div>
    <div class="card-body">
        <p><?php echo $text; ?></p>
        <?php $this->output_export_item_options($options); ?>
    </div>
</div>
