<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<select <?php echo $multiple; ?> name="<?php echo $this->name; ?>" class="form-control <?php echo $css; ?>" <?php echo $required; ?>>
    <?php $this->output_fields(); ?>
</select>
