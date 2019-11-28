<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-2 <?php echo $border; ?> <?php echo $this->css; ?>">
    <div class="d-flex">
        <strong><?php echo $this->title; ?></strong>
        <?php $this->output_help(); ?>
        <?php $this->output_type(); ?>
        <?php $this->output_small_text(); ?>
    </div>
    <div><?php $this->output_field_content(); ?></div>
</div>
