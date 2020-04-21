<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card-header <?php echo $collapsible; ?> <?php echo $show; ?>">
    <div class="d-flex align-items-center">
        <?php echo $this->title; ?>
        <div class="ml-auto">
            <?php $this->output_expand_icon(); ?>
            <?php $this->output_edit_button(); ?>
        </div>
    </div>
</div>
