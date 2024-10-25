<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<button class="btn btn-<?php echo $this->type; ?> tab-button <?php echo $active; ?> <?php echo $this->css; ?>" data-context="<?php echo $this->id; ?>" type="button">
    <?php $this->output_icon($icon); ?>
    <?php echo $this->label; ?>
</button>
<div class="card card-body bg-body-tertiary tab-content tab-content-index-<?php echo $this->id; ?> <?php echo $active; ?>">
    <?php echo $this->output_children(); ?>
</div>
