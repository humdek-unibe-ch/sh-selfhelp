<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="section-<?php echo $this->id_section; ?>" class="alert <?php echo $type; ?> <?php echo $this->css; ?>">
    <?php $this->output_close_button(); ?>
    <?php $this->output_children();?>
</div>