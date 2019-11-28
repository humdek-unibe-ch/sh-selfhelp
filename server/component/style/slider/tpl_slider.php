<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="selfhelp-slider <?php echo $css; ?>">
    <input type="range" name="<?php echo $this->name; ?>" class="custom-range" min="<?php echo $this->min; ?>" max="<?php echo $this->max; ?>" value="<?php echo $this->value; ?>">
    <div class="slider-legend">
        <?php $this->output_legend(); ?>
    </div>
</div>
