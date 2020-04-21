<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container<?php echo $fluid; ?> my-3 <?php echo $this->css; ?>">
    <div class="row">
        <div class="col-md-auto nav-col nav-md-col mb-2">
            <?php $this->output_nav(); ?>
        </div>
        <div class="col">
            <?php $this->output_children(); ?>
            <div>
                <?php $this->output_buttons(); ?>
            </div>
        </div>
    </div>
</div>
