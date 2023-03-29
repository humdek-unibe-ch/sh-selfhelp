<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<select id="<?php echo $this->id_section; ?>" <?php echo $this->disabled ? 'disabled' : ''; ?> <?php echo $multiple; ?> name="<?php echo $this->name; ?>" class="bootstrapSelect selfhelpSelect form-control <?php echo $css; ?>" <?php echo $required; ?> data-allow-clear="<?php echo ($this->allow_clear ? 'true' : 'false'); ?>" data-live-search="<?php echo ($this->live_search ? 'true' : 'false'); ?>" data-size="<?php echo $this->max; ?>" data-locked_after_submit="<?php echo $this->locked_after_submit; ?>">
    <option data-hidden="true"></option>
    <?php $this->output_fields(); ?>
</select>